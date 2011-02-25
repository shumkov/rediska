<?php

/**
 * Remove all the keys of the currently selected DB
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_FlushDb extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param boolean[optional] $all Remove from all Db. For default is false.
     * @return Rediska_Connection_Exec
     */
    public function create($all = false) 
    {
        if ($all) {
            $command = array('FLUSHALL');
        } else {
            $command = array('FLUSHDB');
        }

        $commands = array();
        foreach($this->_rediska->getConnections() as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }
        
        return $commands;
    }

    /**
     * Parse response
     *
     * @param string $response
     * @return boolean
     */
    public function parseResponse($response)
    {
        return true;
    }
}