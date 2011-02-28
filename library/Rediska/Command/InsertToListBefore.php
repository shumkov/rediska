<?php

/**
 * Insert a new value as the element before the reference value
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_InsertToListBefore extends Rediska_Command_InsertToListAbstract
{
    /**
     * Create command
     *
     * @param string  $key            Key name
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return Rediska_Connection_Exec
     */
    public function create($key, $referenceValue, $value)
    {
        return $this->_create($key, self::BEFORE, $referenceValue, $value);
    }
}