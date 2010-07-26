<?php

// Require Rediska
if (!class_exists('Rediska')) {
    require_once dirname(__FILE__) . '/../../Rediska.php';
}

/**
 * This object is for managing PubSub functionality
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Channel implements Iterator
{
    const SUBSCRIBE     = 'subscribe';
    const UNSUBSCRIBE   = 'unsubscribe';
    const MESSAGE       = 'message';

    /**
     * Rediska instance
     *
     * @var Rediska
     */
    protected $_rediska;

    /**
     * Subscriptions
     * 
     * @var array
     */
    protected $_subscriptions = array();

    /**
     * The pool of subscription connections
     * 
     * @var Rediska_PubSub_Connections
     */
    protected $_connections;

    /**
     * Timeout in seconds
     * 
     * @var int
     */
    protected $_timeout;

    /**
     * The unix timestamp when started getting a message
     *
     * @var int
     */
    protected $_timeStart;
    
    /**
     * Server alias or connection object
     * 
     * @var string|Rediska_Connection
     */
    protected $_serverAlias;
    
    /**
     * Current channel for iterator
     *
     * @var string
     */
    protected $_currentChannel;

    /**
     * Current message for iterator
     *
     * @var string
     */
    protected $_currentMessage;

    /**
     * Message buffer
     *
     * @var array
     */
    static protected $_messages = array();

    /**
     * Constructor
     * 
     * @var string|array              $nameOrNames Channel name or array of names
     * @var int                       $timeout     Timeout in seconds
     * @var string|Rediska_Connection $serverAlias Server alias or connection object
     */
    public function __construct($nameOrNames, $timeout = null, $serverAlias = null)
    {
        $this->_setupRediskaDefaultInstance();
        $this->_throwIfNotSupported();

        $this->setTimeout($timeout);

        $this->_serverAlias = $serverAlias;
        $this->_connections = new Rediska_PubSub_Connections($this);

        $this->subscribe($nameOrNames);
    }

    /**
     * Subscribe to channel or channels
     *
     * @param string|array $channelOrChannels Channel name or names
     * @return Rediska_PubSub_Channel
     */
    public function subscribe($channelOrChannels)
    {
        if (!is_array($channelOrChannels)) {
            $channels = array($channelOrChannels);
        } else {
            $channels = $channelOrChannels;
        }

        // Group channels by connections
        $channelsByConnections = array();
        foreach($channels as $channel) {
            if (in_array($channel, $this->_subscriptions)) {
                throw new Rediska_PubSub_Exception("You already subscribed to $channel");
            }

            $this->_subscriptions[] = $channel;

            if (!$this->_connections->hasChannel($channel)) {
                $connection = $this->_connections->addChannel($channel);

                $connectionAlias = $connection->getAlias();
                if (!array_key_exists($connectionAlias, $channelsByConnections)) {
                    $channelsByConnections[$connectionAlias] = array();
                }
                $channelsByConnections[$connectionAlias][] = $channel;
            }
        }

        // Write commands to connections
        foreach($channelsByConnections as $connectionAlias => $channles) {
            $command = array(self::SUBSCRIBE);
            foreach($channles as $channel) {
                $command[] = $this->getRediska()->getOption('namespace') . $channel;
            }
            $connection = $this->_connections->getConnectionByAlias($connectionAlias);
            $subscribe = new Rediska_Connection_Exec($connection, $command);
            $subscribe->write();
        }

        // Get subscribe responses from connections
        while (!empty($channelsByConnections)) {
            foreach ($channelsByConnections as $connectionAlias => $channels) {
                $connection = $this->_connections->getConnectionByAlias($connectionAlias);

                foreach($channels as $channel) {
                    $response = $this->_getResponseFromConnection($connection);

                    if ($response instanceof Rediska_PubSub_Response_Subscribe) {
                        $channel = $response->getChannel();
                        $key = array_search($channel, $channelsByConnections[$connectionAlias]);
                        unset($channelsByConnections[$connectionAlias][$key]);

                        if (empty($channelsByConnections[$connectionAlias])) {
                            unset($channelsByConnections[$connectionAlias]);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Unsubscribe from channel or channels
     * If $keys is passed - we unsubscribe from all channels 
     *
     * @param string|array[optional] $channelOrChannels
     * @return Rediska_PubSub_Channel
     */
    public function unsubscribe($channelOrChannels = null)
    {
        if (is_null($channelOrChannels)) {
            $channels = $this->_subscriptions;
        } elseif (!is_array($channelOrChannels)) {
            $channels = array($channelOrChannels);
        } else {
            $channels = $channelOrChannels;
        }
        
        // Group channels by connections
        $channelsByConnections = array();
        foreach($channels as $channel) {
            if (!in_array($channel, $this->_subscriptions)) {
                throw new Rediska_PubSub_Exception("You not subscribed to $channel");
            }

            if ($this->_connections->hasChannel($channel)) {
                $this->_connections->removeChannel($channel);

                $connection = $this->_connections->getConnectionByChannelName($channel);                

                $connectionAlias = $connection->getAlias();
                if (!array_key_exists($connectionAlias, $channelsByConnections)) {
                    $channelsByConnections[$connectionAlias] = array();
                }
                $channelsByConnections[$connectionAlias][] = $channel;
            }
        }

        // Write commands to connections
        foreach($channelsByConnections as $connectionAlias => $channles) {
            $command = array(self::UNSUBSCRIBE);
            foreach($channles as $channel) {
                $command[] = $this->getRediska()->getOption('namespace') . $channel;
            }
            $connection = $this->_connections->getConnectionByAlias($connectionAlias);
            $subscribe = new Rediska_Connection_Exec($connection, $command);
            $subscribe->write();
        }

        // Get unsubscribe responses from connections
        while (!empty($channelsByConnections)) {
            foreach ($channelsByConnections as $connectionAlias => $channels) {
                $connection = $this->_connections->getConnectionByAlias($connectionAlias);

                foreach($channels as $channel) {
                    $response = $this->_getResponseFromConnection($connection);

                    if ($response instanceof Rediska_PubSub_Response_Unsubscribe) {
                        $channel = $response->getChannel();

                        $key = array_search($channel, $channelsByConnections[$connectionAlias]);
                        unset($channelsByConnections[$connectionAlias][$key]);
                        if (empty($channelsByConnections[$connectionAlias])) {
                            unset($channelsByConnections[$connectionAlias]);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Has subscriptions?
     * 
     * @return boolean
     */
    public function hasSubscriptions()
    {
        return !empty($this->_subscriptions);
    }

    /**
     * Get subscriptions
     * 
     * @return array
     */
    public function getSubscriptions()
    {
        return $this->_subscriptions;
    }

    /**
     * Get message
     *
     * @return Rediska_PubSub_Response_Message|null
     */
    public function getMessage()
    {
        // Try to get message from buffer
        if (!empty(self::$_messages)) {
            /* @var $connection Rediska_Connection */
            foreach($this->_connections as $connection) {
                $channels = $this->_connections->getChannelsByConnection($connection);
                foreach($channels as $channel) {
                    $key = "{$connection->getAlias()}-$channel";
                    if (isset(self::$_messages[$key])) {
                        $message = array_shift(self::$_messages[$key]);
                        if (empty(self::$_messages[$key])) {
                            unset(self::$_messages[$key]);
                        }

                        return $message;
                    }
                }
            }
        }

        // Start timer if not started from iterator
        if ($this->_timeout && !$this->_timeStart) {
            $timeStartedFromThis = true;
            $this->_timeStart = time();
        }

        // Get message from connections
        while (true) {
            /* @var $connection Rediska_Connection */
            foreach($this->_connections as $connection) {
                if ($this->_timeout) {
                    $timeLeft = time() - ($this->_timeStart + $this->_timeout);

                    if (!$timeLeft) {
                        // Reset timeStart if time started from this method
                        if (isset($timeStartedFromThis)) {
                            $this->_timeStart = 0;
                        }
                        
                        return null;
                    }

                    $connection->setReadTimeout($timeLeft);
                }

                try {
                    $response = $this->_getResponseFromConnection($connection);

                    // Reset timeStart if time started from this method 
                    if (isset($timeStartedFromThis)) {
                        $this->_timeStart = 0;
                    }

                    return $response;
                } catch (Rediska_Connection_TimeoutException $e) {
                    if (!$this->_timeout) {
                        throw $e;
                    }

                    // Reset timeStart if time started from this method
                    if (isset($timeStartedFromThis)) {
                        $this->_timeStart = 0;
                    }

                    return null;
                }
            }
        }
    }

    /**
     * Set timeout in seconds
     *
     * @return Rediska_PubSub_Channel
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = (int)$timeout;
        
        return $this;
    }

    /**
     * Get timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }
    
    /**
     * Get server alias
     * 
     * @return Rediska_Connection|string
     */
    public function getServerAlias()
    {
        return $this->_serverAlias;
    }
    
    /**
     * Set Rediska instance
     * 
     * @param Rediska $rediska
     * @return Rediska_PubSub_Channel
     */
    public function setRediska(Rediska $rediska)
    {
        $this->_rediska = $rediska;
        
        return $this;
    }

    /**
     * Get Rediska instance
     * 
     * @return Rediska
     */
    public function getRediska()
    {
        if (!$this->_rediska instanceof Rediska) {
            throw new Rediska_PubSub_Exception('Rediska instance not found for PubSub channel');
        }

        return $this->_rediska;
    }

    /* Iterator implementation */
    
    public function rewind()
    {
        if ($this->_timeout) {
            $this->_timeStart = time();
        }
    }

    public function next()
    {
        
    }

    public function valid()
    {
        $message = $this->getMessage();

        if ($message) {
            $this->_currentChannel = $message->getChannel();
            $this->_currentMessage = $message->getMessage();

            return true;
        } else {
            return false;
        }
    }

    public function key()
    {
        return $this->_currentChannel;
    }

    public function current()
    {
        return $this->_currentMessage;
    }
    
    /**
     * Get response from connection
     *
     * @param Rediska_Connection $connection
     * @return Rediska_PubSub_Response_Abstract
     */
    protected function _getResponseFromConnection(Rediska_Connection $connection)
    {
        list($type, $channel, $body) = Rediska_Connection_Exec::readResponseFromConnection($connection);

        if ($this->getRediska()->getOption('namespace') !== '' && strpos($channel, $this->getRediska()->getOption('namespace')) === 0) {
            $channel = substr($channel, strlen($this->getRediska()->getOption('namespace')));
        }

        switch ($type) {
            case self::SUBSCRIBE:
                return new Rediska_PubSub_Response_Subscribe($channel);

            case self::UNSUBSCRIBE:
                return new Rediska_PubSub_Response_Unsubscribe($channel);

            case self::MESSAGE:
                $message = new Rediska_PubSub_Response_Message($channel, $body);

                $key = "{$connection->getAlias()}-{$channel}";

                if (!isset(self::$_messages[$key])) {
                    self::$_messages[$key] = array();
                }

                self::$_messages[$key][] = $message;

                return $message;

            default:
                throw new Rediska_PubSub_Response_Exception('Unknown reponse type: ' . $type);
        }
    }

    /**
     * Setup Rediska instance
     */
    protected function _setupRediskaDefaultInstance()
    {
        $this->_rediska = Rediska::getDefaultInstance();
        if (!$this->_rediska) {
            $this->_rediska = new Rediska();
        }
    }

    /**
     * Throw if PubSub not supported by Redis
     */
    protected function _throwIfNotSupported()
    {
        $version = '1.3.8';
        $redisVersion = $this->getRediska()->getOption('redisVersion');
        if (version_compare($version, $this->getRediska()->getOption('redisVersion')) == 1) {
            throw new Rediska_PubSub_Exception("Publish/Subscribe requires {$version}+ version of Redis server. Current version is {$redisVersion}. To change it specify 'redisVersion' option.");
        }
    }
}