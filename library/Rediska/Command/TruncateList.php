<?php

/**
 * Trim the list at key to the specified range of elements
 * 
 * @throws Rediska_Command_Exception
 * @param string $name Key name
 * @param integer $start Start index
 * @param integer $end End index
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_TruncateList extends Rediska_Command_Abstract
{
    protected function _create($name, $limit, $offset = 0)
    {
        if (!is_integer($limit)) {
            throw new Rediska_Command_Exception("Limit must be integer");
        }

        if (!is_integer($offset)) {
            throw new Rediska_Command_Exception("Offset must be integer");
        }

        $start = $offset;
        $end   = $offset + $limit - 1;

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "LTRIM {$this->_rediska->getOption('namespace')}$name $start $end";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return (boolean)$response[0];
    }
}