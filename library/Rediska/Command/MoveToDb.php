<?php

/**
 * Move the key from the currently selected DB to the DB having as index dbindex
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name  Key name
 * @param integer $index Db index
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_MoveToDb extends Rediska_Command_Abstract
{
    public function create($name, $dbIndex) 
    {
        if (!is_integer($dbIndex) || $dbIndex < 0) {
            throw new Rediska_Command_Exception("Index must be zero or positive integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "MOVE {$this->_rediska->getOption('namespace')}$name $dbIndex";
        
        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}