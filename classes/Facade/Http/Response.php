<?php

/**
 * A response from an HTTP server
 */
class Facade_Http_Response implements Facade_Response
{
	private $_socket;
	private $_headers;
	private $_status;

	/**
	 * Constructor
	 */
	public function __construct($socket, $exception=true)
	{
		$this->_socket = $socket;
		$this->_status = $this->_socket->readStatus();
		$this->_headers = $this->_socket->readHeaders();

		// throw an exception if the request failed
		if($exception && !$this->isSuccessful() && !$this->_socket->isEof())
		{
			throw new Facade_ResponseException(
				"Request failed: ".$this->getStatusMessage(),
				$this->getStatusCode()
				);
		}
	}

	/**
	 * Whether the request was successful (returned a 200 response)
	 * @return bool
	 */
	public function isSuccessful()
	{
		return $this->_status[0] == 200;
	}

	/**
	 * Gets the status code from the HTTP response
	 * @return int
	 */
	public function getStatusCode()
	{
		return intval($this->_status[0]);
	}

	/**
	 * Gets the status message from the HTTP response
	 * @return string
	 */
	public function getStatusMessage()
	{
		return $this->_status[1];
	}

	/* (non-phpdoc)
	 * @see Facade_Response
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * Gets the content of the response as a string
	 * @return string
	 */
	public function getContentString()
	{
		if($this->getHeaders()->contains('Content-Length'))
		{
			// not sure if this is needed, but seemed sensible
			return stream_get_contents($this->getContentStream(),
				$this->getHeaders()->value('Content-Length'));
		}
		else
		{
			return stream_get_contents($this->getContentStream());
		}
	}

	/**
	 * Gets the content of the response as a stream
	 * @return stream
	 */
	public function getContentStream()
	{
		if($this->_socket->isEof())
		{
			throw new Facade_Exception("Response has no content");
		}

		return $this->_socket->getStream();
	}
	
	public function getStream()
	{
		return $this->getContentStream();
	}

	/**
	 * Gets the content of the response as an xml document
	 * @return SimpleXMLElement
	 */
	public function getContentXml()
	{
		return new SimpleXMLElement($this->getContentString());
	}
}
