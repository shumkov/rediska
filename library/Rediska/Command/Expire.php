<?php

/**
 * Set a time to live in seconds on a key
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name    Key name
 * @param integer $seconds Seconds from now to expire
 * @return boolean 
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Expire extends Rediska_Command_Abstract
{
    protected function _create($name, $seconds)
    {
        if (!is_integer($seconds) || $seconds <= 0) {
            throw new Rediska_Command_Exception("Seconds must be positive integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "EXPIRE {$this->_rediska->getOption('namespace')}$name $seconds";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return (boolean)$response[0];
    }
}