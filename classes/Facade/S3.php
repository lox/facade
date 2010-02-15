<?php

/**
 * A simple Amazon S3 store implementation
 */
class Facade_S3 implements Facade_Store
{
	private $_key;
	private $_secret;
	private $_timeout;

	/**
	 * Constructor
	 * @param string AWS Access Key ID
	 * @param string AWS Secret Key
	 */
	public function __construct($key, $secret, $timeout=30)
	{
		$this->_key = $key;
		$this->_secret = $secret;
		$this->_timeout = $timeout;
	}

	/* (non-phpdoc)
	 * @see Facade_Store::put()
	 */
	public function put($path)
	{
		return $this
			->buildRequest(Facade_Http_Request::METHOD_PUT, $path);
	}

	/* (non-phpdoc)
	 * @see Facade_Store::get()
	 */
	public function get($path)
	{
		return $this
			->buildRequest(Facade_Http_Request::METHOD_GET, $path);
	}

	/* (non-phpdoc)
	 * @see Facade_Store::head()
	 */
	public function head($path)
	{
		return $this
			->buildRequest(Facade_Http_Request::METHOD_HEAD, $path);
	}

	/* (non-phpdoc)
	 * @see Facade_Store::post()
	 */
	public function post($path, $data)
	{
		throw new BadMethodCallException(__METHOD__ . ' not implemented');
	}

	/* (non-phpdoc)
	 * @see Facade_Store::delete()
	 */
	public function delete($path)
	{
		throw new BadMethodCallException(__METHOD__ . ' not implemented');
	}


	/**
	 * Builds an S3 request
	 */
	private function buildRequest($method, $path)
	{
		return new Facade_S3_Request(
			new Facade_Http_Socket('s3.amazonaws.com',80,$this->_timeout),
			$this->_key,
			$this->_secret,
			$method,
			$path
			);
	}
}


