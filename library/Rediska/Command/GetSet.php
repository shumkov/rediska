<?php

/**
 * Return all the members of the Set value at key
 * 
 * @param string $name Key name
 * @param string $sort Deprecated
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
    protected function _create($name, $sort = null)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        if (is_null($sort)) {
            $command = "SMEMBERS {$this->_rediska->getOption('namespace')}$name";
        } else {
            throw new Rediska_Command_Exception("This attribute is depricated. You must use 'sort' command for it.");
        }
        
        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        $values = $responses[0];

        foreach($values as &$value) {
            $value = $this->_rediska->unserialize($value);
        }

        return $values;
    }
}