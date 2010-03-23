<?php

/**
 * Add member to sorted set
 * 
 * @param string $name  Key name
 * @param mixin  $value Member
 * @param number $score Score of member
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_AddToSortedSet extends Rediska_Command_Abstract
{
    protected function _create($name, $value, $score)
    {
    	$connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->serialize($value);

        $command = array('ZADD', "{$this->_rediska->getOption('namespace')}$name", $score, $value);

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return (boolean)$response[0];
    }
}