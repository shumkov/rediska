<?php

/**
 * @see Rediska_Command_CompareSets
 */
require_once 'Rediska/Command/CompareSets.php';

/**
 * Return the difference between the Set stored at key1 and all the Sets key2, ..., keyN
 * 
 * @param array       $names     Array of key names
 * @param string|null $storeName Store union to set with key name
 * @return array|boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DiffSets extends Rediska_Command_CompareSets
{
	protected $_command = 'SDIFF';
    protected $_storeCommand = 'SDIFFSTORE';

    protected function _prepareValues($response)
    {
        return call_user_func_array('array_diff', $response);
    }
}