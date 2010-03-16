<?php

/**
 * Rediska pipeline
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Pipeline
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
     * @var Rediska_Connection_Specified
     */
    protected $_specifiedConnection;

    /**
     * Default pipeline connection
     * 
     * @var Rediska_Connection
     */
    protected $_defaultConnection;
    
    /**
     * One time connection
     * 
     * @var Rediska_Connection
     */
    protected $_oneTimeConnection;

    /**
     * Commands buffer
     * 
     * @var array
     */
    protected $_commands = array();

    /**
     * Constructor
     * 
     * @param Rediska                      $rediska             Rediska instance
     * @param Rediska_Connection_Specified $specifiedConnection Specified connection
     */
    public function __construct(Rediska $rediska, Rediska_Connection_Specified $specifiedConnection)
    {
        $this->_rediska = $rediska;
        $this->_specifiedConnection = $specifiedConnection;
        $this->_defaultConnection = $specifiedConnection->getConnection();
    }

    /**
     * Execute pipelined commands
     * 
     * @return array
     */
    public function execute()
    {
        if (empty($this->_commands)) {
            throw new Rediska_Exception("Nothing to execute!");
        }

        foreach($this->_commands as $command) {
            $command->write();
        }

        $results = array();
        foreach($this->_commands as $command) {
            $results[] = $command->read();
        }

        return $results;
    }

    public function __call($name, $args)
    {
        if (strtolower($name) == 'on' && isset($args[0])) {
            $this->_rediska->on($args[0]);
            $this->_oneTimeConnection = $this->_specifiedConnection->getConnection();

            return $this;
        }

        if ($this->_oneTimeConnection) {
        	$connection = $this->_oneTimeConnection;
        	$this->_oneTimeConnection = null;
        } else {
            $connection = $this->_defaultConnection;
        }

        if ($connection !== null) {
            $this->_specifiedConnection->setConnection($connection);
        } else {
        	$this->_specifiedConnection->resetConnection();
        }
 
        $command = $this->_rediska->getCommand($name, $args);

        if (!$command->isAtomic()) {
        	throw new Rediska_Exception("Command '$name' doesn't work properly (not atomic) in pipeline on multiple servers");
        }

        $this->_commands[] = $command;

        $this->_specifiedConnection->resetConnection();

        return $this;
    }
}