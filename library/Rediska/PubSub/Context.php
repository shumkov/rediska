<?php

/**
 * This object is for managing PubSub functionality
 *
 * @author Yuriy Bogdanov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Context implements Iterator
{
    const SUBSCRIBE     = 'subscribe';
    const UNSUBSCRIBE   = 'unsubscribe';
    const MESSAGE       = 'message';

    /**
     *
     * @var Rediska_PubSub_Context
     */
    static protected $_instance;

    /**
     *
     * @return Rediska_PubSub_Context
     */
    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /////

    /**
     *
     * @var Rediska
     */
    protected $_rediska;

    /**
     *
     * @var Rediska_PubSub_Command
     */
    protected $_command;
    
    /**
     * The pool of subscription connections
     *
     * @see Rediska_PubSub_Connection
     * @var array
     */
    protected $_connections = array();

    /**
     *
     * @var Rediska_PubSub_ConnectionIterator
     */
    protected $_connectionIterator;

    /**
     *
     * @var int
     */
    protected $_timeout;

    /**
     * The unix timestamp when this iterator was created
     * (used for timeout invalidation)
     *
     * @var int
     */
    protected $_timeStart;

    /**
     * Message buffer
     *
     * @var array
     */
    protected $_buffer = array();

    
    /**
     * Constructor.
     * 
     */
    public function __construct($timeout = null)
    {
        if (!is_null($timeout)) {
            $this->setTimeout($timeout);
        }
    }

    /**
     *
     * @param string|array $keys
     */
    public function subscribe($channels)
    {
        // Handle the list of channels
        if (is_array($channels)) {
            $count = count($channels);
            $this->_bulkCommand(self::SUBSCRIBE, $channels);
        }
        // Handle single channel subscription
        else {
            $count = 1;
            $this->_bulkCommand(self::SUBSCRIBE, array($channels));
        }

        // Here we just ensure that all subscriptions succeed
        
        $command = $this->getCommand();
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
                if ($response['type'] == self::SUBSCRIBE) {
                    $left--;
                }
            }
        }

        // Clear commands list
        $command->reset();
    }

    /**
     *
     * @param string|array[optional] $keys
     */
    public function unsubscribe($channels = null)
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
     * Sets rediska instance
     *
     * @param Rediska $rediska
     */
    public function setRediska(Rediska $rediska)
    {
        $this->_rediska = $rediska;
    }

    /**
     * Gets rediska instance
     *
     * @return Rediska
     */
    public function getRediska()
    {
        if (is_null($this->_rediska)) {
            $this->_rediska = Rediska::getDefaultInstance();
        }
        return $this->_rediska;
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
     *
     * @param Rediska_PubSub_Command $command 
     */
    public function setCommand(Rediska_PubSub_Command $command)
    {
        $this->_command = $command;
    }

    /**
     *
     * @return Rediska_PubSub_Command
     */
    public function getCommand()
    {
        if (is_null($this->_command)) {
            $this->_command = new Rediska_PubSub_Command($this->getRediska(), 'pubsub', array());
        }
        return $this->_command;
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

    /**
     *
     * @param string $type SUBSCRIBE or UNSUBSCRIBE
     * @param array $channels
     */
    protected function _bulkCommand($type, array $channels)
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

    /**
     * Connection response parser & handler
     *
     * @param array $responses
     * @return array
     */
    protected function _parseResponses($responses)
    {
        $results = array();

        foreach ($responses as $response) {

            $result = null;
            
            switch ($response[0]) {

                case self::SUBSCRIBE:

                    $result = array(
                        'type' => $response[0],
                        'channel' => $response[1],
                        'numChannels' => $response[2],
                    );

                    $connection = $this->getConnectionByKeyName($result['channel']);
                    $connection->subscribe($result['channel']);
                    
                    break;

                case self::UNSUBSCRIBE:
                    
                    $result = array(
                        'type' => $response[0],
                        'channel' => $response[1],
                        'numChannels' => $response[2],
                    );

                    $connection = $this->getConnectionByKeyName($result['channel']);
                    $connection->unsubscribe($result['channel']);

                    break;

                case self::MESSAGE:

                    $result = array(
                        'type' => $response[0],
                        'channel' => $response[1],
                        'message' => $response[2],
                    );

                    // Add received message to a buffer
                    array_push($this->_buffer, array($result['channel'], $result['message']));

                    break;

                default:
                    throw new Rediska_Command_Exception('Unknown message type: ' . $response[0]);
            }

            $results[] = $result;
        }

        return $results;
    }


    ////////// Iterator implementation

    
    /**
     * Current channel
     *
     * @var string
     */
    protected $_channel;

    /**
     * Current message
     *
     * @var string
     */
    protected $_message;

    /**
     * Flag determines if the cursor is valid
     *
     * @var bool
     */
    protected $_valid = false;

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
     * Returns false if current context timed out
     *
     * @return bool
     */
    public function checkTimeout()
    {
        return is_null($this->_timeout) || $this->_timeStart + $this->_timeout > time();
    }
}