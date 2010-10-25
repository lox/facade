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
		$this->_length = $length;
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
		$r = fread($this->_stream, $bytes);

		// check for errors
		if($errorcode = socket_last_error())
		{
			$errormsg = socket_strerror($errorcode);
			throw new Facade_StreamException("Socket error: $errormsg",
				socket_strerror($errorcode));
		}

		return $r;
	}

	/**
	 * Writes $data to the stream
	 * @return the number of bytes actually written
	 */
	public function write($data)
	{
		if(!$this->_writable)
		{
			throw new Facade_StreamException("Stream is not writable");
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
	 * Seeks to a particular point in the stream
	 */
	public function seek($offset)
	{
		if(fseek($this->_stream, $offset) !== 0)
			throw new Facade_StreamException("Seek failed");
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
	public function copy($stream)
	{
		if(!$this->_writable)
		{
			throw new Facade_StreamException("Stream is not writable");
		}

		if($stream instanceof Facade_Stream)
		{
			$stream = $stream->getRawStream();
		}

		$bytesRead = 0;
		$bytesWritten = 0;

		while(!feof($stream))
		{
			$buffer = fread($stream, 1024);
			$bytesRead += strlen($buffer);
			$bytesWritten += $this->_fwrite($this->_stream, $buffer);
		}

		if($bytesRead <> $bytesWritten)
		{
			throw new Facade_StreamException(sprintf(
				"Stream copy failed, wrote %d of %d bytes",
				$bytesWritten, $bytesRead
				));
		}

		return $bytesRead;
	}

	/**
	 * Wrapper around fwrite that throws an exception
	 * @return int the number of bytes written
	 */
	private function _fwrite($fp, $string)
	{
		$bytes = fwrite($fp, $string);

		// this happens with invalid function params
		if($bytes === false)
		{
			throw new Facade_StreamException(
				"Failed to write to stream, fwrite returned false"
				);
		}

		// note that fwrite doesn't return false as per documentation
		// http://www.php.net/manual/en/function.fwrite.php#96951
		if($bytes !== strlen($string))
		{
			throw new Facade_StreamException(sprintf(
				"Failed to write to stream, fwrite wrote %d of %d bytes",
				$bytes,
				strlen($string)
				));
		}

		return $bytes;
	}

	/**
	 * Sets whether the stream can be written to
	 * @chainable
	 */
	public function setWritable($writable)
	{
		$this->_writable = $writable;
		return $this;
	}

	/**
	 * Sets the length of the stream
	 * @chainable
	 */
	public function setLength($length)
	{
		if($this->_writable)
		{
			throw new Facade_StreamException("Stream is writable, a length cannot be set");
		}

		$this->_length = $length;
		return $this;
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

	/**
	 * Calculates the length of the stream
	 */
	public static function calculateStreamLength($stream)
	{
		$metadata = stream_get_meta_data($stream);
		$position = ftell($stream);
		$length = false;

		// try the filesize, works for urls and files
		if(isset($metadata['uri']))
		{
			clearstatcache();
			return filesize($metadata['uri']) - $position;
		}
		// fall back to seeking, this is slow on large files
		else if($metadata['seekable'])
		{
			fseek($stream, 0, SEEK_END);
			$length = ftell($stream);
			fseek($stream, $position, SEEK_SET);
		}

		return $length;
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
			throw new Facade_Exception("Failed to open file");
		}

		return new Facade_Stream($fp, filesize($file), $writable);
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
			throw new Facade_Exception("Failed to create temp file");
		}

		// write to the buffer
		fwrite($fp, $string);
		rewind($fp);

		return new Facade_Stream($fp, strlen($string), false);
	}

	/**
	 * Helper to construct from a read-only stream, detects the length if not provided
	 * @param stream a stream
	 * @param int the length of the stream, if null then autodetect is used
	 * @param bool whether to allow writes to the stream
	 */
	public static function fromStream($stream, $length=null)
	{
		return new Facade_Stream($stream,
			$length ? $length : self::calculateStreamLength($stream));
	}
}
