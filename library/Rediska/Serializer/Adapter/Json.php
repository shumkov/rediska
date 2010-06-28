<?php

/**
 * JSON adpter
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Serializer_Adapter_Json extends Rediska_Options implements Rediska_Serializer_Adapter_Interface
{
	const DECODE_TYPE_ARRAY = true;
	const DECODE_TYPE_OBJECT = false;

    /**
     * Options
     *  
     * decodeType           - Return associative array or object. Array by default.
     * decodeDepth          - Depth of returned objects. 512 by default.
     * decodeBigintAsString - Convert bigint to strings in returned objects.
     * 
     * @todo Add encode options
     */
	protected $_options = array(
		'decodetype'    	   => self::DECODE_TYPE_ARRAY,
		'decodedepth'   	   => 512,
		'decodebigintasstring' => false,
	);

	/**
	 * Serialize value
	 *
	 * @param mixin $value
	 * @return string
	 */
	public function serialize($value)
	{
		return json_encode($value);
	}

	/**
	 * Unserialize value
	 *
	 * @param mixin $value
	 * @return string
	 */
	public function unserialize($value)
	{
		$value = json_decode($value);

		if ($value === NULL) {
			throw new Rediska_Serializer_Exception("Can't unserialize string");
		}

		return $value;
	}
}