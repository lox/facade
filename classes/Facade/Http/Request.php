<?php

/**
 * An HTTP 1.0 request
 */
class Facade_Http_Request extends Facade_AbstractRequest
{
	const METHOD_PUT='PUT';
	const METHOD_GET='GET';
	const METHOD_POST='POST';
	const METHOD_HEAD='HEAD';

	private $_socket;
	private $_method;
	private $_path;

	/**
	 * Constructor
	 */
	public function __construct($socket, $method, $path)
	{
		parent::__construct();
		$this->_socket = $socket;
		$this->_method = $method;
		$this->_path = $path;
		$this->_headers = array();
	}

	/**
	 *
	 */
	public function setContentType($mimetype)
	{
		return $this->setHeader('Content-Type: '.$mimetype);
	}

	/**
	 *
	 */
	public function setDate($timestamp)
	{
		return $this->setHeader('Date: '. gmdate('D, d M Y H:i:s T', $timestamp));
	}

	/* (non-phpdoc)
	 * @see Facade_Request::send()
	 */
	public function send()
	{
		$headers = $this->getHeaders();

		// set some defaults
		if(!$headers->contains('Date')) $this->setDate(time());
		if(!$headers->contains('Host')) $this->setHeader('Host: ' .$this->_socket->getHost());

		// write the pre-amble
		$this->_socket->writeRequest(
			$this->_method,
			$this->_path,
			$this->getHeaders()
			);

		// most requests have a content stream
		if($headers->contains('Content-Length'))
		{
			$this->_socket->copy($this->getContentStream(),
				$headers->value('Content-Length'));
		}

		// build a response
		return new Facade_Http_Response($this->_socket);
	}
}
