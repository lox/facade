<?php

/**
 * A collection of {@link Facade_Header} objects
 */
class Facade_HeaderCollection implements IteratorAggregate
{
	private $_headers=array();

	/**
	 * Constructor
	 * @param $headers Facade_Header[]
	 */
	public function __construct($headers=array())
	{
		foreach($headers as $header) $this->add($header);
	}

	/**
	 * Adds a header
	 * @param mixed either string in "Header: Value" format or {@link Facade_Header}
	 * @chainable
	 */
	function add($header)
	{
		// convert to object form
		if(is_string($header)) $header = Facade_Header::fromString($header);

		$this->_headers[] = $header;
		return $this;
	}

	/**
	 * Sets a header
	 * @param mixed either string in "Header: Value" format or {@link Facade_Header}
	 * @chainable
	 */
	function set($header)
	{
		// convert to object form
		if(is_string($header)) $header = Facade_Header::fromString($header);

		return $this
			->remove($header->getName())
			->add($header)
			;
	}

	/**
	 * Removes a header from the collection
	 * @param string the name of a header
	 * @chainable
	 */
	function remove($name)
	{
		$normalizer = new Facade_HeaderCaseNormalizer();
		$name = $normalizer->normalize($name);

		foreach($this->_headers as $idx=>$header)
		{
			if($header->getName() == $name)
			{
				unset($this->_headers[$idx]);
			}
		}

		return $this;
	}

	/**
	 * Gets a single header value
	 * @return string
	 */
	function value($name, $default=false)
	{
		$values = $this->values($name);
		return count($values) ? $values[0] : $default;
	}

	/**
	 * Gets an array of the values for a header
	 * @return array
	 */
	function values($name)
	{
		$normalizer = new Facade_HeaderCaseNormalizer();
		$name = $normalizer->normalize($name);
		$values = array();

		foreach($this->_headers as $header)
		{
			if($header->getName() == $name)
			{
				$values[] = $header->getValue();
			}
		}

		return $values;
	}

	/**
	 * Whether the collection contains a specific header
	 * @return bool
	 */
	public function contains($name)
	{
		$values = $this->values($name);
		return count($values);
	}

	/**
	 * Returns a collection of headers where then name matches the pattern
	 * @return Facade_HeaderCollection
	 */
	public function filter($pattern)
	{
		$result = array();

		foreach($this->_headers as $header)
		{
			if(preg_match($pattern, $header->getName()))
			{
				$result[] = $header;
			}
		}

		return new Facade_HeaderCollection($result);
	}

	/**
	 * Sorts the collection by the passed callback, defaults to sorting by key
	 * @chainable
	 */
	public function sort($callback=null)
	{
		$headers = $this->_headers;
		$callback = $callback ? $callback : array($this,'_compareNames');
		usort($headers, $callback);

		return new Facade_HeaderCollection($headers);
	}

	/**
	 * Returns an array of the string versions of headers
	 * @return array
	 */
	public function toArray($crlf=true)
	{
		$headers = array();

		foreach($this->_headers as $header)
		{
			$string = $header->__toString();
			$headers[] = $crlf ? $string : rtrim($string);
		}

		return $headers;
	}

	/* (non-phpdoc)
	 * @see IteratorAggregate::getIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator(array_values($this->_headers));
	}

	private function _compareNames($a, $b)
	{
		return strcmp($a->getName(), $b->getName());
	}
}
