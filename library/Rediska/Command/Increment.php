<?php

/**
 * Increment the number value of key by integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Increment extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string            $key    Key name
     * @param integer[optional] $amount Amount to increment. One for default
     * @return Rediska_Connection_Exec
     */
    public function create($key, $amount = 1)
    {
        if (!is_integer($amount) || $amount <= 0) {
            throw new Rediska_Command_Exception("Amount must be positive integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($key);

        if ($amount == 1) {
            $command = "INCR {$this->_rediska->getOption('namespace')}$key";
        } else {
            $command = "INCRBY {$this->_rediska->getOption('namespace')}$key $amount";
        }

        return new Rediska_Connection_Exec($connection, $command);
    }
}