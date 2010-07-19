<?php

/**
 * Test if the specified value is a member of the Set at key
 * 
 * @param string $name  Key value
 * @prarm mixin  $value Value
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_ExistsInSet extends Rediska_Command_Abstract
{
    public function create($name, $value)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);
        
        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = "SISMEMBER {$this->_rediska->getOption('namespace')}$name " . strlen($value) . Rediska::EOL . $value;

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}