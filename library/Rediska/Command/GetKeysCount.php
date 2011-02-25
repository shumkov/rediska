<?php

/**
 * Get the number of keys
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetKeysCount extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @return Rediska_Connection_Exec
     */
    public function create()
    {
        $commands = array();
        $command = array('DBSIZE');
        foreach($this->_rediska->getConnections() as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    /**
     * Parse response
     *
     * @param array $responses
     * @return integer
     */
    public function parseResponses($responses)
    {
        return array_sum($responses);
    }
}