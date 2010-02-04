<?php

/**
 * Return the length of the List value at key
 * 
 * @param string $name
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.3.0
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetListLength extends Rediska_Command_Abstract
{
    protected function _create($name) 
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "LLEN {$this->_rediska->getOption('namespace')}$name";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $response[0];
    }
}