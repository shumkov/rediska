<?php

/**
 * Save the DB on disk, then shutdown the server
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Shutdown extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param boolean $background[optional] Save asynchronously. For default is false
     * @return array
     */
    public function create($background = false) 
    {
        $command = "SHUTDOWN";
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