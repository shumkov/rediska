<?php

/**
 * Return all the members of the Set value at key
 * 
 * @param string $name Key name
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
    public function create($name)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "SMEMBERS {$this->_rediska->getOption('namespace')}$name";

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return array_map(array($this->_rediska->getSerializer(), 'unserialize'), $response);
    }
}