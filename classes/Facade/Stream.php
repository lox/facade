<?php

/**
 * A wrapper around a php stream
 */
class Facade_Stream
{
	private $_stream;
	private $_length;
	private $_offset;
	private $_writable;

	/**
	 * Constructor
	 */
	public function __construct($stream, $length=null, $writable=false)
	{
		$this->_stream = $stream;
		$this->_offset = ftell($stream);
		$this->_length = $writable ? null : $length;
		$this->_writable = $writable;
	}

	/**
	 * Closes the stream
	 */
	public function close()
	{
		fclose($this->_stream);
	}

	/**
	 * Reads $bytes from the stream
	 */
	public function read($bytes=1024)
	{
		return fread($this->_stream, $bytes);
	}

	/**
	 * Writes $data to the stream
	 * @return the number of bytes actually written
	 */
	public function write($data)
	{
		if(!$this->_writable)
		{
			throw new Facade_Exception("Stream is not writable");
		}

		return fwrite($this->_stream, $data);
	}

	/**
	 * Rewinds the stream
	 */
	public function rewind()
	{
		rewind($this->_stream);
	}

	/**
	 * Determines if the stream is at it's end
	 * @return bool
	 */
	public function isEof()
	{
		return feof($this->_stream);
	}

	/**
	 * Gets the current offset of the stream
	 * @return int
	 */
	public function getOffset()
	{
		return ftell($this->_stream);
	}

	/**
	 * Gets the length of the stream, null for writable streams
	 * @return mixed either null or an integer
	 */
	public function getLength()
	{
		return $this->_length;
	}

	/**
	 * Gets the raw php stream
	 * @return stream
	 */
	public function getRawStream()
	{
		return $this->_stream;
	}

	/**
	 * Copies from another stream to this stream
	 * @param mixed either a php stream or another Facade_Stream
	 * @return the number of bytes copied
	 */
	public function copy($stream, $bytes=null)
	{
		if(!$this->_writable)
		{
			throw new Facade_Exception("Stream is not writable");
		}

		if($stream instanceof Facade_Stream)
		{
			$maxbytes = is_null($bytes) ? $stream->getLength() : $bytes;
			$stream = $stream->getRawStream();
		}

		return stream_copy_to_stream(
			$stream, $this->_stream,
			is_null($bytes) ? -1 : $bytes
			);
	}

	/**
	 * Calculates the length of the stream
	 */
	public function calculateLength()
	{
		$metadata = stream_get_meta_data($this->_stream);
		$position = ftell($this->_stream);
		$length = false;

		if(isset($metadata['uri']))
		{
			return filesize($metadata['uri']) - $position;
		}
		else if($metadata['seekable'])
		{
			fseek($this->_stream, 0, SEEK_END);
			$length = ftell($this->_stream);
			fseek($this->_stream, $position, SEEK_SET);
		}

		return $length;
	}

	/**
	 * Sets the length of the stream
	 */
	public function setLength($length)
	{
		if($this->_writable)
		{
			throw new Facade_Exception("Stream is writable, a length cannot be set");
		}

		$this->_length = $length;
	}

	/**
	 * Gets the remaining contents of the stream as a string
	 */
	public function toString()
	{
		return stream_get_contents(
			$this->_stream,
			$this->_length ? $this->_length : -1
			);
	}

	// -------------------------------------------------
	// helpers for constructing a stream

	/**
	 * Helper to construct from a file
	 * @param string a filename
	 * @param bool whether to allow writes to the stream
	 */
	public static function fromFile($file, $writable=false)
	{
		if(!$fp = @fopen($file, $writable ? 'r+' : 'r'))
		{
			throw new Facade_Exception("Failed to open file: $php_errormsg");
		}

		return new Facade_Stream($fp, filesize($file));
	}

	/**
	 * Helper to construct from a string
	 * @param string the string to use
	 * @param int the size of the memory buffer to use, the rest is stored on disk
	 */
	public static function fromString($string, $buffer=512000)
	{
		if(!$fp = @fopen('php://temp/maxmemory:'.$buffer, 'w+'))
		{
			throw new Facade_Exception("Failed to create temp file: $php_errormsg");
		}

		// write to the buffer
		fwrite($fp, $string);
		rewind($fp);

		return new Facade_Stream($fp, strlen($string), false);
	}
}
