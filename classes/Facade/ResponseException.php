<?php

/**
 * An exception related to a response
 */
class Facade_ResponseException extends Facade_Exception
{
	/**
	 * Whether the failed request can be retried
	 */
	public function isRepeatable()
	{
		return $this->getCode() != 404;
	}
}
