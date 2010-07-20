<?php

/**
 * Rewrite the Append Only File in background when it gets too big
 * 
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_RewriteAppendOnlyFile extends Rediska_Command_Abstract
{
    public function create() 
    {
        $command = "BGREWRITEAOF";

        $commands = array();
        foreach($this->_rediska->getConnections() as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    public function parseResponse($response)
    {
        return true;
    }
}