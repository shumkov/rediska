<?php

/**
 * Append value to the end of List
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_AppendToList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string            $key                Key name
     * @param mixed             $value              Element value
     * @param boolean[optional] $createIfNotExists  Create list if not exists
     * @return Rediska_Connection_Exec
     */
    public function create($key, $value, $createIfNotExists = true)
    {
        if (!$createIfNotExists) {
            $this->_throwExceptionIfNotSupported('2.1.1');
        }


        $connection = $this->_rediska->getConnectionByKeyName($key);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = array($createIfNotExists ? 'RPUSH' : 'RPUSHX',
                         $this->_rediska->getOption('namespace') . $key,
                         $value);

        return new Rediska_Connection_Exec($connection, $command);
    }
}