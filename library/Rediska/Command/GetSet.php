<?php

/**
 * Return all the members of the Set value at key
 * 
 * @param string $name Key name
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSet extends Rediska_Command_Abstract
{
    protected function _create($name)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "SMEMBERS {$this->_rediska->getOption('namespace')}$name";
        
        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        $values = $responses[0];

        foreach($values as &$value) {
            $value = $this->_rediska->getSerializer()->unserialize($value);
        }

        return $values;
    }
}