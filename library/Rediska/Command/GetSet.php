<?php

/**
 * Return all the members of the Set value at key
 * 
 * @param string $name Key name
 * @param string $sort Sorting query see: http://code.google.com/p/redis/wiki/SortCommand
 *                     ALPHA work incorrect becouse values in Set serailized
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSet extends Rediska_Command_Abstract
{
    protected function _create($name, $sort = null)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        if (is_null($sort)) {
            $command = "SMEMBERS {$this->_rediska->getOption('namespace')}$name";
        } else {
            $command = "SORT {$this->_rediska->getOption('namespace')}$name $sort";
        }
        
        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        $values = $response[0];

        foreach($values as &$value) {
            $value = $this->_rediska->unserialize($value);
        }

        return $values;
    }
}