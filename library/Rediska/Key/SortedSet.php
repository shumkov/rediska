<?php

/**
 * @see Rediska_Key_Abstract
 */
require_once 'Rediska/Key/Abstract.php';

/**
 * Rediska Sorted set key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.3.0
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key_SortedSet extends Rediska_Key_Abstract implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Add the specified member to the Sorted set
     * 
     * @param mixin $value Value
     * @param numeric $score Score
     * @return boolean
     */
    public function add($value, $score)
    {
        $result = $this->getRediska()->addToSortedSet($this->_name, $value, $score);

        if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire);
        }

        return $result;
    }
    
    /**
     * Remove the specified member from the Sorted set
     * 
     * @param mixin $value Value
     * @return boolean
     */
    public function remove($value)
    {
        $result = $this->getRediska()->deleteFromSortedSet($this->_name, $value);

        if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire);
        }

        return $result;
    }
    
    /**
     * Get Sorted set length
     * 
     * @return integer
     */
    public function count()
    {
        return $this->getRediska()->getSortedSetLength($this->_name);
    }

    /**
     * Get Sorted set by score
     * 
     * @param number  $min    Min score
     * @param number  $max    Max score
     * @param integer $limit  Limit
     * @param integer $offset Offset
     * @return array
     */
    public function getByScore($min, $max, $limit = null, $offset = null)
    {
        return $this->getRediska()->getFromSortedSetByScore($this->_name, $min, $max, $limit, $offset);
    }

    /**
     * Get Sorted set values
     * 
     * @param integer|string $limitOrSort Limit of elements or sorting query
     *                                    ALPHA work incorrect becouse values in List serailized
     *                                    Read more: http://code.google.com/p/redis/wiki/SortCommand
     * @param integer        $offset      Offset (not using in sorting)
     * @param boolean        $revert      Revert elements (not used in sorting)
     * @return array
     */
    public function toArray($limitOrSort = null, $offset = null, $revert = false)
    {
        return $this->getRediska()->getSortedSet($this->_name, $limitOrSort, $offset, $revert);
    }

    /**
     * Add array to Sorted set
     * 
     * @param array $array
     */
    public function fromArray(array $array)
    {
        // TODO: Use pipelines
        foreach($array as $score => $value) {
            $this->getRediska()->addToSortedSet($this->_name, $value, $score);
        }

        if (!is_null($this->_expire)) {
            $this->expire($this->_expire);
        }

        return true;
    }

    /**
     * Implement intrefaces
     */

    public function getIterator()
    {
        return new ArrayObject($this->toArray());
    }

    public function offsetSet($score, $value)
    {
        if (is_null($score)) {
            throw new Rediska_Key_Exception('Score must be present');
        }

        $this->add($value, $score);

        return $value;
    }

    public function offsetExists($score)
    {
        return (boolean)$this->offsetGet($score);
    }

    public function offsetUnset($score)
    {
        $value = $this->offsetGet($score);
        if (!is_null($value)) {
            $this->remove($value);

            return true;
        } else {
            return false;
        }
    }

    public function offsetGet($score)
    {
        $values = $this->getByScore($score, $score);

        if (!empty($values)) {
            return $values[0];
        }
    }
}