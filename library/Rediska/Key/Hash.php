<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../Rediska.php';

/**
 * Rediska hash key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Key objects
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key_Hash extends Rediska_Key_Abstract implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Construct key
     *
     * @param string                    $name        Key name
     * @param integer                   $options     Options:
     *                                                  expire            - Expire time
     *                                                  expireIsTimestamp - Expire time is timestamp. For default false (in seconds)
     *                                                  serverAlias       - Server alias or connection object
     *                                                  rediska           - Rediska instance name, Rediska object or Rediska options for new instance
     * @param string|Rediska_Connection $serverAlias Server alias or Rediska_Connection object where key is placed. Deprecated!
     */
    public function  __construct($name, $options = array(), $serverAlias = null)
    {
        parent::__construct($name, $options, $serverAlias);

        $this->_throwIfNotSupported();
    }

    /**
     * Set value to a hash field or fields
     *
     * @param array|string  $fieldOrData  Field or array of many fields and values: field => value
     * @param mixed         $value        Value for single field
     * @param boolean       $overwrite    Overwrite for single field (if false don't set and return false if key already exist). For default true.
     * @return boolean
     */
    public function set($fieldOrData, $value = null, $overwrite = true)
    {
        $result = $this->_getRediskaOn()->setToHash($this->getName(), $fieldOrData, $value, $overwrite);

        if (!is_null($this->getExpire()) && ((!$overwrite && $result) || ($overwrite))) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Magic for set a field
     *
     * @param string $field
     * @param mixed  $value
     * @return boolean
     */
    public function  __set($field, $value)
    {
        $this->set($field, $value);

        return $value;
    }

    /**
     * Array magic for set a field
     *
     * @param string $field
     * @param mixed $value
     * @return boolean
     */
    public function offsetSet($field, $value)
    {
        if (is_null($field)) {
            throw new Rediska_Key_Exception('Field must be present');
        }

        $this->set($field, $value);

        return $value;
    }

    /**
     * Get value from hash field or fields
     *
     * @param string       $name          Key name
     * @param string|array $fieldOrFields Field or fields
     * @return mixed
     */
    public function get($fieldOrFields)
    {
        return $this->_getRediskaOn()->getFromHash($this->getName(), $fieldOrFields);
    }

    /**
     * Magic for get a field
     *
     * @param string $field
     * @return mixed
     */
    public function  __get($field)
    {
        return $this->get($field);
    }

    /**
     * Array magic for get a field
     *
     * @param string $name
     * @return mixed
     */
    public function offsetGet($field)
    {
        return $this->get($field);
    }

    /**
     * Increment field value in hash
     *
     * @param mixed  $field            Field
     * @param number $amount[optional] Increment amount. Default: 1
     * @return integer
     */
    public function increment($field, $amount = 1)
    {
        $result = $this->_getRediskaOn()->incrementInHash($this->getName(), $field, $amount);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Test if field is present in hash
     *
     * @param mixed  $field Field
     * @return boolean
     */
    public function exists($field)
    {
        return $this->_getRediskaOn()->existsInHash($this->getName(), $field);
    }

    /**
     * Magic for test if field is present in hash
     *
     * @param string $field
     * @return boolean
     */
    public function  __isset($field)
    {
        return $this->exists($field);
    }

    /**
     * Array magic for test if field is present in hash
     *
     * @param string $field
     * @return boolean
     */
    public function offsetExists($field)
    {
        return $this->exists($field);
    }

    /**
     * Remove field from hash
     *
     * @param mixed  $field Field
     * @return boolean
     */
    public function remove($field)
    {
        $result = $this->_getRediskaOn()->deleteFromHash($this->getName(), $field);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Magic for remove field from hash
     *
     * @param string $field
     * @return boolean
     */
    public function  __unset($field)
    {
        return $this->remove($field);
    }

    /**
     * Array magic for remove field from hash
     *
     * @param string $field
     * @return boolean
     */
    public function offsetUnset($field)
    {
        return $this->remove($field);
    }

    /**
     * Get hash fields
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->_getRediskaOn()->getHashFields($this->getName());
    }

    /**
     * Get hash values
     * 
     * @return array
     */
    public function getValues()
    {
        return $this->_getRediskaOn()->getHashValues($this->getName());
    }
    
    /**
     * Get hash fields and values
     * 
     * @return array
     */
    public function getFieldsAndValues()
    {
        return $this->_getRediskaOn()->getHash($this->getName());
    }

    /**
     * Get hash length
     *
     * @return intger
     */
    public function getLength()
    {
        return $this->_getRediskaOn()->getHashLength($this->getName());
    }

    /**
     * Get hash as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getFieldsAndValues();
    }

    /* Countable implementation */

    public function count()
    {
        return $this->getLength();
    }

    /* IteratorAggregate implementation */

    public function getIterator()
    {
        return new ArrayObject($this->toArray());
    }

    /**
     * Throw if PubSub not supported by Redis
     */
    protected function _throwIfNotSupported()
    {
        $version = '1.3.10';
        $redisVersion = $this->getRediska()->getOption('redisVersion');
        if (version_compare($version, $this->getRediska()->getOption('redisVersion')) == 1) {
            throw new Rediska_PubSub_Exception("Publish/Subscribe requires {$version}+ version of Redis server. Current version is {$redisVersion}. To change it specify 'redisVersion' option.");
        }
    }
}