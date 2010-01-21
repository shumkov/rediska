<?php

/**
 * @see Rediska_Key_Abstract
 */
require_once 'Rediska/Key/Abstract.php';

/**
 * Rediska basic key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.3.0
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key extends Rediska_Key_Abstract
{
	/**
	 * Set key value
	 * 
	 * @param $value
	 * @return boolean
	 */
	public function setValue($value)
	{
		$reply = $this->getRediska()->set($this->_name, $value);

		if ($reply && !is_null($this->_expire)) {
		    $this->expire($this->_expire);
		}

		return $reply;
	}

	/**
	 * Get key value
	 * 
	 * @return mixin
	 */
	public function getValue()
	{
		return $this->getRediska()->get($this->_name);
	}

	/**
	 * Get value, if value not present set it from chain method
	 * 
	 * @param $object Object of chain method
	 */
    public function getOrSetValue($object = null)
    {
        return new Rediska_Key_GetOrSetValue($this, $object);
    }

	public function __toString()
	{
		return (string)$this->getValue();
	}
}

/**
 * GetOrSetValue helper class
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.3.0
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key_GetOrSetValue
{
	/**
	 * Key object
	 * 
	 * @var Rediska_Key
	 */
	protected $_key;

	/**
	 * Object provider
	 * 
	 * @var object
	 */
	protected $_object;

	/**
	 * Construct GetOrSetValue provider
	 * 
	 * @param Rediska_Key $key
	 * @param object      $object Provider object
	 */
	public function __construct(Rediska_Key $key, $object = null)
	{
        $this->_key    = $key;
        $this->_object = $object;
	}

    public function __call($method, $args)
    {
    	$value = $this->_key->getValue();

        if (is_null($value)) {
            if (is_null($this->_object)) {
                $callback = $method;
            } else {
                $callback = array($this->_object, $method);
            }
            $value = call_user_func_array($callback, $args);
            $this->_key->setValue($value);
        }

        return $value;
    }
    
    public function __get($attribute)
    {
    	$value = $this->_key->getValue();

        if (is_null($value)) {
            $value = $this->_object->{$attribute};
            $this->_key->setValue($value);
        }

        return $value;
    }
    
    public function __toString()
    {
    	return (string)$this->_object;
    }
}