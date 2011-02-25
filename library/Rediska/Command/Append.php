<?php

/**
 * Append value to a end of string key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Append extends Rediska_Command_Abstract
{
    protected $_version = '1.3.3';

    /**
     * Create command
     *
     * @param string $key    Key name
     * @param mixed  $value  Value
     * @return Rediska_Connection_Exec
     */
    public function create($key, $value)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);
        
        $member = $this->_rediska->getSerializer()->serialize($value);

        $command = array('APPEND',
                         $this->_rediska->getOption('namespace') . $key,
                         $member);

        return new Rediska_Connection_Exec($connection, $command);
    }
}