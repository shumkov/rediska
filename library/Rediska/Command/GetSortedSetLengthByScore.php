<?php

/**
 * Get count of members from sorted set by min and max score
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSortedSetLengthByScore extends Rediska_Command_Abstract
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
     * @param string $key Key name
     * @param number $min Min score
     * @param number $max Max score
     * @return Rediska_Connection_Exec
     */
    public function create($key, $min, $max)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('ZCOUNT',
                         $this->_rediska->getOption('namespace') . $key,
                         $min,
                         $max);

        return new Rediska_Connection_Exec($connection, $command);
    }
}