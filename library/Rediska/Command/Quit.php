<?php

/**
 * Ask the server to silently close the connection.
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Quit extends Rediska_Command_Abstract
{
    protected $_affectedConnections = array();

    /**
     * Create command
     *
     * @return Rediska_Connection_Exec
     */
    public function create() 
    {
        $command = array('QUIT');

        $this->_affectedConnections = $this->_rediska->getConnections();

        $commands = array();
        foreach($this->_affectedConnections as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    public function write()
    {
        parent::write();

        foreach($this->_affectedConnections as $connection) {
            $connection->disconnect();
        }

        return true;
    }

    public function read()
    {
        return true;
    }
}