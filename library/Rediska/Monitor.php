<?php

// Require Rediska
require_once dirname(__FILE__) . '/../Rediska.php';

/**
 * Rediska monitor
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Monitor extends Rediska_Options_RediskaInstance implements Iterator
{
    /**
     * Connections
     *
     * @var Rediska_Monitor_Connections
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
     * Current timestamp for iterator
     *
     * @var string
     */
    protected $_currentTimestamp;

    /**
     * Current command for iterator
     *
     * @var string
     */
    protected $_currentCommand;
    
    /**
     * Exception class name for options
     * 
     * @var string
     */
    protected $_optionsException = 'Rediska_Monitor_Exception';

    /**
     * Constructor
     * 
     * @param array[optional] $options Options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);

        $this->_connections = new Rediska_Monitor_Connections($this);
    }

    /**
     * Get command
     *
     * @return array
     */
    public function getCommand($timeout = null)
    {
        if (!$timeout && $this->getTimeout()) {
            $timeout = $this->getTimeout();
        }

        // Get message from connections
        while (true) {
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
                }

                try {
                    $response = $this->_getResponseFromConnection($connection);

                    if ($response === null) {
                        // Sleep before next connection check
                        usleep(10000); // 0.01 sec
                        continue;
                    }

                    // Reset timeStart if time started from this method 
                    if ($this->_needStart) {
                        $this->_timeStart = 0;
                    }

                    return $response;
                } catch (Rediska_Connection_TimeoutException $e) {
                    if (!$timeout) {
                        throw $e;
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
        $commandAndTimestamp = $this->getCommand();
        $this->_needStart = true;

        if ($commandAndTimestamp) {
            list($this->_currentTimestamp, $this->_currentCommand) = $commandAndTimestamp;

            return true;
        } else {
            $this->_currentTimestamp = null;
            $this->_currentCommand = null;

            $this->_timeStart = 0;

            return false;
        }
    }

    public function key()
    {
        return $this->_currentTimestamp;
    }

    public function current()
    {
        return $this->_currentCommand;
    }

    /**
     * Get response from connection
     *
     * @param Rediska_Connection $connection
     * @return array|null
     */
    protected function _getResponseFromConnection(Rediska_Connection $connection)
    {
        $response = Rediska_Connection_Exec::readResponseFromConnection($connection);

        if ($response === null || $response === true) {
            return null;
        }

        $timestampAndCommand = explode(' ', $response, 2);

        $command = $timestampAndCommand[1];

        if ($command == '"MONITOR"') {
            return null;
        }

        return $timestampAndCommand;
    }
}