<?php

/**
 * Set a new value as the element at index position of the List at key
 * 
 * @throws Rediska_Command_Exception
 * @param string $name Key name
 * @param mixin $value Value
 * @param integer $index Index
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SetToList extends Rediska_Command_Abstract
{
    protected function _create($name, $index, $value) 
    {
        if (!is_integer($index)) {
            throw new Rediska_Command_Exception("Index must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->serialize($value);

        $command = "LSET {$this->_rediska->getOption('namespace')}$name $index " . strlen($value) . Rediska::EOL . $value;

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return (boolean)$response[0];
    }
}