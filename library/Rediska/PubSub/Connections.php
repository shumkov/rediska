<?php

/**
 * This iterator is used by Rediska_PubSub_Context
 * to repeatedly iterate through available connections
 *
 * @author Yuriy Bogdanov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Connections implements Iterator, Countable
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
     * Pool of connections
     * 
     * @var array
     */
    static protected $_allConnections = array();

    /**
     * Index
     *
     * @var int
     */
    protected $_index = 0;

    /**
     * Constructor
     *
     * @param Rediska_PubSub_Channel $channel
     */
    public function __construct(Rediska_PubSub_Channel $channel)
    {
        $this->_channel = $channel;

        // Set specified connection
        if (!$channel->getServerAlias() instanceof Rediska_Connection) {
            $this->_specifiedConnection = $channel->getServerAlias();
        } elseif ($channel->getServerAlias() !== null) {
            $this->_specifiedConnection = $channel->getRediska()->getConnectionByAlias($channel->getServerAlias());
        }
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
        if (!array_key_exists($connection->getAlias(), self::$_connections)) {
            self::$_connections[$connection->getAlias()] = clone $connection;
        }
        $connection = self::$_connections[$connection->getAlias()];

        // Add channel to connection
        if (!array_key_exists($connection->getAlias(), $this->_channelsByConnections)) {
            $this->_channelsByConnections[$connection->getAlias()] = array();
            $this->_connections[] = $connection;
        }
        if (!in_array($name, $this->_channelsByConnections[$connection->getAlias()])) {
            $this->_channelsByConnections[$connection->getAlias()][] = $name;
        }

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
        if (!isset(self::$_connections[$alias])) {
            throw new Rediska_PubSub_Exception("Can't find connection '$alias'");
        }

        return self::$_connections[$alias];
    }

    public function removeConnectionByChannelName($name)
    {
        
    }

    /**
     *
     * @return Rediska_Connection
     */
    public function current()
    {
        return $this->_connections[$this->_index];
    }

    /**
     *
     * @return integer
     */
    public function key()
    {
        return $this->_index;
    }

    public function next()
    {
        // Run around
        $this->_index = ++$this->_index % $this->count();
    }

    public function rewind()
    {
        $this->_index = 0;
    }

    public function valid()
    {
        // Valid until there are some active connections
        return $this->count() > 0;
    }

    public function count()
    {
        return count($this->_connections);
    }
}