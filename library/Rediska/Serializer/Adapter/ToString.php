<?php

/**
 * ToString adapter convert all values to strings
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Serializer
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Serializer_Adapter_ToString implements Rediska_Serializer_Adapter_Interface
{
    /**
     * Serialize value
     *
     * @param mixed $value
     * @return string
     */
    public function serialize($value)
    {
        return (string)$value;
    }

    /**
     * Unserialize value
     *
     * @param string $value
     * @return string
     */
    public function unserialize($value)
    {
        return $value;
    }
}