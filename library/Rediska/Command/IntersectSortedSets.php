<?php

/**
 * Store to key intersection between sorted sets
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_IntersectSortedSets extends Rediska_Command_CompareSortedSets
{
    protected $_command = 'ZINTERSTORE';
    
    protected function _compareSets($sets)
    {
        return call_user_func_array('array_intersect', array_values($sets));
    }
}