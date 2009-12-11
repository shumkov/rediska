<?php

class Rediska_Connection_Specified
{
    /**
     * Rediska instance
     * 
     * @var Rediska
     */
    protected $_rediska;

    /**
     * Specified connection
     * 
     * @var Rediska_Connection
     */
    protected $_connection;

    public function __construct(Rediska $rediska)
    {
        $this->_rediska = $rediska;
    }

    public function __call($method, $args)
    {
        $callback = array($this->_rediska, $method);

        $result = call_user_func_array($callback, $args);

        $this->_connection = null;

        return $result;
    }

    public function setConnection(Rediska_Connection $connection)
    {
        $this->_connection = $connection;
    }

    public function getConnection()
    {
        return $this->_connection;
    }
}