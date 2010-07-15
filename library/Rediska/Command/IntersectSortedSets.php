<?php

/**
 * Store to key intersection between sorted sets
 * 
 * @param array  $names       Array of key names or associative array with weights
 * @param string $storeName   Result sorted set key name
 * @param string $aggregation Aggregation method: SUM (for default), MIN, MAX.
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_IntersectSortedSets extends Rediska_Command_CompareSortedSets
{
	protected $_command = 'ZINTERSTORE';
	
    protected function _compareSets($sets)
    {
        return call_user_func_array('array_intersect', array_values($sets));
    }
}