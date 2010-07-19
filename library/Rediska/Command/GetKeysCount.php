<?php

/**
 * Get the number of keys
 * 
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetKeysCount extends Rediska_Command_Abstract
{
    public function create()
    {
        $commands = array();
        $command = 'DBSIZE';
        foreach($this->_rediska->getConnections() as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    public function parseResponses($responses)
    {
        $count = 0;
        foreach($responses as $response) {
            $count += $response;
        }
        return $count;
    }
}