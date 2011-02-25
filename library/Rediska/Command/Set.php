<?php

/**
 * Set value to a key or muliple values to multiple keys
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Set extends Rediska_Command_Abstract
{
    protected $_multiple = false;

    /**
     * Create command
     *
     * @param string|array $keyOrData                  Key name or array with key => value.
     * @param mixed        $valueOrOverwrite[optional] Value or overwrite property for array of values. For default true.
     * @param boolean      $overwrite[optional]        Overwrite for single value (if false don't set and return false if key already exist). For default true.
     * @return Rediska_Connection_Exec
     */
    public function create($keyOrData, $valueOrOverwrite = null, $overwrite = true)
    {
        if (is_array($keyOrData)) {
            $this->_throwExceptionIfNotSupported('1.1');

            $this->_multiple = true;
            $data = $keyOrData;
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

            $commands = array();
            $namespace = $this->_rediska->getOption('namespace');
            foreach($keysByConnections as $connectionAlias => $data) {
                $command = array($overwrite ? 'MSET' : 'MSETNX');
                foreach($data as $key => $value) {
                    $command[] = $namespace . $key;
                    $command[] = $this->_rediska->getSerializer()->serialize($value);
                }
                $commands[] = new Rediska_Connection_Exec($connections[$connectionAlias], $command);
            }

            return $commands;
        } else {
            $key   = $keyOrData;
            $value = $valueOrOverwrite;

            $connection = $this->_rediska->getConnectionByKeyName($key);

            $value = $this->_rediska->getSerializer()->serialize($value);

            $command = array($overwrite ? 'SET' : 'SETNX',
                             $this->_rediska->getOption('namespace') . $key,
                             $value);

            return new Rediska_Connection_Exec($connection, $command);
        }
    }

    /**
     * Parse responses
     *
     * @param array $responses
     * @return boolean
     */
    public function parseResponses($responses)
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