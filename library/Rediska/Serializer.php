<?php

/**
 * Rediska Serializer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Serializer
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Serializer
{
    /**
     * Serializer
     * 
     * @var Rediska_Serializer_Adapter_Interface
     */
    protected $_adapter;

    /**
     * Constuctor
     * 
     * @param mixed $adapter Adapter
     */
    public function __construct($adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * Set adapter
     * 
     * @param $adapter
     * @return Rediska_Serializer
     */
    public function setAdapter($adapter)
    {
        if (is_object($adapter)) {
            $this->_adapter = $adapter;
        } else if (in_array($adapter, array('phpSerialize', 'json', 'toString'))) {
            $adapter = ucfirst($adapter);
            $className = "Rediska_Serializer_Adapter_$adapter";
            $this->_adapter = new $className;
        } else {
            if (!@class_exists($adapter)) {
                throw new Rediska_Serializer_Exception("Serializer adapter '$adapter' not found. You need include it before or setup autoload.");
            }
            $this->_adapter = new $adapter;
        }

        if (!$this->_adapter instanceof Rediska_Serializer_Adapter_Interface) {
            throw new Rediska_Serializer_Exception("'$adapter' must implement Rediska_Serializer_Adapter_Interface");
        }

        return $this;
    }
    
    /**
     * Get adapter
     * 
     * @return Rediska_Serializer_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Serialize value
     * 
     * @param mixed $value Value for serialize
     * @return string
     */
    public function serialize($value)
    {
        if (is_numeric($value) || is_string($value)) {
            return (string)$value;
        } else {
            return $this->_adapter->serialize($value);
        }
    }

    /**
     * Unserailize value
     * 
     * @param string $value Serialized value
     * @return mixed
     */
    public function unserialize($value)
    {
        if (is_null($value)) {
            return null;
        } else if (is_numeric($value)) {
            if (strpos($value, '.') === false) {
                $unserializedValue = (integer)$value;
            } else {
                $unserializedValue = (float)$value;
            }

            if ((string)$unserializedValue != $value) {
                $unserializedValue = $value;
            }
        } else {
            try {
                $unserializedValue = $this->_adapter->unserialize($value);
            } catch (Rediska_Serializer_Adapter_Exception $e) {
                $unserializedValue = $value;
            }
        }

        return $unserializedValue;
    }
}