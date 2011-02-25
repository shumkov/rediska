<?php

/**
 * Delete element from list by member at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DeleteFromList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param $key             Key name
     * @param $value           Element value
     * @param $count[optional] Limit of deleted items. For default no limit.
     * @return Rediska_Connection_Exec
     */
    public function create($key, $value, $count = 0)
    {        
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = array('LREM',
                         $this->_rediska->getOption('namespace') . $key,
                         $count,
                         $value);

        return new Rediska_Connection_Exec($connection, $command);
    }
}