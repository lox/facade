<?php

/**
 * A header belonging to am {@link Facade_Request} or a {@link Facade_Response}
 * @author Paul Annesley <paul@annesley.cc>
 * @licence http://www.opensource.org/licenses/mit-license.php
 * @see http://github.com/pda/bringit
 */
class Facade_Header
{
	const CRLF = "\r\n";

	private $_name;
	private $_value;

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name, $value)
	{
		$normalizer = new Facade_HeaderCaseNormalizer();
		$this->_name = $normalizer->normalize($name);
		$this->_value = $value;
	}

	/**
	 * The case-normalized name of the header.
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * The value of the header.
	 * @return string
	 */
	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * The full header string, e.g. 'Example-Header: Some Value'
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			'%s: %s%s',
			$this->getName(),
			$this->getValue(),
			self::CRLF
		);
	}

	/**
	 * Creates a header from a string representing a single header.
	 * @param string $headerString
	 * @return
	 */
	public static function fromString($headerString)
	{
		$headerString = rtrim($headerString,"\r\n");
		list($key, $value) = array_map('trim',explode(':', $headerString, 2));
		return new self($key, $value);
	}
}
