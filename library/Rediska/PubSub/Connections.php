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
     * 
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
    
    protected static $_connections = array();

    /**
     * Index
     *
     * @var int
     */
    protected $_index = 0;

    /**
     * Constructor.
     *
     * @param Rediska_PubSub_Channel $channel
     */
    public function __construct(Rediska_PubSub_Channel $channel)
    {
        $this->_channel = $channel;

        // Set specified connection
        if (!$channel->getServerAlias() instanceof Rediska_Connection) {
            $connection = $channel->getServerAlias();
        } elseif ($channel->getServerAlias() !== null) {
            $connection = $channel->getRediska()->getConnectionByAlias($channel->getServerAlias());
        }
    }

    /**
     * Add channel
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
        }
        $this->_channelsByConnections[$connection->getAlias()][] = $name;

        return $connection;
    }

    /**
     *
     * @return Rediska_PubSub_Context
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     *
     * @return Rediska_PubSub_Connection
     */
    public function current()
    {
        $data = $this->_context->getActiveConnections();
        return $data[$this->_i];
    }

    /**
     *
     * @return string
     */
    public function key()
    {
        return $this->_i;
    }

    public function next()
    {
        // Run around
        $this->_i = ++$this->_i % $this->count();
    }

    public function rewind()
    {
        $this->_i = 0;
    }

    public function valid()
    {
        // Valid until there are some active connections
        return $this->count() > 0;
    }

    public function count()
    {
        return count($this->_context->getActiveConnections());
    }
}