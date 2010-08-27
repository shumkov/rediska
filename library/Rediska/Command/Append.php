<?php

/**
 * Append value to a end of string key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Append extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param $key    Key name
     * @param $value  Value
     * @return Rediska_Connection_Exec
     */
    public function create($key, $value)
    {
        $command = array('APPEND', $this->_rediska->getOption('namespace') . $key, $this->_rediska->getSerializer()->serialize($value));

        $connection = $this->_rediska->getConnectionByKeyName($key);

        return new Rediska_Connection_Exec($connection, $command);
    }
}