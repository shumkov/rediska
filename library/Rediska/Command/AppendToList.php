<?php

/**
 * Append value to the end of List
 * 
 * @param string $name  Key name
 * @param mixin  $value Value
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_AppendToList extends Rediska_Command_Abstract
{
    public function create($name, $value)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = "RPUSH {$this->_rediska->getOption('namespace')}$name " . strlen($value) . Rediska::EOL . $value;

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return $response;
    }
}