<?php

/**
 * Return element of List by index at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetFromList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string  $key   Key name
     * @param integer $index Index
     * @return Rediska_Connection_Exec
     */
    public function create($key, $index)
    {
        if (!is_integer($index)) {
            throw new Rediska_Command_Exception("Index must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = "LINDEX {$this->_rediska->getOption('namespace')}$key $index";
        
        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param string $response
     * @return mixin
     */
    public function parseResponse($response)
    {
        return $this->_rediska->getSerializer()->unserialize($response);
    }
}