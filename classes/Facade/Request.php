<?php

/**
 * A request made to a store
 */
interface Facade_Request
{
	/**
	* Sets the content to send as an {@link Facade_Stream}
	 * @chainable
	 */
	public function setStream($stream);

	/**
	 * Sets a header
	 * @param either a string containing the header or a {@link Facade_Header}
	 * @chainable
	 */
	public function setHeader($header);

	/**
	 * Gets the collection of headers
	 * @return Facade_HeaderCollection
	 */
	public function getHeaders();

	/**
	 * Sends the request
	 * @return Facade_Response
	 */
	public function send();
}
