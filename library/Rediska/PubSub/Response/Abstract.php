<?php

abstract class Rediska_PubSub_Response_Abstract
{
    /**
     * 
     * @var Rediska_Connection
     */
    protected $_connection;
    protected $_channel;

    public function __construct(Rediska_Connection $connection, $channel)
    {
        $this->_channel = $channel;
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function getChannel()
    {
        return $this->_channel;
    }
}