<?php

/**
 * Get random element from the Set value at key
 * 
 * @param string  $name Key name
 * @param boolean $pop If true - pop value from the set
 * @return mixin
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetRandomFromSet extends Rediska_Command_Abstract
{
    protected function _create($name, $pop = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        if ($pop) {
            $command = "SPOP";
        } else {
            $command = "SRANDMEMBER";
        }

        $command .= " {$this->_rediska->getOption('namespace')}$name";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        return $this->_rediska->unserialize($response[0]);
    }
}