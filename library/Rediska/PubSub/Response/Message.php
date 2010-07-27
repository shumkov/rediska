<?php

class Rediska_PubSub_Response_Message extends Rediska_PubSub_Response_Abstract
{
    protected $_message;

    public function __construct(Rediska_Connection $connection, $channel, $message)
    {
        parent::__construct($connection, $channel);

        $this->_message = $message;
    }

    public function getMessage()
    {
        return $this->_message;
    }
}