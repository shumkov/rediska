<?php

/**
 * Set value to a key or muliple values to multiple keys
 * 
 * @param string|array $nameOrData       Key name or array with key => value.
 * @param mixed        $valueOrOverwrite Value or overwrite property for array of values. For default true.
 * @param boolean      $overwrite        Overwrite for single value (if false don't set and return false if key already exist). For default true.
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Set extends Rediska_Command_Abstract
{
    protected $_multiple = false;

    protected function _create($nameOrData, $valueOrOverwrite = null, $overwrite = true)
    {
        if (is_array($nameOrData)) {
            $this->_checkVersion('1.1');

            $this->_multiple = true;
            $data = $nameOrData;
            $overwrite = ($valueOrOverwrite === null || $valueOrOverwrite);

            if (empty($data)) {
                throw new Rediska_Command_Exception('Not present keys and values for set');
            }

            $connections = array();
            $keysByConnections = array();
            foreach ($data as $key => $value) {
                $connection = $this->_rediska->getConnectionByKeyName($key);
                $connectionAlias = $connection->getAlias();
                if (!array_key_exists($connectionAlias, $connections)) {
                    $connections[$connectionAlias] = $connection;
                    $keysByConnections[$connectionAlias] = array();
                }
                $keysByConnections[$connectionAlias][$key] = $value;
            }

            foreach($keysByConnections as $connectionAlias => $data) {
                $command = array($overwrite ? 'MSET' : 'MSETNX');
                foreach($data as $key => $value) {
                    $command[] = $this->_rediska->getOption('namespace') . $key;
                    $command[] = $this->_rediska->getSerializer()->serialize($value);
                }

                $this->_addCommandByConnection($connections[$connectionAlias], $command);
            }
        } else {
            $name = $nameOrData;
            $value = $valueOrOverwrite;

            $connection = $this->_rediska->getConnectionByKeyName($name);

            $value = $this->_rediska->getSerializer()->serialize($value);
    
            if ($overwrite) {
                $command = 'SET';
            } else {
                $command = 'SETNX';
            }
            $command .= " {$this->_rediska->getOption('namespace')}$name " . strlen($value) . Rediska::EOL . $value;
    
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponses($responses)
    {
        if ($this->_multiple) {
            if (!empty($responses)) {
                foreach($responses as $response) {
                    if (!$response) {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return (boolean)$responses[0];
        }
    }
}