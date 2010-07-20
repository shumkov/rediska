<?php

/**
 * Get rank of member from sorted set
 * 
 * @param string  $name   Key name
 * @param integer $value  Member value
 * @param boolean $revert Revert elements (not used in sorting)
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetRankFromSortedSet extends Rediska_Command_Abstract
{
    protected $_version = '1.3.4';

    public function create($name, $value, $revert = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = array($revert ? 'ZREVRANK' : 'ZRANK',
                         "{$this->_rediska->getOption('namespace')}$name",
                         $value);

        return new Rediska_Connection_Exec($connection, $command);
    }
}