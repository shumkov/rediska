<?php

/**
 * Get key type
 * 
 * @param string $name Key name
 * @return string
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetType extends Rediska_Command_Abstract
{
    protected function _create($name)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "TYPE {$this->_rediska->getOption('namespace')}$name";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $response[0];
    }
}