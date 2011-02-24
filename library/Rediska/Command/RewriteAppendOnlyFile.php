<?php

/**
 * Rewrite the Append Only File in background when it gets too big
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_RewriteAppendOnlyFile extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @return array
     */
    public function create() 
    {
        $command = array('BGREWRITEAOF');

        $commands = array();
        foreach($this->_rediska->getConnections() as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    /**
     * Parse responses
     *
     * @param $responses
     * @return boolean
     */
    public function parseResponses($responses)
    {
        return true;
    }
}