<?php

abstract class Rediska_PubSub_Response_Abstract
{
    protected $_channel;

    public function __construct($channel)
    {
        $this->_channel = $channel;
    }

    public function getChannel()
    {
        return $this->_channel;
    }
}