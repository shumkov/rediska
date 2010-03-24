<?php

/**
 * Remove all the elements in the sorted set at key with a score between min and max (including elements with score equal to min or max).
 * 
 * @param string  $name  Key name
 * @param numeric $min   Min value
 * @param numeric $max   Max value
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DeleteFromSortedSetByScore extends Rediska_Command_Abstract
{
    protected function _create($name, $min, $max)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = array('ZREMRANGEBYSCORE', "{$this->_rediska->getOption('namespace')}$name", $min, $max);

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $response[0];
    }
}