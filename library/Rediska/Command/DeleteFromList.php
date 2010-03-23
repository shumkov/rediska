<?php

/**
 * Delete element from list by value at key
 * 
 * @throws Rediska_Command_Exception
 * @param $name Key name
 * @param $value Element value
 * @param $count Limit of deleted items
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DeleteFromList extends Rediska_Command_Abstract
{
    protected function _create($name, $value, $count = 0)
    {        
        if (!is_integer($count)) {
            throw new Rediska_Command_Exception("Count must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->serialize($value);

        $command = "LREM {$this->_rediska->getOption('namespace')}$name $count " . strlen($value) . Rediska::EOL . $value;

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $response[0];
    }
}