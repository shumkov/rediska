<?php

/**
 * Get value from hash field or fields
 * 
 * @param string       $name          Key name
 * @param string|array $fieldOrFields Field or fields
 * @return mixin
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetFromHash extends Rediska_Command_Abstract
{ 
    protected $_version = '1.3.10';

    protected $_fields = array();

    public function create($name, $fieldOrFields)
    {
        if (is_array($fieldOrFields)) {
            $this->_fields = array_values($fieldOrFields);

            if (empty($this->_fields)) {
            	throw new Rediska_Command_Exception('Not present fields');
            }
            
            $command = array('HMGET', $this->_rediska->getOption('namespace') . $name) + $this->_fields;
        } else {
            $field = $fieldOrFields;
            $command = array('HGET', $this->_rediska->getOption('namespace') . $name, $field);
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);
        
        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        if (!empty($this->_fields)) {
            $result = array();
            $fieldsCount = count($this->_fields);
            for ($i = 0; $i < $fieldsCount; $i++) {
                $result[$this->_fields[$i]] = $this->_rediska->getSerializer()->unserialize($response[$i]);
            }
            return $result;
        } else {
            return $this->_rediska->getSerializer()->unserialize($response);
        }
    }
}