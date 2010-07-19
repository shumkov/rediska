<?php

/**
 * Return the length of the List value at key
 * 
 * @param string $name
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetListLength extends Rediska_Command_Abstract
{
    public function create($name) 
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "LLEN {$this->_rediska->getOption('namespace')}$name";

        return new Rediska_Connection_Exec($connection, $command);
    }
}