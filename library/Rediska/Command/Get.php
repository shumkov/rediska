<?php

/**
 * Get value of key or array of values by array of keys
 * 
 * @param string|array $nameOrNames Key name or array of names
 * @return mixed
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Get extends Rediska_Command_Abstract
{
    protected $_multi = false;
    protected $_keys = array();
    protected $_keysByConnections = array();

    protected function _create($nameOrNames)
    {
        if (is_array($nameOrNames)) {
            $this->_multi = true;
            $names = $nameOrNames;

            if (empty($names)) {
            	throw new Rediska_Command_Exception('Not present keys for get');
            }

            $sortedResult = array();
            $this->_keys = $names;
            $connections = array();
            $keysByConnections = array();
            foreach ($names as $name) {
                $connection = $this->_rediska->getConnectionByKeyName($name);
                $connectionAlias = $connection->getAlias();
                if (!array_key_exists($connectionAlias, $connections)) {
                    $connections[$connectionAlias] = $connection;
                    $keysByConnections[$connectionAlias] = array();
                }
                $keysByConnections[$connectionAlias][] = $name;
            }

            $result = array();
            foreach($keysByConnections as $connectionAlias => $keys) {
                $command = "MGET ";
                foreach($keys as $key) {
                    $command .= " {$this->_rediska->getOption('namespace')}$key";
                    $this->_keysByConnections[] = $key;
                }

                $this->_addCommandByConnection($connections[$connectionAlias], $command);
            }
        } else {
            $name = $nameOrNames;

            $connection = $this->_rediska->getConnectionByKeyName($name);

            $command = "GET {$this->_rediska->getOption('namespace')}$name";
            
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponse($response)
    {
        if ($this->_multi) {
            $result = array();
            if (!empty($response)) {
                $responses = array();
                foreach($response as $responseByConnection) {
                    $responses = array_merge($responses, $responseByConnection);
                }
                $unsortedResult = array();
                foreach($this->_keysByConnections as $i => $key) {
                    $unsortedResult[$key] = $responses[$i];
                }
                foreach($this->_keys as $key) {
                    if (isset($unsortedResult[$key])) {
                        $result[$key] = $this->_rediska->unserialize($unsortedResult[$key]);
                    }
                }
            }

            return $result;
        } else {
            return $this->_rediska->unserialize($response[0]);
        }
    }
}