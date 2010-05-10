<?php

/**
 * An wrapper around a socket with http related methods
 * @author Lachlan Donald <lachlan@ljd.cc>
 */
class Facade_Http_Socket extends Facade_Stream
{
	private $_host;
	private $_port;
	private $_debug=false;

	/**
	 * Constructor
	 */
	public function __construct($host, $port, $timeout=30)
	{
		$this->_host = $host;
		$this->_port = $port;

		// open the tcp socket
		if(!$socket = @fsockopen($this->_host, $this->_port, $errno, $errstr, $timeout))
		{
			throw new Exception("Failed to connect to $this->_host: $errstr");
		}

		// delegate to stream constructor
		parent::__construct($socket,null,true);
	}

	/**
	 * Reads a line until a carriage-return and newline is encountered
	 * @return string the line read
	 */
	public function readLine()
	{
		$line = '';
		while(!$this->isEof() && substr($line,-2) != "\r\n")
		{
			$line .= $this->read(1);
		}

		$this->_debug("<<< ", $line);
		return $line;
	}

	/**
	 * Reads a line and parses the HTTP status response
	 */
	public function readStatus()
	{
		if($this->isEof())
		{
			throw new Exception("Server unexpectedly closed connection");
		}

		$line = trim($this->readLine());

		if(!preg_match('#^HTTP/1.\d (\d+) (.+?)$#',$line,$m))
		{
			throw new Exception("Malformed HTTP response from S3: $line");
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
			if(!preg_match("#^(.+?):(.*)$#",$line))
			{
				throw new Exception("Malformed HTTP header: ".trim($line));
			}

			$headers->add($line);
		}

		return $headers;
	}

	/* (non-phpdoc)
	 * @see Facade_Stream
	 */
	public function write($line)
	{
		$this->_debug(">>> ", $line);
		return parent::write($line);
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

	private function _debug($prefix, $line)
	{
		if($this->_debug)
		{
			file_put_contents('/tmp/socket.log',sprintf("%s %s\n",$prefix,trim($line)),FILE_APPEND);
			//printf("%s %s\n", $prefix, rtrim($line,"\r\n"));
		}
	}
}
