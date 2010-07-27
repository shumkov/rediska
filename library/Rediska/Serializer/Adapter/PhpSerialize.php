<?php

/**
 * Default PHP serializer adapter
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
	 * @param string $value
	 * @return mixin
	 */
	public function unserialize($value)
	{
		set_error_handler(array($this, '_throwCantUnserializeException'));

        $unserializedValue = @unserialize($value);

		restore_error_handler();

        return $unserializedValue;
	}
	
	/**
	 * Throw can't unserialize exception
	 *
	 * @throws Rediska_Serializer_Exception
	 */
	protected function _throwCantUnserializeException()
	{
		throw new Rediska_Serializer_Adapter_Exception("Can't unserialize string");
	}
}