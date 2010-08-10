<?php

/**
 * Set a new value as the element at index position of the List at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SetToList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string  $key   Key name
     * @param mixin   $value Value
     * @param integer $index Index
     * @return Rediska_Connection_Exec
     */
    public function create($key, $index, $member)
    {
        if (!is_integer($index)) {
            throw new Rediska_Command_Exception("Index must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($key);

        $member = $this->_rediska->getSerializer()->serialize($member);

        $command = "LSET {$this->_rediska->getOption('namespace')}$key $index " . strlen($member) . Rediska::EOL . $member;

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param integer $response
     * @return boolean
     */
    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}