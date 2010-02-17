<?php

/**
 * A response from Amazon's S3 service
 */
class Facade_S3_Response implements Facade_Response
{
	private $_socket;
	private $_headers;
	private $_status;

	/**
	 * Constructor
	 */
	public function __construct($socket, $path)
	{
		$this->_socket = $socket;
		$this->_status = $this->_socket->readStatus();
		$this->_headers = $this->_socket->readHeaders();

		// set the socket length from the Content-Length header
		if($this->_headers->contains('Content-Length'))
		{
			$this->_socket->setLength($this->_headers->value('Content-Length'));
		}

		// throw an exception if the request failed
		if(!$this->isSuccessful() && !$this->_socket->isEof())
		{
			$response = $this->getContentXml();

			throw new Facade_Exception(
				"S3 request for $path failed: {$response->Message}",
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
	 * Gets the content stream
	 * @return string
	 */
	public function getStream()
	{
		return $this->_socket;
	}

	/**
	 * Gets the content of the response as an xml document
	 * @return SimpleXMLElement
	 */
	public function getContentXml()
	{
		if($this->getHeaders()->value('Content-Type') != 'application/xml')
		{
			throw new Contests_Aws_S3Exception("Response is not xml");
		}

		return new SimpleXMLElement($this->getStream()->toString());
	}
}
