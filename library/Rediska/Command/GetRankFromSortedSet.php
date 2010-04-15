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

    protected function _create($name, $value, $revert = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);
        
        $value = $this->_rediska->serialize($value);

        $command = array($revert ? 'ZREVRANK' : 'ZRANK',
                         "{$this->_rediska->getOption('namespace')}$name",
                         $value);

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        return $responses[0];
    }
}