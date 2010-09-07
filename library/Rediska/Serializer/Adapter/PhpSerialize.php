<?php

/**
 * Default PHP serializer adapter
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Serializer
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Serializer_Adapter_PhpSerialize implements Rediska_Serializer_Adapter_Interface
{
    protected $_unserialized = true;
    
    /**
     * Serialize value
     *
     * @param mixed $value
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
     * @return mixed
     */
    public function unserialize($value)
    {
        set_error_handler(array($this, 'catchUnserializeNotice'));

        $unserializedValue = @unserialize($value);

        restore_error_handler();

        if (!$this->_unserialized) {
            $this->_unserialized = true;
            throw new Rediska_Serializer_Adapter_Exception("Can't unserialize value");
        }

        return $unserializedValue;
    }

    /**
     * Catch unserialize notice
     */
    public function catchUnserializeNotice($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (!error_reporting() && strpos($errstr, 'unserialize()') !== false) {
            $this->_unserialized = false;
            return true;
        } else {
            return false;
        }
    }
}