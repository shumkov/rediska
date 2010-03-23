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
    protected function _create($name, $value)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);
        
        $value = $this->_rediska->serialize($value);

        $command = "SISMEMBER {$this->_rediska->getOption('namespace')}$name " . strlen($value) . Rediska::EOL . $value;

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return (boolean)$response[0];
    }
}