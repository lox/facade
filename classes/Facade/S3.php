<?php

/**
 * A simple Amazon S3 backend
 */
class Facade_S3 implements Facade_Backend
{
	const S3_HOST='s3.amazonaws.com';

	private $_key;
	private $_secret;
	private $_timeout;

	/**
	 * Constructor
	 * @param string AWS Access Key ID
	 * @param string AWS Secret Key
	 */
	public function __construct($key, $secret, $timeout=10)
	{
		$this->_key = $key;
		$this->_secret = $secret;
		$this->_timeout = $timeout;
	}

	/* (non-phpdoc)
	 * @see Facade_Backend::put()
	 */
	public function put($path)
	{
		return $this
			->buildRequest(Facade_Http_Request::METHOD_PUT, $path);
	}

	/* (non-phpdoc)
	 * @see Facade_Backend::get()
	 */
	public function get($path)
	{
		return $this
			->buildRequest(Facade_Http_Request::METHOD_GET, $path);
	}

	/* (non-phpdoc)
	 * @see Facade_Backend::head()
	 */
	public function head($path)
	{
		return $this
			->buildRequest(Facade_Http_Request::METHOD_HEAD, $path);
	}

	/* (non-phpdoc)
	 * @see Facade_Backend::post()
	 */
	public function post($path, $data)
	{
		throw new BadMethodCallException(__METHOD__ . ' not implemented');
	}

	/* (non-phpdoc)
	 * @see Facade_Backend::delete()
	 */
	public function delete($path)
	{
		return $this
			->buildRequest(Facade_Http_Request::METHOD_DELETE, $path);
	}

	/**
	 * Builds an S3 request
	 */
	private function buildRequest($method, $path)
	{
		return new Facade_ErrorResistantRequest(
			new Facade_S3_Request(
				new Facade_Http_Socket(self::S3_HOST, 80, $this->_timeout),
				$this->_key,
				$this->_secret,
				$method,
				$path
				));
	}
}


