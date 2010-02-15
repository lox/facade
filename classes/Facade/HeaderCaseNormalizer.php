<?php

/**
 * Normalizes the capitalization of header names e.g. Content-Type.
 * @author Paul Annesley <paul@annesley.cc>
 * @licence http://www.opensource.org/licenses/mit-license.php
 * @see http://github.com/pda/bringit
 */
class Facade_HeaderCaseNormalizer
{
	/**
	 * @param string $string
	 * @return string
	 */
	public function normalize($string)
	{
		return preg_replace_callback('#\w+#', array($this, '_callback'), $string);
	}

	/**
	 * Callback for preg_replace in self::normalize()
	 */
	private function _callback($matches)
	{
		return ucwords($matches[0]);
	}
}
