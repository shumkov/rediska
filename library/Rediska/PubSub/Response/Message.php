<?php

/**
 * Rediska PubSub message response 
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage PublishSubscribe
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Response_Message extends Rediska_PubSub_Response_Abstract
{
    /**
     * Message
     * 
     * @var mixed
     */
    protected $_message;

    /**
     * Constructor
     * 
     * @param Rediska_Connection $connection
     * @param string             $channel
     * @param mixed              $message
     */
    public function __construct(Rediska_Connection $connection, $channel, $message)
    {
        parent::__construct($connection, $channel);

        $this->_message = $message;
    }

    /**
     * Get message
     * 
     * @return mixed
     */
    public function getMessage()
    {
        return $this->_message;
    }
}