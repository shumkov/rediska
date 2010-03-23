<?php

/**
 * Return and remove the first element of the List at key
 * 
 * @param string $name Key name
 * @return mixin
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_ShiftFromList extends Rediska_Command_Abstract
{
    protected function _create($name) 
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "LPOP {$this->_rediska->getOption('namespace')}$name";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $this->_rediska->unserialize($response[0]);
    }
}