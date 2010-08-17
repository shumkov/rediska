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
    protected $_userErrorHandler;
    
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
        $this->_userErrorHandler = set_error_handler(array($this, 'throwCantUnserializeException'));

        $unserializedValue = @unserialize($value);

        restore_error_handler();

        return $unserializedValue;
    }

    /**
     * Throw can't unserialize exception
     *
     * @throws Rediska_Serializer_Exception
     */
    public function throwCantUnserializeException($errno, $errstr, $errfile, $errline, $errcontext)
    {
        restore_error_handler();

        if (!error_reporting() && strpos($errstr, 'unserialize()') !== false) {
            throw new Rediska_Serializer_Adapter_Exception("Can't unserialize value");
        } elseif ($this->_userErrorHandler) {
            call_user_func($this->_userErrorHandler, $errno, $errstr, $errfile, $errline, $errcontext);
        }
    }
}