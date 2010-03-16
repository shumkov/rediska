<?php

/**
 * Increment score of sorted set element
 * 
 * @param string $name  Key name
 * @param mixin  $value Member
 * @param number $score Score to increment
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_IncrementScoreInSortedSet extends Rediska_Command_Abstract
{
    protected function _create($name, $value, $score)
    {
    	$connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->serialize($value);

        $command = array('ZINCRBY', "{$this->_rediska->getOption('namespace')}$name", $score, $value);

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $response[0];
    }
}