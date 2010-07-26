<?php

/**
 * Synchronously save the DB on disk, then shutdown the server
 * 
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Shutdown extends Rediska_Command_Abstract
{
    public function create($background = false) 
    {
        $command = "SHUTDOWN";
        $commands = array();
        foreach($this->_rediska->getConnections() as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    public function parseResponses($responses)
    {
        return true;
    }
}