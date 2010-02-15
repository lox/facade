<?php

/**
 * An wrapper around a socket with http related methods
 * @author Lachlan Donald <lachlan@ljd.cc>
 */
class Facade_Http_Socket
{
	private $_socket;
	private $_timeout;
	private $_host;
	private $_port;
	private $_debug=true;

	/**
	 * Constructor
	 */
	public function __construct($host, $port, $timeout=30)
	{
		$this->_host = $host;
		$this->_port = $port;

		// open the tcp socket
		if(!$this->_socket = @fsockopen($this->_host, $this->_port, $errno, $errstr, $timeout))
		{
			throw new Exception("Failed to connect to $this->_host: $errstr");
		}
	}

	/**
	 * Closes the socket
	 */
	public function close()
	{
		fclose($this->_socket);
	}

	/**
	 * Basic socket read
	 * @return the number of bytes read
	 */
	public function read($bytes=1024)
	{
		return fread($this->_socket, $bytes);
	}

	/**
	 * Reads a line until a carriage-return and newline is encountered
	 * @return string the line read
	 */
	public function readLine()
	{
		$line = '';

		while(!feof($this->_socket) && substr($line,-2) != "\r\n")
		{
			$line .= $this->read(1);
		}

		if($this->_debug) printf("<<< %s%s",trim($line),(php_sapi_name()=='cli'?"\n":'<br />'));
		return $line;
	}

	/**
	 * Reads a line and parses the HTTP status response
	 */
	public function readStatus()
	{
		if(!preg_match('#^HTTP/1.\d (\d+) (.+?)$#',trim($this->readLine()),$m))
		{
			throw new Exception("Malformed HTTP response from S3");
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
			if(!preg_match("#^(.+?):(.+?)$#",trim($line),$m))
			{
				throw new Exception("Malformed HTTP header");
			}

			$headers->add($line);
		}

		return $headers;
	}

	/**
	 * Reads from the socket until EOF is encountered, or $maxbytes is met
	 */
	public function readAll($maxbytes=-1)
	{
		return stream_get_contents($this->_socket, $maxbytes);
	}

	/**
	 * Basic socket write
	 * @chainable
	 */
	public function write($line)
	{
		if($this->_debug) printf(">>> %s%s",trim($line),(php_sapi_name()=='cli'?"\n":'<br />'));
		fwrite($this->_socket, $line);
		return $this;
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

	/**
	 * Copies a stream's contents (until EOF or $maxbytes) into this socket
	 * @chainable
	 */
	public function copy($stream, $maxbytes=-1)
	{
		stream_copy_to_stream($stream, $this->_socket, $maxbytes);
		return $this;
	}

	/**
	 * Determines if the socket is at the EOF
	 * @return bool
	 */
	public function isEof()
	{
		return feof($this->_socket);
	}

	/**
	 * Gets the socket as a php stream
	 * @return stream
	 */
	public function getStream()
	{
		return $this->_socket;
	}

	/**
	 * Gets the host that the socket is connecting to
	 * @return string
	 */
	public function getHost()
	{
		return $this->_host;
	}

	/**
	 * Attempts to figure out the length of a stream
	 */
	public static function getStreamLength($stream)
	{
		$metadata = stream_get_meta_data($stream);
		$position = ftell($stream);
		$length = false;

		if(isset($metadata['uri']))
		{
			return filesize($metadata['uri']) - ftell($stream);
		}
		else if($metadata['seekable'])
		{
			fseek($stream, 0, SEEK_END);
			$length = ftell($stream);
			fseek($stream, $position, SEEK_SET);
		}

		return $length;
	}
}
