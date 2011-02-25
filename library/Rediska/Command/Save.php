<?php

/**
 * Save the DB on disk
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Save extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param boolean[optional] $background Save asynchronously. For default is false
     * @return Rediska_Connection_Exec
     */
    public function create($background = false) 
    {
        $command = array($background ? 'BGSAVE' : 'SAVE');

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
     * @return boolean
     */
    public function parseResponses($responses)
    {
        return true;
    }
}