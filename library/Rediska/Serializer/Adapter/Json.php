<?php

/**
 * JSON adpter
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Serializer
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Serializer_Adapter_Json extends Rediska_Options implements Rediska_Serializer_Adapter_Interface
{
    const DECODE_TYPE_ARRAY = true;
    const DECODE_TYPE_OBJECT = false;

    /**
     * Options
     * 
     * Encode options:
     * 
     * encodeAsObject       - Force encode as object
     * encodeHexQuote       - Encode quote to hex
     * encodeHexTag         - Encode tag to hex
     * encodeHexAmp         - Encode amp to hex
     * encodeHexApos        - Encode apos to hex
     * 
     * 
     * Decode options:
     * 
     * decodeType           - Return associative array or object. Array by default.
     * decodeDepth          - Depth of returned objects. 512 by default.
     * decodeBigintAsString - Convert bigint to strings in returned objects.
     */
    protected $_options = array(
        'encodeasobject'       => false,
        'encodehexquote'       => false,
        'encodehextag'         => false,
        'encodehexamp'         => false,
        'encodehexapos'        => false,
        'decodetype'           => self::DECODE_TYPE_ARRAY,
        'decodedepth'          => 512,
        'decodebigintasstring' => false,
    );

    /**
     * Serialize value
     *
     * @param mixed $value
     * @return string
     */
    public function serialize($value)
    {
        $options = 0;

        if ($this->_options['encodeasobject']) {
            $options = $options | JSON_FORCE_OBJECT;
        }
        if ($this->_options['encodehexquote']) {
            $options = $options | JSON_HEX_QUOT;
        }
        if ($this->_options['encodehextag']) {
            $options = $options | JSON_HEX_TAG;
        }
        if ($this->_options['encodehexamp']) {
            $options = $options | JSON_HEX_AMP;
        }
        if ($this->_options['encodehexapos']) {
            $options = $options | JSON_HEX_APOS;
        }

        $serializedValue = json_encode($value);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Rediska_Serializer_Adapter_Exception("Can't serialize value");
        }

        return $serializedValue;
    }

    /**
     * Unserialize value
     *
     * @param mixed $value
     * @return string
     */
    public function unserialize($value)
    {
        $value = json_decode($value);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Rediska_Serializer_Adapter_Exception("Can't unserialize value");
        }

        return $value;
    }
}