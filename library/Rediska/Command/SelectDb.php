<?php

/**
 * Select the DB having the specified index
 * 
 * @throws Rediska_Command_Exception
 * @param integer $index Db index
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SelectDb extends Rediska_Command_Abstract
{
    protected function _create($index) 
    {
    	if (!is_integer($index) || $index < 0) {
            throw new Rediska_Command_Exception("Index must be zero or positive integer");
        }

        $command = "SELECT $index";

        foreach($this->_rediska->getConnections() as $connection) {
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponse($response)
    {
        return true;
    }
}