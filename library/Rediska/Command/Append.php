<?php

/**
 * Append value to a end of string key
 * 
 * @param $name   Key name
 * @param $value  Value
 * @return string
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Append extends Rediska_Command_Abstract
{
    public function create($name, $value)
    {
        $command = array('APPEND', $this->_rediska->getOption('namespace') . $name, $this->_rediska->getSerializer()->serialize($value));

        $connection = $this->_rediska->getConnectionByKeyName($name);

        return new Rediska_Connection_Exec($connection, $command);
    }
}