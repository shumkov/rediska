<?php

// Require Rediska
require_once dirname(__FILE__) . '/../Rediska.php';

/**
 * Rediska basic key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Key objects
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key extends Rediska_Key_Abstract implements Countable, ArrayAccess
{
    /**
     * Set key value
     * 
     * @param $value
     * @return boolean
     */
    public function setValue($value)
    {
        if ($this->getExpire() !== null && !$this->isExpireTimestamp()) {
            $reply = $this->setAndExpire($value, $this->getExpire());
        } else {
            $reply = $this->_getRediskaOn()->set($this->getName(), $value);

            if ($reply && !is_null($this->getExpire())) {
                $this->expire($this->getExpire(), $this->isExpireTimestamp());
            }
        }

        return $reply;
    }

    /**
     * Get key value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->_getRediskaOn()->get($this->getName());
    }

    /**
     * Increment integer value
     * 
     * @param integer $amount
     * @return integer
     */
    public function increment($amount = 1)
    {
        return $this->_getRediskaOn()->increment($this->getName(), $amount);
    }

    /**
     * Decrement integer value
     * 
     * @param integer $amount
     * @return integer
     */
    public function decrement($amount = 1)
    {
        return $this->_getRediskaOn()->decrement($this->getName(), $amount);
    }

    /**
     * Set and expire
     *
     * @param mixed   $value
     * @param integer $seconds
     * @return boolean
     */
    public function setAndExpire($value, $seconds)
    {
        return $this->_getRediskaOn()->setAndExpire($this->getName(), $value, $seconds);
    }

    /**
     * Append value
     *
     * @param mixed $value Value
     * @return integer
     */
    public function append($value)
    {
        return $this->_getRediskaOn()->append($this->getName(), $value);
    }

    /**
     * Returns the bit value at offset in the string value stored at key
     *
     * @param integer $offset Offset
     * @param integer $bit    Bit (0 or 1)
     * @return integer
     */
    public function setBit($offset, $bit)
    {
        return $this->_getRediskaOn()->setBit($this->getName(), $offset, $bit);
    }

    /**
     * Returns the bit value at offset in the string value stored at key
     *
     * @param integer $offset Offset
     * @return integer
     */
    public function getBit($offset)
    {
        return $this->_getRediskaOn()->getBit($this->getName(), $offset);
    }

    /**
     * Set range
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @param integer $value  Value
     * @return string
     */
    public function setRange($offset, $value)
    {
        return $this->_getRediskaOn()->setRange($this->getName(), $offset, $value);
    }

    /**
     * Get range
     *
     * @param integer           $start Start
     * @param integer[optional] $end   End. If end is omitted, the substring starting from $start until the end of the string will be returned. For default end of string
     * @return mixin
     */
    public function getRange($start, $end = -1)
    {
        return $this->_getRediskaOn()->getRange($this->getName(), $start, $end);
    }

    /**
     * Get range
     *
     * @deprecated
     * @param integer           $start Start
     * @param integer[optional] $end   End. If end is omitted, the substring starting from $start until the end of the string will be returned. For default end of string
     * @return mixin
     */
    public function substring($start, $end = -1)
    {
        return $this->_getRediskaOn()->substring($this->getName(), $start, $end);
    }

    /**
     * Returns the length of the string
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->_getRediskaOn()->getLength($this->getName());
    }

    /**
     * Get value, if value not present set it from chain method
     *
     * @param mixin[optional]   $object            Object of chain method
     * @param integer[optional] $expire            Expire
     * @param boolean[optional] $expireIsTimestamp If true $expire argument in seconds, or $expire is timestamp
     * @return Rediska_Key_GetOrSetValue
     */
    public function getOrSetValue($object = null, $expire = null, $expireIsTimestamp = false)
    {
        return new Rediska_Key_GetOrSetValue($this, $object, $expire, $expireIsTimestamp);
    }

    /**
     * Magic for get value
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * Implements Countable
     */

    public function count()
    {
        return $this->getLength();
    }

    /**
     * Implements ArrayAccess
     */

    public function offsetExists($offset)
    {
        return (boolean)$this->getBit($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getBit($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            throw new Rediska_Key_Exception("You must specify offset");
        }

        return $this->setBit($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->setBit($offset, 0);
    }
}

/**
 * GetOrSetValue helper class
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
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
     * Expire
     *
     * @var integer
     */
    protected $_expire;

    /**
     * Is expire in seconds
     *
     * @var bool
     */
    protected $_expireIsTimestamp = false;

    /**
     * Construct GetOrSetValue provider
     * 
     * @param Rediska_Key $key
     * @param object      $object Provider object
     * @param integer[optional] $expire            Expire
     * @param boolean[optional] $expireIsTimestamp If true $expire argument in seconds, or $expire is timestamp
     */
    public function __construct(Rediska_Key $key, $object = null, $expire = null, $expireIsTimestamp = false)
    {
        $this->_key               = $key;
        $this->_object            = $object;
        $this->_expire            = $expire;
        $this->_expireIsTimestamp = $expireIsTimestamp;
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
            if ($this->_expire !== null) {
                $this->_key->expire($this->_expire, $this->_expireIsTimestamp);
            }
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