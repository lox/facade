<?php

/**
 * A set of primitive actions that can be made against any facade store. These
 * primatives return {@link Facade_Response} objects subtyped for the specific
 * storage system.
 */
interface Facade_Store
{
	/**
	 * Gets an object from a store.
	 * @param mixed {@link Facade_Path} or string
	 * @return Facade_Request
	 */
	public function get($path);

	/**
	 * Gets an object from a store.
	 * @param mixed {@link Facade_Path} or string
	 * @return Facade_Request
	 */
	public function put($path);

	/**
	 * Sends data to the store
	 * @param mixed {@link Facade_Path} or string
	 * @param array key value data to send to the store
	 * @return Facade_Request
	 */
	public function post($path, $data);

	/**
	 * Deletes an object from the store
	 * @param mixed {@link Facade_Path} or string
	 * @return Facade_Request
	 */
	public function delete($path);

	/**
	 * Gets an object's headers from the store
	 * @param mixed {@link Facade_Path} or string
	 * @return Facade_Request
	 */
	public function head($path);
}
