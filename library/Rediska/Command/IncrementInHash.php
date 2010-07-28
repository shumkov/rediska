<?php

/**
 * Increment field value in hash
 * 
 * @param string $name             Key name
 * @param mixin  $field            Field
 * @param number $amount[optional] Increment amount. Default: 1
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_IncrementInHash extends Rediska_Command_Abstract
{
    protected $_version = '1.3.10';

    public function create($name, $field, $amount = 1)
    {
    	$connection = $this->_rediska->getConnectionByKeyName($name);

        $command = array('HINCRBY', "{$this->_rediska->getOption('namespace')}$name", $field, $amount);

        return new Rediska_Connection_Exec($connection, $command);
    }
}