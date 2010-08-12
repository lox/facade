<?php

/**
 * A request that retries on error conditions
 */
class Facade_ErrorResistantRequest implements Facade_Request
{
	const ERROR_RETRIES=5;

	private $_request;
	private $_offset;
	private $_stream;

	/**
	 * Constructor
	 */
	public function __construct($request)
	{
		$this->_request = $request;
	}

	/* (non-phpdoc)
	 * @see Facade_Request::setStream()
	 */
	public function setStream($stream)
	{
		$this->_offset = $stream->getOffset();
		$this->_stream = $stream;

		$this->_request->setStream($stream);
		return $this;
	}

	/* (non-phpdoc)
	 * @see Facade_Request::setHeader()
	 */
	public function setHeader($header)
	{
		$this->_request->setHeader($header);
		return $this;
	}

	/* (non-phpdoc)
	 * @see Facade_Request::getHeaders()
	 */
	public function getHeaders()
	{
		return $this->_request->getHeaders();
	}

	/* (non-phpdoc)
	 * @see Facade_Request::reset()
	 */
	public function reset()
	{
		// reset the source stream offset if it's changed
		if(isset($this->_stream) && $this->_stream->getOffset() != $this->_offset)
			$this->_stream->seek($this->_offset);

		return $this->_request->reset();
	}

	/* (non-phpdoc)
	 * @see Facade_Request::send()
	 */
	public function send()
	{
		for($i=0; $i<self::ERROR_RETRIES-1; $i++)
		{
			try
			{
				return $this->_request->send();
			}
			catch(Facade_ResponseException $e)
			{
				if(!$e->isRepeatable()) throw $e;
			}
			catch(Facade_StreamException $e)
			{
				// always retry stream exceptions
			}

			// exponential sleep
			usleep(min(((pow(4, $i) * 10000)) + 1000000, 2000000));

			// reset state on request
			try
			{
				$this->reset();
			}
			catch(Facade_StreamException $e)
			{
				// this happens on connect errors
			}
		}

		return $this->_request->send();
	}

	/**
	 * Generic decorator, send all calls to delegate
	 */
	public function __call($method, $params)
	{
		$return = call_user_func_array(
			array($this->_request, $method), $params);

		// chainable methods need some intervention
		return $return === $this->_request ? $this : $return;
	}
}
