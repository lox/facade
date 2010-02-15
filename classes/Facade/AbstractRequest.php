<?php

/**
 * A basic request object
 */
abstract class Facade_AbstractRequest implements Facade_Request
{
	const BUFFER_SIZE = '512000'; // 500Kb

	private $_headers;
	private $_stream;

	/**
	 * Constructor
	 */
	public function __construct($headers=array())
	{
		$this->_headers = new Facade_HeaderCollection($headers);
	}

	/* (non-phpdoc)
	 * @see Facade_Request::setContentFile()
	 */
	public function setContentFile($file)
	{
		if(!is_file($file))
		{
			throw new Facade_Exception("$file isn't a file");
		}

		return $this->setContentStream(fopen($file,'r'), filesize($file));
	}

	/* (non-phpdoc)
	 * @see Facade_Request::setContentStream()
	 */
	public function setContentStream($stream, $length=null)
	{
		if($length) $this->setContentLength($length);
		$this->_stream = $stream;
		return $this;
	}

	/* (non-phpdoc)
	 * @see Facade_Request::setContentString()
	 */
	public function setContentString($string)
	{
		if(!$fp = fopen('php://temp/maxmemory:'.self::BUFFER_SIZE, 'w+'))
		{
			throw new Facade_Exception("Failed to create temp file stream");
		}

		// write to the buffer
		fwrite($fp, $string);
		rewind($fp);

		return $this->setContentStream($fp, strlen($string));
	}

	/**
	 * Returns the content stream, or an exception if it's not yet been set
	 */
	protected function getContentStream()
	{
		return $this->_stream;
	}

	/* (non-phpdoc)
	 * @see Facade_Request::setHeader()
	 */
	public function setHeader($header)
	{
		$this->getHeaders()->set($header);
		return $this;
	}

	/* (non-phpdoc)
	 * @see Facade_Request::getHeaders()
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/* (non-phpdoc)
	 * @see Facade_Request::hasHeader()
	 */
	public function hasHeader($header)
	{
		return $this->getHeaders()->contains($header);
	}

	/**
	 * Sets the length of the content
	 */
	public function setContentLength($length)
	{
		return $this->setHeader('Content-Length: '.$length);
	}
}
