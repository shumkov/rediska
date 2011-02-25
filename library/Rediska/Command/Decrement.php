<?php

/**
 * Decrement the number value of key by integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Decrement extends Rediska_Command_Abstract
{
    /**
     * Create command
     * 
     * @param string            $key    Key name
     * @param integer[optional] $amount Amount to decrement. One for default
     * @return Rediska_Connection_Exec
     */
    public function create($key, $amount = 1)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        if ($amount == 1) {
            $command = array('DECR',
                             $this->_rediska->getOption('namespace') . $key);
        } else {
            $command = array('DECRBY',
                             $this->_rediska->getOption('namespace') . $key,
                             $amount);
        }

        return new Rediska_Connection_Exec($connection, $command);
    }
}