<?php

/**
 * Remove all elements in the sorted set at key with rank between start  and end
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DeleteFromSortedSetByRank extends Rediska_Command_Abstract
{
    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.3.4';

    /**
     * Create command
     *
     * @param string  $key   Key name
     * @param numeric $start Start position
     * @param numeric $end   End position
     * @return Rediska_Connection_Exec
     */
    public function create($key, $start, $end)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('ZREMRANGEBYRANK',
                         $this->_rediska->getOption('namespace') . $key,
                         $start,
                         $end);

        return new Rediska_Connection_Exec($connection, $command);
    }
}