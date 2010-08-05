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

        $this->_throwIfNotSupported();
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

        $multi = new Rediska_Connection_Exec($this->_connection, 'MULTI');
        $multi->execute();
        
        $this->_isStarted = true;

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
     * Execute transaction
     * 
     * @return array
     */
    public function execute()
    {
        $results = array();

        if ($this->isStarted()) {
            $exec = new Rediska_Connection_Exec($this->_connection, 'EXEC');
            $responses = $exec->execute();

            if (!empty($this->_commands)) {
                if (!$responses) {
                    throw new Rediska_Transaction_Exception('Transaction has been aborted by server');
                }

                foreach($this->_commands as $i => $command) {
                    $results[] = $command->parseResponses(array($responses[$i]));
                }
            }

            $this->_commands = array();
            $this->_isStarted = false;
        }

        return $results;
    }

    /**
     * Magic method for execute
     *
     * @return array
     */
    public function __invoke()
    {
        return $this->execute();
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

        $discard = new Rediska_Connection_Exec($this->_connection, 'DISCARD');
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
        
        $command->execute();
        
        if (!$command->isQueued()) {
            throw new Rediska_Transaction_Exception("Command not added to transaction!");
        }
        
        $this->_commands[] = $command;

        $this->_specifiedConnection->resetConnection();

        return $this;
    }

    /**
     * Throw if transaction not supported by Redis
     */
    protected function _throwIfNotSupported()
    {
        $version = '1.3.8';
        $redisVersion = $this->getRediska()->getOption('redisVersion');
        if (version_compare($version, $this->getRediska()->getOption('redisVersion')) == 1) {
            throw new Rediska_Transaction_Exception("Transaction requires {$version}+ version of Redis server. Current version is {$redisVersion}. To change it specify 'redisVersion' option.");
        }
    }
}