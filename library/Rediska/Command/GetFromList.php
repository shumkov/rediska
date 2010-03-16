<?php

/**
 * Return element of List by index at key
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name  Key name
 * @param integer $index Index
 * @return mixin
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetFromList extends Rediska_Command_Abstract
{
    protected function _create($name, $index)
    {
        if (!is_integer($index)) {
            throw new Rediska_Command_Exception("Index must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "LINDEX {$this->_rediska->getOption('namespace')}$name $index";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $this->_rediska->unserialize($response[0]);
    }
}