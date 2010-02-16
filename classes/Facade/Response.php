<?php

/**
 * A response from a backend
 */
interface Facade_Response
{
	/**
	 * Whether the request was successful
	 * @return bool
	 */
	public function isSuccessful();

	/**
	 * Gets the headers from the response
	 * @return Facade_HeaderCollection
	 */
	public function getHeaders();

	/**
	* Gets the content of the response as a {@link Facade_Stream}
	 * @return Facade_Stream
	 */
	public function getStream();
}
