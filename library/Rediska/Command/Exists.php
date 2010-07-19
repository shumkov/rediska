<?php

/**
 * Test if a key exists
 * 
 * @param string $name Key name
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Exists extends Rediska_Command_Abstract
{
    public function create($name) 
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);
        
        $command = "EXISTS {$this->_rediska->getOption('namespace')}$name";
        
        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}