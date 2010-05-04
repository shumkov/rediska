<?php

// Require Rediska
if (!class_exists('Rediska')) {
    require_once dirname(__FILE__) . '/../../Rediska.php';
}

/**
 * Rediska List key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key_List extends Rediska_Key_Abstract implements IteratorAggregate, ArrayAccess, Countable
{
	/**
	 * Append value to the end of List
	 * 
	 * @param mixin $value Value
	 * @return boolean
	 */
	public function append($value)
	{
		$result = $this->_getRediskaOn()->appendToList($this->_name, $value);

	    if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
	}
	
	/**
	 * Append value to the head of List
	 * 
	 * @param mixin $value Value
     * @return boolean
	 */
	public function prepend($value)
	{
		$result = $this->_getRediskaOn()->prependToList($this->_name, $value);
		
		if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
	}
	
	/**
     * Get List length
     * 
     * @return integer
	 */
	public function count()
	{
		return $this->_getRediskaOn()->getListLength($this->_name);
	}

    /**
     * Trim the list at key to the specified range of elements
     * 
     * @param integer $start Start index
     * @param integer $end End index
     * @return boolean
     */
    public function truncate($limit, $offset = 0)
    {
        $result = $this->_getRediskaOn()->truncateList($this->_name, $limit, $offset);

        if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }

    /**
     * Return element of List by index
     * 
     * @param integer $index Index
     * @return mixin
     */
    public function get($index)
    {
        return $this->_getRediskaOn()->getFromList($this->_name, $index);
    }
    
    /**
     * Set a new value as the element at index position of the List
     * 
     * @param mixin $value Value
     * @param integer $index Index
     * @return boolean
     */
    public function set($index, $value)
    {
    	$result = $this->_getRediskaOn()->setToList($this->_name, $index, $value);

    	if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }
    
    /**
     * Delete element from list by value
     * 
     * @throws Rediska_Exception
     * @param $value Element value
     * @param $count Limit of deleted items
     * @return integer
     */
    public function remove($value, $count = 0)
    {
        $result = $this->_getRediskaOn()->deleteFromList($this->_name, $value, $count);

        if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }
    
    /**
     * Return and remove the first element of the List
     * 
     * @return mixin
     */
    public function shift()
    {
    	$result = $this->_getRediskaOn()->shiftFromList($this->_name);

    	if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }

    /**
     * Return and remove the last element of the List
     * 
     * @return mixin
     */
    public function pop()
    {
    	$result = $this->_getRediskaOn()->popFromList($this->_name);

    	if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }
    
    /**
     * Get sorted the elements
     * 
     * @param string|array  $value Options or SORT query string (http://code.google.com/p/redis/wiki/SortCommand).
     *                             Important notes for SORT query string:
     *                                 1. If you set Rediska namespace option don't forget add it to key names.
     *                                 2. If you use more then one connection to Redis servers, it will choose by key name,
     *                                    and key by you pattern's may not present on it.
     * @return array
     */
    public function sort($options = array())
    {
        return $this->_getRediskaOn()->sort($this->_name, $options);
    }
    
    /**
     * Get List values
     * 
     * @param integer $start Start index
     * @param integer $end   End index
     * @return array
     */
    public function toArray($start = 0, $end = -1)
    {
        return $this->_getRediskaOn()->getList($this->_name, $start, $end);
    }
    
    /**
     * Add array to List
     * 
     * @param array $array
     */
    public function fromArray(array $array)
    {
        $pipeline = $this->_getRediskaOn()->pipeline();
        foreach($array as $item) {
            $pipeline->appendToList($this->_name, $item);
        }

        if (!is_null($this->_expire)) {
            $pipeline->expire($this->_name, $this->_expire, $this->_isExpireTimestamp);
        }

        $pipeline->execute();

        return true;
    }
    
    /**
     * Implement intrefaces
     */

    public function getIterator()
    {
        return new ArrayObject($this->toArray());
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
        	$this->append($value);
        } else {
        	$this->set($offset, $value);
        }

        return $value;
    }

    public function offsetExists($offset)
    {
        return (boolean)$this->get($offset);
    }

    public function offsetUnset($offset)
    {
        throw new Rediska_Key_Exception("Redis not support delete by index. User 'remove' method for delete by value");
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}