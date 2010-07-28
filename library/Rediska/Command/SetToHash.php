<?php

/**
 * Set value to a hash field or fields
 * 
 * @param string        $name         Key name
 * @param array|string  $fieldOrData  Field or array of many fields and values: field => value
 * @param mixin         $value        Value for single field
 * @param boolean       $overwrite    Overwrite for single field (if false don't set and return false if key already exist). For default true.
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SetToHash extends Rediska_Command_Abstract
{
    protected $_version = '1.3.10';

    public function create($name, $fieldOrData, $value = null, $overwrite = true)
    {
        if (is_array($fieldOrData)) {
            $data = $fieldOrData;

            if (empty($data)) {
                throw new Rediska_Command_Exception('Not present fields and values for set');
            }

            $command = array('HMSET', $this->_rediska->getOption('namespace') . $name);
            foreach($data as $field => $value) {
                $command[] = $field;
                $command[] = $this->_rediska->getSerializer()->serialize($value);
            }
        } else {
            $field = $fieldOrData;

            $value = $this->_rediska->getSerializer()->serialize($value);
    
            $command = array($overwrite ? 'HSET' : 'HSETNX', $this->_rediska->getOption('namespace') . $name, $field, $value);
        }
        
        $connection = $this->_rediska->getConnectionByKeyName($name);
        
        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}