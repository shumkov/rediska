<?php

/**
 * Increment the number value of key by integer
 * 
 * @throws Rediska_Command_Exception
 * @param string $name Key name
 * @param integer $amount Amount to increment
 * @return integer New value
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Increment extends Rediska_Command_Abstract
{
    protected function _create($name, $amount = 1) 
    {
        if (!is_integer($amount) || $amount <= 0) {
            throw new Rediska_Command_Exception("Amount must be positive integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        if ($amount == 1) {
            $command = "INCR {$this->_rediska->getOption('namespace')}$name";
        } else {
            $command = "INCRBY {$this->_rediska->getOption('namespace')}$name $amount";
        }

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $response[0];
    }
}