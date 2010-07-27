<?php

/**
 * Rediska PubSub message response 
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Response_Message extends Rediska_PubSub_Response_Abstract
{
    /**
     * Message
     * 
     * @var mixin
     */
    protected $_message;

    /**
     * Constructor
     * 
     * @param Rediska_Connection $connection
     * @param string             $channel
     * @param mixin              $message
     */
    public function __construct(Rediska_Connection $connection, $channel, $message)
    {
        parent::__construct($connection, $channel);

        $this->_message = $message;
    }

    /**
     * Get message
     * 
     * @return mixin
     */
    public function getMessage()
    {
        return $this->_message;
    }
}