<?php

/**
 * Return the difference between the Set stored at key1 and all the Sets key2, ..., keyN
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DiffSets extends Rediska_Command_CompareSets
{
    protected $_command = 'SDIFF';
    protected $_storeCommand = 'SDIFFSTORE';

    protected function _compareSets($sets)
    {
        return call_user_func_array('array_diff', $sets);
    }
}