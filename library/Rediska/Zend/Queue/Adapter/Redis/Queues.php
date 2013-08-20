<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../../../../Rediska.php';

/**
 * Caching layer for queue adapter to avoid subsequent identical queries on existing queues set.
 *
 * @author Maxim Ivanov
 * @package Rediska
 * @subpackage ZendFrameworkIntegration
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Queue_Adapter_Redis_Queues
{
    /**
     * Queues set
     *
     * @var Rediska_Key_Set
     */
    protected $_queuesSet;

    /**
     * Cached queues set array
     *
     * @var array
     */
    protected $_queuesCache;

    /**
     * Constructor
     *
     * @param Rediska_Key_Set $queuesSet
     */
    public function __construct(Rediska_Key_Set $queuesSet)
    {
        $this->_queuesSet = $queuesSet;
    }

    /**
     * Does a queue already exist?
     *
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        $queuesCache = $this->_getQueuesCache();

        return in_array($name, $queuesCache);
    }

    /**
     * Create a new queue in the queues set
     *
     * @param string $name
     * @return bool
     */
    public function add($name)
    {
        $result = $this->_queuesSet->add($name);

        if ($result) {
            $queuesCache = $this->_getQueuesCache();
            $queuesCache[] = $name;
            $this->_setQueuesCache($queuesCache);
        }

        return $result;
    }

    /**
     * Delete a queue from the queues set
     *
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        $result = $this->_queuesSet->remove($name);

        if ($result) {
            $queuesCache = $this->_getQueuesCache();

            $key = array_search($name, $queuesCache);
            unset($queuesCache[$key]);

            $this->_setQueuesCache($queuesCache);
        }

        return $result;
    }

    /**
     * Get existing queues names
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_getQueuesCache();
    }

    /**
     * Get queues cache. Fetches data from redis on first call.
     *
     * @return array
     */
    protected function _getQueuesCache()
    {
        if ($this->_queuesCache === null) {
            $this->_queuesCache = $this->_queuesSet->toArray();
        }

        return $this->_queuesCache;
    }

    /**
     * Set queues cache
     *
     * @param array $queuesCache
     */
    protected function _setQueuesCache(array $queuesCache)
    {
        $this->_queuesCache = $queuesCache;
    }
}