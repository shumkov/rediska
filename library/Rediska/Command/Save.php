<?php

/**
 * Synchronously save the DB on disk
 * 
 * @param boolean $background Save asynchronously
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Save extends Rediska_Command_Abstract
{
    protected function _create($background = false) 
    {
        if ($background) {
            $command = "BGSAVE";
        } else {
            $command = "SAVE";
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