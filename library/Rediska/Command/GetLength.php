<?php

/**
 * Returns the length of the string value stored at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetLength extends Rediska_Command_Abstract
{ 
    protected $_version = '2.1.2';

    /**
     * Create command
     *
     * @param string  $key Key name
     * @return Rediska_Connection_Exec
     */
    public function create($key)
    {
        $connection = $this->getRediska()->getConnectionByKeyName($key);

        $command = array('STRLEN',
                         $this->getRediska()->getOption('namespace'). $key);

        return new Rediska_Connection_Exec($connection, $command);
    }
}