<?php

/**
 * Remove all the elements in the sorted set at key with a score between min and max (including elements with score equal to min or max).
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DeleteFromSortedSetByScore extends Rediska_Command_Abstract
{
    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.1';

    /**
     * Create command
     *
     * @param string  $key   Key name
     * @param numeric $min   Min value
     * @param numeric $max   Max value
     * @return Rediska_Connection_Exec
     */
    public function create($key, $min, $max)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('ZREMRANGEBYSCORE',
                         $this->_rediska->getOption('namespace') . $key,
                         $min,
                         $max);
        
        return new Rediska_Connection_Exec($connection, $command);
    }
}