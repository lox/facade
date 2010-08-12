<?php

/**
 * A request that retries on error conditions
 */
class Facade_ErrorResistantRequest implements Facade_Request
{
	const ERROR_RETRIES=5;

	private $_request;
	private $_streamOffset;
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
		$this->_streamOffset = $stream->getOffset();
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

			// sleep in ever increasing amounts of microseconds, max 2s
			usleep(min(((pow(4, $i) * 10000)) + 1000000, 2000000));

			// reset the stream position if needed
			if(isset($this->_stream)
				&& $this->_stream->getOffset() != $this->_streamOffset)
			{
				$this->_stream->seek($this->_streamOffset);
				$this->_request->setStream($this->_stream);
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
