<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../Rediska.php';

/**
 * Rediska List key
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Key objects
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key_List extends Rediska_Key_Abstract implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Append value to the end of List
     *
     * @param mixed             $value              Value
     * @param boolean[optional] $createIfNotExists  Create list if not exists
     * @return boolean
     */
    public function append($value, $createIfNotExists = true)
    {
        $result = $this->_getRediskaOn()->appendToList($this->getName(), $value, $createIfNotExists);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Append value to the head of List
     *
     * @param mixed             $value              Value
     * @param boolean[optional] $createIfNotExists  Create list if not exists
     * @return boolean
     */
    public function prepend($value, $createIfNotExists = true)
    {
        $result = $this->_getRediskaOn()->prependToList($this->getName(), $value, $createIfNotExists);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Get List length
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->_getRediskaOn()->getListLength($this->getName());
    }

    /**
     * Trim the list at key to the specified range of elements
     *
     * @param integer $start Start index
     * @param integer $end End index
     * @return boolean
     */
    public function truncate($start, $end)
    {
        $result = $this->_getRediskaOn()->truncateList($this->getName(), $start, $end);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Return element of List by index
     *
     * @param integer $index Index
     * @return mixed
     */
    public function get($index)
    {
        return $this->_getRediskaOn()->getFromList($this->getName(), $index);
    }

    /**
     * Set a new value as the element at index position of the List
     *
     * @param integer $index Index
     * @param mixed $value Value
     * @return boolean
     */
    public function set($index, $value)
    {
        $result = $this->_getRediskaOn()->setToList($this->getName(), $index, $value);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Insert a new value as the element after the reference value
     *
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return integer|boolean
     */
    public function insertAfter($referenceValue, $value)
    {
        return $this->_getRediskaOn()->insertToListAfter($this->getName(), $referenceValue, $value);
    }

    /**
     * Insert a new value as the element before the reference value
     *
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return integer|boolean
     */
    public function insertBefore($referenceValue, $value)
    {
        return $this->_getRediskaOn()->insertToListBefore($this->getName(), $referenceValue, $value);
    }

    /**
     * Insert a new value as the element before or after the reference value
     *
     * @param string  $position       BEFORE or AFTER
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return integer|boolean
     */
    public function insert($position, $referenceValue, $value)
    {
        return $this->_getRediskaOn()->insertToList($this->getName(), $position, $referenceValue, $value);
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
        $result = $this->_getRediskaOn()->deleteFromList($this->getName(), $value, $count);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Return and remove the first element of the List
     *
     * @return mixed
     */
    public function shift()
    {
        $result = $this->_getRediskaOn()->shiftFromList($this->getName());

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Return and remove the first element of the List and block if list empty or not exists
     *
     * @param $timeout Blocking timeout
     * @return mixed
     */
    public function shiftBlocking($timeout = 0)
    {
        $result = $this->_getRediskaOn()->shiftFromListBlocking($this->getName(), $timeout);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Return and remove the last element of the List
     *
     * @param string|Rediska_Key_List $pushTo After pop, push value to another list
     * @return mixed
     */
    public function pop($pushTo = null)
    {
        if ($pushTo instanceof Rediska_Key_List) {
            $pushTo = $pushTo->getName();
        }

        $result = $this->_getRediskaOn()->popFromList($this->getName(), $pushTo);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Return and remove the last element of the List and block if list empty or not exists.
     *
     * @param integer $timeout[optional]   Blocking timeout. 0 for default - timeout is disabled.
     * @param string  $pushToKey[optional] If not null - push value to another list.
     * @return mixed
     */
    public function popBlocking($timeout = 0, $pushToKey = null)
    {
        $result = $this->_getRediskaOn()->popFromListBlocking($this->getName(), $timeout, $pushToKey);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Get List values
     *
     * @param integer $start                       Start index
     * @param integer $end                         End index
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created
     * @return array
     */
    public function getValues($start = 0, $end = -1, $responseIterator = false)
    {
        return $this->_getRediskaOn()->getList($this->getName(), $start, $end, $responseIterator);
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
        return $this->_getRediskaOn()->sort($this->getName(), $options);
    }

    /**
     * Get List values
     *
     * @param integer $start                       Start index
     * @param integer $end                         End index
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created
     * @return array
     */
    public function toArray($start = 0, $end = -1, $responseIterator = false)
    {
        return $this->getValues($start, $end, $responseIterator);
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
            $pipeline->appendToList($this->getName(), $item);
        }

        if (!is_null($this->getExpire())) {
            $pipeline->expire($this->getName(), $this->getExpire(), $this->isExpireTimestamp());
        }

        $pipeline->execute();

        return true;
    }

    /**
     * Implement intrefaces
     */

    public function count()
    {
        return $this->getLength();
    }

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
