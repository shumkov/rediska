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
    protected $_subscribed = false;

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
     * Message buffer
     *
     * @var array
     */
    protected static $_messages = array();

    /**
     * Timeout
     * 
     * @var mixin
     */
    protected $_timeout;

    /**
     * Server alias or connection object
     * 
     * @var string|Rediska_Connection
     */
    protected $_serverAlias;
    
    
    
    
    

    
    
    
    

    /**
     * Current channel
     *
     * @var string
     */
    protected $_currentChannel;

    /**
     * Current message
     *
     * @var string
     */
    protected $_currentMessage;

    /**
     * Flag determines if the cursor is valid
     *
     * @var bool
     */
    protected $_iteratorValid = false;
    
    



    /**
     * The unix timestamp when this iterator was created
     * (used for timeout invalidation)
     *
     * @var int
     */
    protected $_timeStart;

    

    /**
     * Constructor
     * 
     * @var string|array              $nameOrNames Channel name or array of names
     * @var mixin                     $timeout     Timeout
     * @var string|Rediska_Connection $serverAlias Server alias or connection object
     */
    public function __construct($nameOrNames, $timeout = null, $serverAlias = null)
    {
        $this->_setupRediskaDefaultInstance();
        $this->_throwIfNotSupported();

        $this->subscribe($nameOrNames);

        $this->_serverAlias = $serverAlias;
        $this->_timeout     = $timeout;

        $this->_connections = new Rediska_PubSub_Connections($this);
    }

    /**
     * Subscribe to channel or channels
     *
     * @param string|array $channelOrChannels Channel name or names
     */
    public function subscribe($channelOrChannels)
    {
        if (!is_array($channelOrChannels)) {
            $channels = array($channelOrChannels);
        } else {
            $channels = $channelOrChannels;
        }

        // Check if already subscribed
        foreach($channels as $channel) {
            if (in_array($channel, $this->_subscriptions)) {
                throw new Rediska_PubSub_Exception("You already subscribed to $channel");
            }
        }

        if (!$this->_subscribed) {
            // Add subscriptions for init
            $this->_subscriptions += $channels;
        } else {
            // Subscribe now!
            $channelsByConnections = array();
            foreach($channels as $channel) {
                $connection = $this->_connections->getConnectionByChannelName($channel);
 
                $connectionAlias = $connection->getAlias();
                if (!array_key_exists($connectionAlias, $channelsByConnections)) {
                    $channelsByConnections[$connectionAlias] = array();
                }
                $channelsByConnections[$connectionAlias][] = $channel;
            }

            foreach($channelsByConnections as $connectionAlias => $channles) {
                $command = array(self::SUBSCRIBE);
                foreach($channles as $channel) {
                    $command[] = $this->getRediska()->getOption('namespace') . $channel;
                }
                $connection = $this->_connections->getConnectionByAlias($connectionAlias);
                $subscribe = new Rediska_Connection_Exec($connection, $command);
                $subscribe->write();
            }

            // Here we just ensure that all subscriptions succeed
            $left = count($channels);

            while ($left > 0) {

                $responses = array();
    
                foreach ($command->getCommandsByConnections() as $commandByConnection) {
                    list($connection) = $commandByConnection;
                    // Do not request to this connection if there are no pending channels
                    if (!$connection->hasPendingChannels()) {
                        continue;
                    }
                    $responses[] = $this->_readFromConnection($connection);
                }

                foreach ($responses as $response) {
                    if ($response['type'] == self::SUBSCRIBE) {
                        $left--;
                    }
                }
            }
    
            // Clear commands list
            $command->reset();
            
            
            
            
            
            
            
        }
        
        
        // Handle the list of channels
        if (is_array($channels)) {
            $count = count($channels);
            $this->_bulkCommand(self::SUBSCRIBE, $channels);
        }
        // Handle single channel subscription
        else {
            $count = 1;
            
        }

        
    }
    
    
    /**
     * Connection response parser & handler
     *
     * @param array $responses
     * @return array
     */
    protected function _getResponse($response)
    {
        $channel = $response[1];

        if ($this->getRediska()->getOption('namespace') !== '' && strpos($channel, $this->getRediska()->getOption('namespace')) === 0) {
            $channel = substr($channel, strlen($this->getRediska()->getOption('namespace')));
        }

        switch ($response[0]) {
            case self::SUBSCRIBE:
                return new Rediska_PubSub_Response_Subscribe($channel);

            case self::UNSUBSCRIBE:
                return new Rediska_PubSub_Response_Unsubscribe($channel);

            case self::MESSAGE:
                $message = new Rediska_PubSub_Response_Message($channel, $response[2]);

                $this->_messages[] = $message;

                return $message;

            default:
                throw new Rediska_PubSub_Response_Exception('Unknown reponse type: ' . $response[0]);
        }
    }
    
    
    
    
    
    
/**
     *
     * @param string $type SUBSCRIBE or UNSUBSCRIBE
     * @param array $channels
     */
    protected function _($type, array $channels)
    {
        // Clear the list of pending channels for all connections
        // just in case..
        foreach ($this->_connections as $connection) {
            $connection->clearPendingChannels();
        }

        $command = $this->getCommand();

        // Group channels by different connections
        $connections = array();
        foreach ($channels as $channel) {
            $connection = $this->getConnectionByKeyName($channel);
            $connectionAlias = $connection->getAlias();
            if (!array_key_exists($connectionAlias, $connections)) {
                $connections[$connectionAlias] = $connection;
            }
            $connection->addPending($channel);
        }

        // Add command for each connection "(UN)SUBSCRIBE key1 key2 keyN"
        $rediska = $this->getRediska();
        foreach($connections as $connection) {
            $commandStr = $type;
            foreach($connection->getPendingChannels() as $channel) {
                $commandStr .= ' ' . $rediska->getOption('namespace') . $channel;
            }
            $command->addCommandByConnection($connection, $commandStr);
        }

        $command->write();
    }
    
    
    

    public function hasSubscriptions()
    {
        return !empty($this->_subscriptions);
    }

    public function getSubscriptions()
    {
        return $this->_subscriptions;
    }

    /**
     *
     * @param string|array[optional] $keys
     */
    public function removeSubscription($channels = null)
    {
        // Handle bulk unsubscription
        if (is_array($channels)) {
            $count = count($channels);
            $this->_bulkCommand(self::UNSUBSCRIBE, $channels);
        }
        // Handle single unsubscription
        else if (!is_null($channels)) {
            $count = 1;
            $this->_bulkCommand(self::UNSUBSCRIBE, array($channels));
        }
        // Process sunsubscription from all
        else {
            // Collect all active channels
            $channels = array();
            foreach($this->getActiveConnections() as $connection) {
                $channels = array_merge($channels, $connection->getSubscribedChannels());
            }
            $count = count($channels);
            $this->_bulkCommand(self::UNSUBSCRIBE, $channels);
        }

        // Here we just ensure that all unsubscribe commands succeed

        $command = $this->getCommand();
        $result = array();
        $left = $count;
        while ($left > 0) {

            $responses = array();

            foreach ($command->getCommandsByConnections() as $commandByConnection) {
                list($connection) = $commandByConnection;
                // Do not request to this connection if there are no pending channels
                if (!$connection->hasPendingChannels()) {
                    continue;
                }
                $responses[] = $this->_readFromConnection($connection);
            }

            foreach ($responses as $response) {
                if ($response['type'] == self::UNSUBSCRIBE) {
                    $left--;
                }
            }
        }

        // Clear commands list
        $command->reset();

        return $result;
    }
    
    public function clearSubscriptions()
    {
        
    }
    
    
    /**
     * Set Rediska instance
     * 
     * @param Rediska $rediska
     * @return Rediska_Key_Abstract
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

    public function getServerAlias()
    {
        return $this->_serverAlias;
    }



    /**
     * Gets a single message from any subscribed connection
     *
     * @return array
     */
    public function getMessage()
    {
        $connectionIterator = $this->getConnectionIterator();

        $message = null;
        while (!$message) {
            // Check if current context is timed out
            if (!$this->checkTimeout()) {
                return false;
            }
            // Check connection interator
            if (!$connectionIterator->valid()) {
                return false;
            }
            // Try to retrieve message
            try {
                // If returned message is not empty, it means that we have something in buffer
                $connection = $connectionIterator->current();
                $connectionIterator->next();
                $response = $this->_readFromConnection($connection);
                if ($response['type'] !== self::MESSAGE) {
                    return null;
                }
                return $response;
            }
            catch (Rediska_Connection_TimeoutException $e) {
            }
        }
    }

    /**
     * Returns Rediska_Connection_Subscribe by alias
     *
     * @param string $alias
     * @return Rediska_Connection_Subscribe
     */
    public function getConnectionByAlias($alias)
    {
        if (!array_key_exists($alias, $this->_connections)) {
            $connection = $this->getRediska()->getConnectionByAlias($alias);
            $newConnection = Rediska_PubSub_Connection::createFromConnection($connection);
            $this->_connections[$alias] = $newConnection;
        }
        return $this->_connections[$alias];
    }

    /**
     * Returns Rediska_Connection_Subscribe by key name
     *
     * @param string $key
     */
    public function getConnectionByKeyName($key)
    {
        $rediskaConnection = $this->getRediska()->getConnectionByKeyName($key);
        $connectionAlias = $rediskaConnection->getAlias();
        $connection = $this->getConnectionByAlias($connectionAlias);
        return $connection;
    }

    /**
     * Returns the list of connections which has active channels
     *
     * @return array
     */
    public function getActiveConnections()
    {
        $connections = array();
        foreach ($this->_connections as $connection) {
            if ($connection->hasSubscriptions()) {
                $connections[] = $connection;
            }
        }

        return $connections;
    }

    /**
     * Sets current global timeout
     *
     * @return int
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = (int)$timeout;
    }

    /**
     * Returns specified timeout in seconds
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * Sets connection iterator
     *
     * @param Iterator $iterator
     */
    public function setConnectionIterator(Iterator $iterator)
    {
        $this->_connectionIterator = $iterator;
    }

    /**
     * Gets connection iterator
     *
     * @return Rediska_PubSub_ConnectionIterator
     */
    public function getConnectionIterator()
    {
        if (is_null($this->_connectionIterator)) {
            $this->_connectionIterator = new Rediska_PubSub_ConnectionIterator($this);
        }
        return $this->_connectionIterator;
    }

    /**
     * This function does a single read from Rediska_PubSub_Connection
     * Then parse it and evaluate
     *
     */
    public function _readFromConnection(Rediska_PubSub_Connection $connection)
    {
        $command = $this->getCommand();
        $response = $command->readFromConnection($connection);
        $parsed = $this->_parseResponses(array($response));
        return $parsed[0];
    }

    

    


    ////////// Iterator implementation

    

    
    
    

    /**
     * Returns false if current context timed out
     *
     * @return bool
     */
    public function checkTimeout()
    {
        return is_null($this->_timeout) || $this->_timeStart + $this->_timeout > time();
    }
    

    /**
     * Returns current channel
     *
     * @return string
     */
    public function key()
    {
        return $this->_channel;
    }

    /**
     *
     * @return mixed
     */
    public function current()
    {
        return $this->_message;
    }

    /**
     * Returns current message
     * 
     * @return string
     */
    public function next()
    {
        // If the buffer is empty, try to fetch new message
        if (count($this->_buffer) == 0) {
            $this->_valid = (bool)$this->getMessage();
        }
        // If there is something in buffer (after fetching message, maybe)
        if (count($this->_buffer) > 0) {
            list($this->_channel, $this->_message) = array_shift($this->_buffer);
        }
    }

    public function rewind()
    {
        // Here we can reset timer
        $this->_timeStart = time();
        // Get the first message, fill the buffer
        if (!count($this->_buffer)) {
            $this->_valid = (bool)$this->getMessage();
        }
        // If there is something in buffer (after fetching message, maybe)
        // shift to it
        if (count($this->_buffer) > 0) {
            $this->next();
        }
    }

    public function valid()
    {
        // see rewind() and next()
        return $this->_valid;
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
            throw new Rediska_PubSub_Exception("Transaction requires {$version}+ version of Redis server. Current version is {$redisVersion}. To change it specify 'redisVersion' option.");
        }
    }
}