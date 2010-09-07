<?php

/**
 * Store to key union between the sorted sets
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_UnionSortedSets extends Rediska_Command_CompareSortedSets
{
    protected $_command = 'ZUNIONSTORE';

    protected function _compareSets($sets)
    {
        $resultSet = array();
        foreach($sets as $name => $values) {
            $resultSet = array_merge($resultSet, $values);
        }
        return array_unique($resultSet);
    }
}