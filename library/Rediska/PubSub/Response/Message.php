<?php

class Rediska_PubSub_Response_Message extends Rediska_PubSub_Response_Abstract
{
    protected $_message;

    public function __construct($channel, $message)
    {
        parent::__construct($channel);

        $this->_message = $message;
    }

    public function getMessage()
    {
        return $this->_message;
    }
}