<?php

/**
 * Get rank of member from sorted set
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetRankFromSortedSet extends Rediska_Command_Abstract
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
     * @param string  $key              Key name
     * @param integer $member           Member value
     * @param boolean $revert[optional] Revert elements (not used in sorting). For default is false
     * @return Rediska_Connection_Exec
     */
    public function create($key, $member, $revert = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $member = $this->_rediska->getSerializer()->serialize($member);

        $command = array($revert ? 'ZREVRANK' : 'ZRANK',
                         $this->_rediska->getOption('namespace') . $key,
                         $member);

        return new Rediska_Connection_Exec($connection, $command);
    }
}