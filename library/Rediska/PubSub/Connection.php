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
    protected $_subscribed = array();
    protected $_pending = array();

    /**
     * Creates new Rediska_PubSub_Connection based on Rediska_Connection
     *
     * @param Rediska_Connection $connection
     * @return Rediska_Connection_Subscription
     */
    public static function createFromConnection(Rediska_Connection $connection)
    {
        return new self($connection->getOptions());
    }

    /**
     * Returns the list of subscribed channels
     *
     * @return array
     */
    public function getSubscribedChannels()
    {
        return $this->_subscribed;
    }

    /**
     * Adds channel to list
     *
     */
    public function subscribe($channel)
    {
        array_push($this->_subscribed, $channel);
        $this->removePending($channel);
    }

    /**
     * Removes channel from list
     * 
     */
    public function unsubscribe($channel)
    {
        $index = array_search($channel, $this->_subscribed);
        if ($index !== false) {
            unset($this->_subscribed[$index]);
        }
        $this->removePending($channel);
    }

    /**
     * Clear all channels list
     *
     * @return void
     */
    public function unsubscribeAll()
    {
        $this->_subscribed = array();
    }

    /**
     * Returns true if connection has active channels
     *
     * @return bool
     */
    public function hasSubscriptions()
    {
        return count($this->_subscribed) > 0;
    }

    /**
     * Adds pending channel
     *
     * @param string $channel
     */
    public function addPending($channel)
    {
        array_push($this->_pending, $channel);
    }

    /**
     * Removes pending channel from a list
     *
     * @param string $channel
     */
    public function removePending($channel)
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