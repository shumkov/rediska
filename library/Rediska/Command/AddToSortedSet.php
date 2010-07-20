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
    protected $_version = '1.1';

    public function create($name, $value, $score)
    {
    	$connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = array('ZADD', "{$this->_rediska->getOption('namespace')}$name", $score, $value);

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}