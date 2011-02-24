<?php

/**
 * Returns the bit value at offset in the string value stored at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SetBit extends Rediska_Command_Abstract
{ 
    protected $_version = '2.1.8';

    /**
     * Create command
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @param integer $bit    Bit (0 or 1)
     * @return Rediska_Connection_Exec
     */
    public function create($key, $offset, $bit)
    {
        $connection = $this->getRediska()->getConnectionByKeyName($key);

        $command = array('SETBIT',
                         $this->getRediska()->getOption('namespace'). $key,
                         $offset,
                         $bit);

        return new Rediska_Connection_Exec($connection, $command);
    }
}