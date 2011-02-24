<?php

/**
 * Stop all the clients, save the DB, then quit the server
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Shutdown extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @return array
     */
    public function create() 
    {
        $command = array('SHUTDOWN');
        $commands = array();
        foreach($this->_rediska->getConnections() as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    /**
     * Parse responses
     *
     * @param array $responses
     * @return true
     */
    public function parseResponses($responses)
    {
        return true;
    }
}