<?php

/**
 * Get value from hash field or fields
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetFromHash extends Rediska_Command_Abstract
{
    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.3.10';

    protected $_fields = array();

    /**
     * Create command
     *
     * @param string       $key           Key name
     * @param string|array $fieldOrFields Field or fields
     * @return Rediska_Connection_Exec
     */
    public function create($key, $fieldOrFields)
    {
        if (is_array($fieldOrFields)) {
            $this->_fields = array_values($fieldOrFields);

            if (empty($this->_fields)) {
                throw new Rediska_Command_Exception('Not present fields');
            }

            $command = array_merge(array('HMGET', $this->_rediska->getOption('namespace') . $key), $this->_fields);
        } else {
            $field = $fieldOrFields;
            $command = array('HGET',
                             $this->_rediska->getOption('namespace') . $key,
                             $field);
        }

        $connection = $this->_rediska->getConnectionByKeyName($key);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param array|string $response
     * @return mixed
     */
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