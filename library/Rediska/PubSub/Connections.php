<?php

/**
 * This iterator is used by Rediska_PubSub_Channel
 * to repeatedly iterate through available connections
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage PublishSubscribe
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Connections implements IteratorAggregate, Countable
{
    /**
     * PubSub channel 
     *
     * @var Rediska_PubSub_Channel
     */
    protected $_channel;
    
    /**
     * Channels by connections
     * 
     * @var array
     */
    protected $_channelsByConnections = array();

    /**
     * Specified by server alias connection
     * 
     * @var Rediska_Connection
     */
    protected $_specifiedConnection;

    /**
     * Connections
     *
     * @var array
     */
    protected $_connections = array();

    /**
     * Constructor
     *
     * @param Rediska_PubSub_Channel $channel
     */
    public function __construct(Rediska_PubSub_Channel $channel)
    {
        $this->_channel = $channel;

        // Set specified connection
        if ($channel->getServerAlias() instanceof Rediska_Connection) {
            $this->_specifiedConnection = $channel->getServerAlias();
        } elseif ($channel->getServerAlias() !== null) {
            $this->_specifiedConnection = $channel->getRediska()->getConnectionByAlias($channel->getServerAlias());
        }
    }
    
    /**
     * Add channel
     * 
     * @param string $channel
     * @return Rediska_Connection
     */
    public function addChannel($channel)
    {
        $connection = $this->getConnectionByChannelName($channel);

        if (!array_key_exists($connection->getAlias(), $this->_channelsByConnections)) {
            $this->_channelsByConnections[$connection->getAlias()] = array();
            $this->_connections[$connection->getAlias()] = $connection;
        }
        if (!in_array($channel, $this->_channelsByConnections[$connection->getAlias()])) {
            $this->_channelsByConnections[$connection->getAlias()][] = $channel;
        }

        return $connection;
    }

    /**
     * Has channel?
     * 
     * @param string $channel
     * @return boolean
     */
    public function hasChannel($channel)
    {
        $connection = $this->getConnectionByChannelName($channel);

        return isset($this->_channelsByConnections[$connection->getAlias()])
            && in_array($channel, $this->_channelsByConnections[$connection->getAlias()]);
    }

    /**
     * Remove channel
     * 
     * @param string $channel
     * @return Rediska_Connection
     */
    public function removeChannel($channel)
    {
        $connection = $this->getConnectionByChannelName($channel);

        $key = array_search($channel, $this->_channelsByConnections[$connection->getAlias()]);
        unset($this->_channelsByConnections[$connection->getAlias()][$key]);

        if (empty($this->_channelsByConnections[$connection->getAlias()])) {
            unset($this->_channelsByConnections[$connection->getAlias()]);
            unset($this->_connections[$connection->getAlias()]);

            // If only one - move to blocking mode
            if (count($this->_connections) == 1) {
                foreach($this->_connections as $connection) {
                    if (!$connection->getOption('blockingMode')) {
                        $connection->setOption('blockingMode', true);
                    }
                }
            }
        }

        return $connection;
    }

    /**
     * Get connection by channel name
     * 
     * @param string $name Channel name
     * @return Rediska_Connection
     */
    public function getConnectionByChannelName($name)
    {
        // Get connection
        if ($this->_specifiedConnection) {
            $connection = $this->_specifiedConnection;
        } else {
            $connection = $this->_channel->getRediska()->getConnectionByKeyName($name);
        }

        if (!array_key_exists($connection->getAlias(), $this->_connections)) {
            $this->_connections[$connection->getAlias()] = clone $connection;

            // If more than one - move to non blocking mode
            if (count($this->_connections) > 1) {
                foreach($this->_connections as $connection) {
                    if ($connection->getOption('blockingMode')) {
                        $connection->setOption('blockingMode', false);
                    }
                }
            }
        }
        $connection = $this->_connections[$connection->getAlias()];

        return $connection;
    }

    /**
     * Get connection by alias
     * 
     * @param string $alias
     * @return Rediska_Connection
     */
    public function getConnectionByAlias($alias)
    {
        if (!isset($this->_connections[$alias])) {
            throw new Rediska_PubSub_Exception("Can't find connection '$alias'");
        }

        return $this->_connections[$alias];
    }

    /**
     * Get channels by connection
     * 
     * @param $connection
     * @return array
     */
    public function getChannelsByConnection(Rediska_Connection $connection)
    {
        if (!isset($this->_channelsByConnections[$connection->getAlias()])) {
            throw new Rediska_PubSub_Exception("Channels by this connection not present");
        }

        return $this->_channelsByConnections[$connection->getAlias()];
    }

    /* IteratorAggregate implementation */

    public function getIterator()
    {
        return new ArrayObject($this->_connections);
    }

    /* Countable implementation */

    public function count()
    {
        return count($this->_connections);
    }
}