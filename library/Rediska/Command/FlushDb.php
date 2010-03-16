<?php

/**
 * Remove all the keys of the currently selected DB
 * 
 * @param boolean $all Remove from all Db
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_FlushDb extends Rediska_Command_Abstract
{
    protected function _create($all = false) 
    {
        if ($all) {
            $command = "FLUSHALL";
        } else {
            $command = "FLUSHDB";
        }

        foreach($this->_rediska->getConnections() as $connection) {
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponse($response)
    {
        return true;
    }
}