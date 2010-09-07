<?php

/**
 * Abstract PubSub response
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage PublishSubscribe
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_PubSub_Response_Abstract
{
    /**
     * Rediska connection
     * 
     * @var Rediska_Connection
     */
    protected $_connection;
    
    /**
     * Channel
     * 
     * @var string
     */
    protected $_channel;

    /**
     * Constructor
     * 
     * @param Rediska_Connection $connection
     * @param string             $channel
     */
    public function __construct(Rediska_Connection $connection, $channel)
    {
        $this->_connection = $connection;
        $this->_channel = $channel;
    }

    /**
     * Get connection
     * 
     * @return Rediska_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Get channel
     * 
     * @return string
     */
    public function getChannel()
    {
        return $this->_channel;
    }
}