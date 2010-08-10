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

    /**
     * Execute command
     * 
     * @param $name
     * @param $args
     * @return mixin
     */
    public function __call($name, $args)
    {
        if (in_array(strtolower($name), array('pipeline', 'transaction', 'subscribe', 'monitor', 'config'))) {
            return call_user_func_array(array($this->_rediska, $name), $args);
        } else {
            $command = $this->_rediska->getCommand($name, $args);

            return $command->execute();
        }
    }

    /**
     * Set connection
     * 
     * @param $connection
     * @return Rediska_Connection_Specified
     */
    public function setConnection(Rediska_Connection $connection)
    {
        $this->_connection = $connection;
        
        return $this;
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
     * Reset connection
     * 
     * @return Rediska_Connection
     */
    public function resetConnection()
    {
        $this->_connection = null;
        
        return $this;
    }
}