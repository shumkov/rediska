<?php

/**
 * @see Rediska_Key_Abstract
 */
require_once 'Rediska/Key/Abstract.php';

/**
 * Rediska List key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.2.2
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
		return $this->getRediska()->appendToList($this->_name, $value);
	}
	
	/**
	 * Append value to the head of List
	 * 
	 * @param mixin $value Value
     * @return boolean
	 */
	public function prepend($value)
	{
		return $this->getRediska()->prependToList($this->_name, $value);
	}
	
	/**
     * Get List length
     * 
     * @return integer
	 */
	public function count()
	{
		return $this->getRediska()->getListLength($this->_name);
	}

	/**
	 * Get List values
	 * 
	 * @see Rediska#getList
     * @param integer|string $limitOrSort Limit of elements or sorting query
     *                                    ALPHA work incorrect becouse values in List serailized
     *                                    Read more: http://code.google.com/p/redis/wiki/SortCommand
     * @param integer        $offset      Offset
     * @return array
	 */
    public function toArray($limitOrSort = null, $offset = null)
    {
        return $this->getRediska()->getList($this->_name, $limitOrSort, $offset);
    }
    
    /**
     * Add array to List
     * 
     * @param array $array
     */
    public function fromArray(array $array)
    {
    	foreach($array as $item) {
    		$this->append($item);
    	}

    	return true;
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
        return $this->getRediska()->truncateList($this->_name, $limit, $offset);
    }

    /**
     * Return element of List by index
     * 
     * @param integer $index Index
     * @return mixin
     */
    public function get($index)
    {
        return $this->getRediska()->getFromList($this->_name, $index);
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
    	return $this->getRediska()->setToList($this->_name, $index, $value);
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
        return $this->getRediska()->deleteFromList($this->_name, $value, $count);
    }
    
    /**
     * Return and remove the first element of the List
     * 
     * @return mixin
     */
    public function shift()
    {
    	return $this->getRediska()->shiftFromList($this->_name);
    }

    /**
     * Return and remove the last element of the List
     * 
     * @return mixin
     */
    public function pop()
    {
    	return $this->getRediska()->popFromList($this->_name);
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