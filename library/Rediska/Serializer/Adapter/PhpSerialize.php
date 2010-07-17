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
    const SERIALIZED_FALSE = 'b:0;';

	/**
	 * Serialize value
	 *
	 * @param mixin $value
	 * @return string
	 */
	public function serialize($value)
	{
            if (is_numeric($value) || is_string($value)) {
                return (string)$value;
            } else {
                return serialize($value);
            }
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
        $unserializedValue = @unserialize($value);

        if ($unserializedValue === false && $value != self::SERIALIZED_FALSE) {
            throw new Rediska_Serializer_Adapter_Exception("Can't unserialize string");
        }

        return $unserializedValue;
	}
}