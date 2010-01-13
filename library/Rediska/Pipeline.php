<?php

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
     * Commands buffer
     * 
     * @var array
     */
    protected $_commands = array();

    public function __construct(Rediska $rediska, Rediska_Connection_Specified $specifiedConnection)
    {
        $this->_rediska = $rediska;
        $this->_specifiedConnection = $specifiedConnection;
        $this->_defaultConnection = $specifiedConnection->getConnection();
    }

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
            $connection = $this->_specifiedConnection->getConnection();
        } else {
            $connection = $this->_defaultConnection;
        }

        $this->_specifiedConnection->setConnection($connection);
 
        $this->_commands[] = $this->_rediska->getCommand($name, $args);

        $this->_specifiedConnection->resetConnection();

        return $this;
    }
}