<?php

/**
 * Get member score from Sorted Set
 * 
 * @param string $name
 * @param mixin $value
 * @return number
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetScoreFromSortedSet extends Rediska_Command_Abstract
{
    protected function _create($name, $value)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->serialize($value);

        $command = array('ZSCORE', "{$this->_rediska->getOption('namespace')}$name", $value);

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $response[0];
    }
}