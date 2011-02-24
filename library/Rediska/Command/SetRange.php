<?php

/**
 * Overwrite part of a string at key starting at the specified offset
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SetRange extends Rediska_Command_Abstract
{
    protected $_version = '2.1.8';

    /**
     * Create command
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @param integer $value  Value
     * @return Rediska_Connection_Exec
     */
    public function create($key, $offset, $value)
    {
        $value = $this->getRediska()->getSerializer()->serialize($value);

        $command = array('SETRANGE',
                         $this->_rediska->getOption('namespace') . $key,
                         $offset,
                         $value);

        $connection = $this->_rediska->getConnectionByKeyName($key);

        return new Rediska_Connection_Exec($connection, $command);
    }
}