<?php

/**
 * Set + Expire atomic command
 * 
 * @param string|array $name       Key name
 * @param mixed        $value      Value
 * @param boolean      $time     Expire
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SetAndExpire extends Rediska_Command_Abstract
{
    public function create($name, $value, $time)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);
        $value = $this->_rediska->getSerializer()->serialize($value);
        $name = $this->_rediska->getOption('namespace') . $name;

        return new Rediska_Connection_Exec($connection, array('SETEX', $name, $time, $value));
    }
}