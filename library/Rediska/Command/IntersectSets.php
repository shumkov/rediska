<?php

/**
 * Return the intersection between the Sets stored at key1, key2, ..., keyN
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_IntersectSets extends Rediska_Command_CompareSets
{
    protected $_command = 'SINTER';
    protected $_storeCommand = 'SINTERSTORE';

    protected function _compareSets($sets)
    {
        return call_user_func_array('array_intersect', $sets);
    }
}