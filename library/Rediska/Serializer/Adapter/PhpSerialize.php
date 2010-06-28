<?php

/**
 * Default PHP adapter
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Serializer_Adapter_PhpSerialize implements Rediska_Serializer_Adapter_Interface
{
	/**
	 * Serialize value
	 *
	 * @param mixin $value
	 * @return string
	 */
	public function serialize($value)
	{
		return serialize($value);
	}

	/**
	 * Unserialize value
	 *
	 * @throws Rediska_Serializer_Exception
	 * @param mixin $value
	 * @return string
	 */
	public function unserialize($value)
	{
		$beforeSerializeError = error_get_last();

		$value = @unserialize($value);

		$serializeError = error_get_last();

		if ($beforeSerializeError !== $serializeError) {
			throw new Rediska_Serializer_Exception("Can't unserialize string");
		}

		return $value;
	}
}