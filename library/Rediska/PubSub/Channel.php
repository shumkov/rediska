<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../Rediska.php';

/**
 * Rediska PubSub channel
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage PublishSubscribe
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Channel extends Rediska_Options_RediskaInstance implements Iterator, ArrayAccess
{
    const SUBSCRIBE     = 'subscribe';
    const UNSUBSCRIBE   = 'unsubscribe';
    const MESSAGE       = 'message';

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
     * Need start time?
     * 
     * @var boolean
     */
    protected $_needStart = true;

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
     * Exception class name for options
     * 
     * @var string
     */
    protected $_optionsException = 'Rediska_PubSub_Exception';

    /**
     * Constructor
     * 
     * @param string|array    $nameOrNames Channel name or array of names
     * @param array[optional] $options     Options:
     *                                         timeout     - Timeout in seconds
     *                                         serverAlias - Server alias or connection object
     *                                         rediska     - Rediska instance name, Rediska object or Rediska options for new instance
     */
    public function __construct($nameOrNames, $options = array())
    {
        $this->setOptions($options);

        $this->_throwIfNotSupported();

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

        $this->_execCommand(self::SUBSCRIBE, $channels);

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

        $this->_execCommand(self::UNSUBSCRIBE, $channels);

        return $this;
    }

    /**
     * Publish a message to channel
     * 
     * @param $message
     * @return int
     */
    public function publish($message)
    {
        $rediska = $this->getRediska();

        if ($this->getServerAlias() !== null) {
            $rediska = $rediska->on($this->getServerAlias());
        }

        return $rediska->publish($this->_subscriptions, $message);
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
     * @param integer[optional] Timeout
     * @return Rediska_PubSub_Response_Message|null
     */
    public function getMessage($timeout = null)
    {
        // Get default timeout
        if (!$timeout && $this->getTimeout()) {
            $timeout = $this->getTimeout();
        }

        // Start timer if not started from iterator
        if ($timeout && $this->_needStart) {
            $this->_timeStart = time();
        }

        // Get message from connections
        while (true) {
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

            if (empty($this->_subscriptions)) {
                return null;
            }

            /* @var $connection Rediska_Connection */
            foreach ($this->_connections as $connection) {
                if ($timeout) {
                    $timeLeft = ($this->_timeStart + $timeout) - time();

                    if ($timeLeft <= 0) {
                        // Reset timeStart if time started from this method
                        if ($this->_needStart) {
                            $this->_timeStart = 0;
                        }

                        return null;
                    }

                    $connection->setReadTimeout($timeLeft);
                } else {
                    $connection->setReadTimeout(600);
                }

                try {
                    $response = $this->_getResponseFromConnection($connection);

                    if ($response === null) {
                        // Sleep before next connection check
                        usleep(10000); // 0.01 sec
                        continue;
                    }

                    if (!in_array($response->getChannel(), $this->_subscriptions)) {
                        $this->_addMessageToBuffer($response);
                        continue;
                    }

                    // Reset timeStart if time started from this method 
                    if ($this->_needStart) {
                        $this->_timeStart = 0;
                    }

                    return $response;
                } catch (Rediska_Connection_TimeoutException $e) {
                    if (!$timeout) {
                        continue;
                    }

                    // Reset timeStart if time started from this method
                    if ($this->_needStart) {
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
     * Set server alias
     * 
     * @param $serverAlias
     * @return Rediska_PubSub_Channel
     */
    public function setServerAlias($serverAlias)
    {
        $this->_serverAlias = $serverAlias;
        
        return $this;
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
        $this->_needStart = false;
        $message = $this->getMessage();
        $this->_needStart = true;

        if ($message) {
            $this->_currentChannel = $message->getChannel();
            $this->_currentMessage = $message->getMessage();

            return true;
        } else {
            $this->_currentChannel = null;
            $this->_currentMessage = null;
            
            $this->_timeStart = 0;

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
    
    /* ArrayAccess implementation */

    public function offsetSet($offset, $value)
    {
        if (!is_null($offset)) {
            throw new Rediska_PubSub_Exception('Offset is not allowed in Rediska_PubSub_Channel');
        }

        $this->publish($value);

        return $value;
    }

    public function offsetExists($value)
    {
        throw new Rediska_PubSub_Exception('Offset is not allowed in Rediska_PubSub_Channel');
    }

    public function offsetUnset($value)
    {
        throw new Rediska_PubSub_Exception('Offset is not allowed in Rediska_PubSub_Channel');
    }

    public function offsetGet($value)
    {
        throw new Rediska_PubSub_Exception('Offset is not allowed in Rediska_PubSub_Channel');
    }

    /**
     * Execute subscribe or unsubscribe command
     * 
     * @param string $command
     * @param array $channels
     */
    protected function _execCommand($command, $channels)
    {
        // Group channels by connections
        $channelsByConnections = array();
        foreach($channels as $channel) {
            $hasSubscription = in_array($channel, $this->_subscriptions);

            if ($command == self::SUBSCRIBE) {
                if ($hasSubscription) {
                    throw new Rediska_PubSub_Exception("You already subscribed to $channel");
                } else {
                    $this->_subscriptions[] = $channel;
                }
            }

            if ($command == self::UNSUBSCRIBE) {
                if (!$hasSubscription) {
                    throw new Rediska_PubSub_Exception("You not subscribed to $channel");
                } else {
                    $key = array_search($channel, $this->_subscriptions);
                    unset($this->_subscriptions[$key]);
                }
            }

            $hasChannel = $this->_connections->hasChannel($channel);

            if (($command == self::SUBSCRIBE && !$hasChannel) || ($command == self::UNSUBSCRIBE && $hasChannel)) {
                switch ($command) {
                    case self::SUBSCRIBE:
                        $connection = $this->_connections->addChannel($channel);
                        break;
                    case self::UNSUBSCRIBE:
                        $connection = $this->_connections->removeChannel($channel);
                        break;
                }

                $connectionAlias = $connection->getAlias();
                if (!array_key_exists($connectionAlias, $channelsByConnections)) {
                    $channelsByConnections[$connectionAlias] = array();
                }
                $channelsByConnections[$connectionAlias][] = $channel;
            }
        }

        // Write commands to connections
        foreach($channelsByConnections as $connectionAlias => $channels) {
            $execCommand = array($command);
            foreach($channels as $channel) {
                $execCommand[] = $this->getRediska()->getOption('namespace') . $channel;
            }
            $connection = $this->_connections->getConnectionByAlias($connectionAlias);
            $exec = new Rediska_Connection_Exec($connection, $execCommand);
            $exec->write();
        }

        // Get responses from connections
        while (!empty($channelsByConnections)) {
            foreach ($channelsByConnections as $connectionAlias => $channels) {
                $connection = $this->_connections->getConnectionByAlias($connectionAlias);

                foreach($channels as $channel) {
                    $response = $this->_getResponseFromConnection($connection);

                    // TODO: May be timeout? Not data or server die()
                    if ($response === null) {
                        continue;
                    }

                    $channel = $response->getChannel();

                    if (($command == self::SUBSCRIBE && $response instanceof Rediska_PubSub_Response_Subscribe)
                     || ($command == self::UNSUBSCRIBE && $response instanceof Rediska_PubSub_Response_Unsubscribe)) {
                        $key = array_search($channel, $channelsByConnections[$connectionAlias]);

                        unset($channelsByConnections[$connectionAlias][$key]);

                        if (empty($channelsByConnections[$connectionAlias])) {
                            unset($channelsByConnections[$connectionAlias]);
                        }
                    } else if ($response instanceof Rediska_PubSub_Response_Message) {
                        $this->_addMessageToBuffer($response);
                    }
                }
            }
        }
    }

    /**
     * Add message response to buffer
     * 
     * @param Rediska_PubSub_Response_Message $message
     */
    protected function _addMessageToBuffer(Rediska_PubSub_Response_Message $message)
    {
        $key = "{$message->getConnection()->getAlias()}-{$message->getChannel()}";

        if (!isset(self::$_messages[$key])) {
            self::$_messages[$key] = array();
        }

        self::$_messages[$key][] = $message;
    }
    
    /**
     * Get response from connection
     *
     * @param Rediska_Connection $connection
     * @return Rediska_PubSub_Response_Abstract
     */
    protected function _getResponseFromConnection(Rediska_Connection $connection)
    {
        $response = Rediska_Connection_Exec::readResponseFromConnection($connection);

        if ($response === null || $response === true) {
            return null;
        }

        list($type, $channel, $body) = $response;

        if ($this->getRediska()->getOption('namespace') !== '' && strpos($channel, $this->getRediska()->getOption('namespace')) === 0) {
            $channel = substr($channel, strlen($this->getRediska()->getOption('namespace')));
        }

        switch ($type) {
            case self::SUBSCRIBE:
                return new Rediska_PubSub_Response_Subscribe($connection, $channel);

            case self::UNSUBSCRIBE:
                return new Rediska_PubSub_Response_Unsubscribe($connection, $channel);

            case self::MESSAGE:
                return new Rediska_PubSub_Response_Message($connection, $channel, $body);

            default:
                throw new Rediska_PubSub_Response_Exception('Unknown reponse type: ' . $type);
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