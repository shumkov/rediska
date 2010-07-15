<?php

/**
 * Rediska transaction
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Transaction
{
    /**
     * Rediska instance
     * 
     * @var Rediska
     */
    protected $_rediska;

    /**
     * Rediska specified connection instance
     * 
     * @var Rediska_Connection
     */
    protected $_connection;

    /**
     * Rediska specified connection instance
     * 
     * @var Rediska_Connection_Specified
     */
    protected $_specifiedConnection;

    /**
     * Is transaction started
     * 
     * @var boolean
     */
    protected $_isStarted = false;

    /**
     * Commands buffer
     * 
     * @var array
     */
    protected $_commands = array();

    /**
     * Constructor
     * 
     * @param Rediska            $rediska    Rediska instance
     * @param Rediska_Connection $connection Trancation connection
     */
    public function __construct(Rediska $rediska, Rediska_Connection_Specified $specifiedConnection, Rediska_Connection $connection)
    {
        $this->_rediska             = $rediska;
        $this->_specifiedConnection = $specifiedConnection;
        $this->_connection          = clone $connection;
    }

    /**
     * Start transaction
     * 
     * @return boolean
     */
    public function start()
    {
        if ($this->isStarted()) {
            return false;
        }

        $multi = new Rediska_Command($this->_connection, 'MULTI');
        $multi->execute();

        return true;
    }

    /**
     * Is transaction started
     * 
     * @return boolean
     */
    public function isStarted()
    {
        return $this->_isStarted;
    }

    /**
     * Execute pipelined commands
     * 
     * @return array
     */
    public function execute()
    {
        $results = array();

        if ($this->isStarted()) {
            $exec = new Rediska_Command($this->_connection, 'EXEC');
            $exec->execute();

            if (!empty($this->_commands)) {
                foreach($this->_commands as $command) {
                    $command->read();
                }
            }

            $this->_commands = array();
            $this->_isStarted = false;
        }

        return $results;
    }

    /**
     * Discard transaction
     * 
     * @return boolean
     */
    public function discard()
    {
        if (!$this->isStarted()) {
            return false;
        }

        $discard = new Rediska_Command($this->_connection, 'DISCARD');
        $reply = $discard->execute();

        $this->_commands = array();
        $this->_isStarted = false;

        return $reply;
    }

    /**
     * Add command to transaction
     * 
     * @param $name
     * @param $args
     * @return Rediska_Transaction
     */
    public function __call($name, $args)
    {
        if (in_array(strtolower($name), array('on', 'pipeline'))) {
            throw new Rediska_Transaction_Exception("You can't use '$name' in transaction");
        }

        $this->start();

        $this->_specifiedConnection->setConnection($this->_connection);

        $command = $this->_rediska->getCommand($name, $args);

        if (!$command->isAtomic()) {
        	throw new Rediska_Exception("Command '$name' doesn't work properly (not atomic) in pipeline on multiple servers");
        }

        $commandError = false;
        try {
            $command->execute();
        } catch (Rediska_Command_Exception $e) {
            $commandError = true;
        }

        if (!$commandError) {
            $this->_commands[] = $command;
        }

        $this->_specifiedConnection->resetConnection();

        return $this;
    }
}