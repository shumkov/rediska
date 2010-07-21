<?php

/**
 * Atomic set value and return old 
 * 
 * @param string  $name   Key name
 * @param mixin   $value  Value
 * @return mixin
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SetAndGet extends Rediska_Command_Abstract
{
    public function create($name, $value)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = "GETSET {$this->_rediska->getOption('namespace')}$name " . strlen($value) . Rediska::EOL . $value;

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return $this->_rediska->getSerializer()->unserialize($response);
    }
}