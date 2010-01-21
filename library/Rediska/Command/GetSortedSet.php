<?php
/**
 * Get all the members of the Sorted Set value at key
 * 
 * @throws Rediska_Command_Exception
 * @param string         $name        Key name
 * @param integer|string $limitOrSort Limit of elements or sorting query
 *                                    ALPHA work incorrect becouse values in List serailized
 *                                    Read more: http://code.google.com/p/redis/wiki/SortCommand
 * @param integer        $offset      Offset (not using in sorting)
 * @param boolean        $revert      Revert elements (not used in sorting)
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.3.0
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSortedSet extends Rediska_Command_Abstract
{
    protected function _create($name, $limitOrSort = null, $offset = null, $revert = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        if (is_null($limitOrSort) || is_numeric($limitOrSort)) {
            $limit = $limitOrSort;

            if (!is_null($limit) && !is_integer($limit)) {
                throw new Rediska_Command_Exception("Limit must be integer");
            }

            if (is_null($offset)) {
                $offset = 0;
            } else if (!is_integer($offset)) {
                throw new Rediska_Command_Exception("Offset must be integer");
            }

            $start = $offset;

            if (is_null($limit)) {
                $end = -1;
            } else {
                $end = $offset + $limit - 1;
            }

            $command = array($revert ? 'ZREVRANGE' : 'ZRANGE',
                             "{$this->_rediska->getOption('namespace')}$name",
                             $start,
                             $end);
        } else {
            $sort = $limitOrSort;

            if (!is_null($offset)) {
                throw new Rediska_Command_Exception("Offset not used with sorting query. Use LIMIT in query.");
            }

            if ($revert) {
                throw new Rediska_Command_Exception("Revert not used with sorting query. Use DESC in query.");
            }

            $command = "SORT {$this->_rediska->getOption('namespace')}$name $sort";
        }

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        $values = $response[0];

        foreach($values as &$value) {
            $value = $this->_rediska->unserialize($value);
        }

        return $values;
    }
}