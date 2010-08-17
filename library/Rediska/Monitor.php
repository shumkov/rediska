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
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Monitor extends Rediska_Options implements Iterator
{
    /**
     * Rediska instance
     * 
     * @var string|Rediska
     */
    protected $_rediska = Rediska::DEFAULT_NAME;

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
    public function getCommand()
    {
        // Get message from connections
        while (true) {
            /* @var $connection Rediska_Connection */
            foreach ($this->_connections as $connection) {
                if ($this->_timeout) {
                    $timeLeft = ($this->_timeStart + $this->_timeout) - time();

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
                    if (!$this->_timeout) {
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

    /**
     * Set Rediska instance
     *
     * @param Rediska $rediska Rediska instance or name
     * @return Rediska_Key_Abstract
     */
    public function setRediska($rediska)
    {
        if (is_object($rediska) && !$rediska instanceof Rediska) {
            throw new Rediska_PubSub_Exception('$rediska must be Rediska instance or name');
        }

        $this->_rediska = $rediska;

        return $this;
    }

    /**
     * Get Rediska instance
     *
     * @throws Rediska_Exception
     * @return Rediska
     */
    public function getRediska()
    {
        if (!is_object($this->_rediska)) {
            $this->_rediska = Rediska_Manager::getOrInstanceDefault($this->_rediska);
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