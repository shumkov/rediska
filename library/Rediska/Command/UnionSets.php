<?php

/**
 * Return the union between the Sets stored at key1, key2, ..., keyN
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_UnionSets extends Rediska_Command_CompareSets
{
    protected $_command = 'SUNION';
    protected $_storeCommand = 'SUNIONSTORE';

    protected function _compareSets($sets)
    {
        $comparedSet = array();
        foreach($sets as $setValues) {
            $comparedSet = array_merge($comparedSet, $setValues);
        }
        return array_unique($comparedSet);
    }
}