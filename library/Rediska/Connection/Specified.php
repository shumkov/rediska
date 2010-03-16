<?php

/**
 * Rediska specified connection
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
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

    public function __call($name, $args)
    {
        if (strtolower($name) == 'pipeline') {
            return $this->_rediska->pipeline();
        } else {
            $command = $this->_rediska->getCommand($name, $args);
            $command->write();
            return $command->read();
        }
    }

    public function setConnection(Rediska_Connection $connection)
    {
        $this->_connection = $connection;
        
        return $this;
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function resetConnection()
    {
        $this->_connection = null;
        
        return $this;
    }
}