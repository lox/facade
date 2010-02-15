<?php

/**
 * A request made to a store
 */
interface Facade_Request
{
	/**
	 * Set the input to use a file
	 * @chainable
	 */
	public function setContentFile($file);

	/**
	 * Sets the content to send as a php stream, with an optional length
	 * @chainable
	 */
	public function setContentStream($stream, $length=null);

	/**
	 * Sets the content to send as a string
	 * @chainable
	 */
	public function setContentString($string);

	/**
	 * Sets the length of the content
	 * @chainable
	 */
	public function setContentLength($bytes);

	/**
	 * Sets a header
	 * @param either a string containing the header or a {@link Facade_Header}
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
