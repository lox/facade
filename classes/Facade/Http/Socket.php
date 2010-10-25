<?php

/**
 * An wrapper around a socket with http related methods
 * @author Lachlan Donald <lachlan@ljd.cc>
 */
class Facade_Http_Socket
{
	private $_host;
	private $_port;
	private $_timeout;
	private $_stream;
	private $_connected=false;
	private $_debug=false;

	/**
	 * Constructor
	 */
	public function __construct($host, $port, $timeout=30)
	{
		$this->_host = $host;
		$this->_port = $port;
		$this->_timeout = $timeout;
	}

	/**
	 * Connects to the remote host and creates the internal stream
	 * @chainable
	 */
	public function connect()
	{
		$this->_debug("### ",
			"connecting to {$this->_host}:{$this->_port}, timeout {$this->_timeout}");

		// open the tcp socket
		if(!$socket = @fsockopen($this->_host, $this->_port, $errno, $errstr, $this->_timeout))
		{
			throw new Facade_StreamException(
				"Failed to connect to $this->_host: $errstr", $errno);
		}

		$this->_connected = true;
		$this->_stream = new Facade_Stream($socket, null, true);
		return $this;
	}

	/**
	 * Returns the internal stream, connects if required
	 * @return Facade_Http_Stream
	 */
	public function stream()
	{
		if(!$this->_connected)
			$this->connect();

		return $this->_stream;
	}

	/**
	 * Reads a line until a carriage-return and newline is encountered
	 * @return string the line read
	 */
	public function readLine()
	{
		$line = '';
		while(!$this->stream()->isEof() && substr($line,-2) != "\r\n")
		{
			$line .= $this->stream()->read(1);
		}

		$this->_debug("<<< ", $line);
		return $line;
	}

	/**
	 * Reads a line and parses the HTTP status response
	 */
	public function readStatus()
	{
		if($this->stream()->isEof())
		{
			throw new Facade_StreamException("Server unexpectedly closed connection");
		}

		$line = trim($this->readLine());

		if(!preg_match('#^HTTP/1.\d (\d+) (.+?)$#',$line,$m))
		{
			throw new Facade_StreamException("Malformed HTTP response from S3: $line");
		}

		return array($m[1], $m[2]);
	}

	/**
	 * Reads lines and parses HTTP response headers from a stream
	 * @return Facade_HeaderCollection
	 */
	public function readHeaders()
	{
		$headers = new Facade_HeaderCollection();

		// read until headers are over
		while(($line = $this->readLine()) !== "\r\n")
		{
			if(!preg_match('#^(.+?):(.*)$#',$line))
			{
				throw new Facade_StreamException("Malformed HTTP header: ".trim($line));
			}

			$headers->add($line);
		}

		return $headers;
	}

	/**
	 * Writes to the sockets
	 * @return int how many bytes are written
	 */
	public function write($line)
	{
		$this->_debug(">>> ", $line);
		return $this->stream()->write($line);
	}

	/**
	 * Writes an HTTP 1.0 request preamble to the socket
	 * @param string the HTTP method
	 * @param string the path to the object
	 * @param Facade_HeaderCollection
	 * @chainable
	 */
	public function writeRequest($method, $path, $headers)
	{
		// use HTTP 1.0 for now
		$this->write(sprintf("%s %s HTTP/1.0\r\n",$method, $path));

		// write headers
		foreach($headers->toArray() as $line) $this->write($line);

		$this->write("\r\n");
		return $this;
	}
	
	public function getHost()
	{
		return $this->_host;
	}
	
	public function getStream()
	{
		return $this->_stream;
	}

	/**
	 * Generic decorator, send all calls to internal stream
	 */
	public function __call($method, $params)
	{
		$stream = $this->stream();
		$return = call_user_func_array(array($stream, $method), $params);

		// chainable methods need some intervention
		return $return === $stream ? $this : $return;
	}

	private function _debug($prefix, $line)
	{
		if($this->_debug)
		{
			file_put_contents('/tmp/socket.log',sprintf("%s %s\n",$prefix,trim($line)),FILE_APPEND);
			//printf("%s %s\n", $prefix, trim($line));
		}
	}
}
