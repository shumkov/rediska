<?php

/**
 * Rediska PubSub connection
 * 
 * @author Yuriy Bogdanov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Connection extends Rediska_Connection
{
    /**
     * Subscribed channels
     * 
     * @var array
     */
    protected $_subscriptions = array();

    /**
     * Pending channels
     * 
     * @var array
     */
    protected $_pending = array();

    /**
     * Creates new Rediska_PubSub_Connection based on Rediska_Connection
     *
     * @param Rediska_Connection $connection
     * @return Rediska_PubSub_Connection
     */
    public static function createFromConnection(Rediska_Connection $connection)
    {
        return new self($connection->getOptions());
    }

    /**
     * Adds channel to list
     * 
     * @var string $channel
     * @return Rediska_PubSub_Connection
     */
    public function subscribe($channel)
    {
        array_push($this->_subscriptions, $channel);
        $this->removePending($channel);
        
        return $this;
    }

    /**
     * Removes channel from list
     * 
     * @var string $channel
     * @return Rediska_PubSub_Connection;
     */
    public function unsubscribe($channel)
    {
        $index = array_search($channel, $this->_subscriptions);
        if ($index !== false) {
            unset($this->_subscriptions[$index]);
        }
        $this->removePending($channel);
        
        return $this;
    }

    /**
     * Clear all channels list
     *
     * @return void
     */
    public function unsubscribeAll()
    {
        $this->_subscriptions = array();
    }
    
    /**
     * Returns the list of subscribed channels
     *
     * @return array
     */
    public function getSubscribedChannels()
    {
        return $this->_subscriptions;
    }

    /**
     * Returns true if connection has active channels
     *
     * @return bool
     */
    public function hasSubscriptions()
    {
        return count($this->_subscriptions) > 0;
    }

    /**
     * Adds pending channel
     *
     * @param string $channel
     */
    public function addPendingChannel($channel)
    {
        array_push($this->_pending, $channel);
    }

    /**
     * Removes pending channel from a list
     *
     * @param string $channel
     */
    public function removePendingChannel($channel)
    {
        $index = array_search($channel, $this->_pending);
        if ($index !== false) {
            unset($this->_pending[$index]);
        }
    }

    /**
     * Returns the list of pending channels
     *
     * @return array
     */
    public function getPendingChannels()
    {
        return $this->_pending;
    }

    /**
     * Returns true if connection has pending channels
     *
     * @return bool
     */
    public function hasPendingChannels()
    {
        return count($this->_pending) > 0;
    }

    /**
     * Clears pending channels list
     * 
     */
    public function clearPendingChannels()
    {
        $this->_pending = array();
    }
}