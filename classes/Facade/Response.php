<?php

/**
 * A response from a store
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
	 * Gets the content of the response as a string
	 * @return string
	 */
	public function getContentString();

	/**
	 * Gets the content of the response as a stream
	 * @return stream
	 */
	public function getContentStream();

	/**
	 * Gets the content of the response as an xml document
	 * @return SimpleXMLElement
	 */
	public function getContentXml();
}
