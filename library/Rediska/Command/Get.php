<?php

/**
 * Get value of key or array of values by array of keys
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Get extends Rediska_Command_Abstract
{ 
    protected $_keys = array();
    protected $_keysByConnections = array();

    /**
     * Create command
     *
     * @param string|array $keyOrKeys Key name or array of names
     * @return Rediska_Connection_Exec
     */
    public function create($keyOrKeys)
    {
        if (is_array($keyOrKeys)) {
            $this->_multi = true;
            $keys = $keyOrKeys;

            if (empty($keys)) {
                throw new Rediska_Command_Exception('Not present keys for get');
            }

            $sortedResult = array();
            $this->_keys = $keys;
            $connections = array();
            $keysByConnections = array();
            foreach ($keys as $key) {
                $connection = $this->_rediska->getConnectionByKeyName($key);
                $connectionAlias = $connection->getAlias();
                if (!array_key_exists($connectionAlias, $connections)) {
                    $connections[$connectionAlias] = $connection;
                    $keysByConnections[$connectionAlias] = array();
                }
                $keysByConnections[$connectionAlias][] = $key;
            }

            $result = array();
            $commands = array();
            foreach($keysByConnections as $connectionAlias => $keys) {
                $command = array('MGET');
                foreach($keys as $key) {
                    $command[] = $this->_rediska->getOption('namespace') . $key;
                    $this->_keysByConnections[] = $key;
                }
                $commands[] = new Rediska_Connection_Exec($connections[$connectionAlias], $command);
            }

            return $commands;
        } else {
            $key = $keyOrKeys;

            $connection = $this->_rediska->getConnectionByKeyName($key);

            $command = array('GET',
                             $this->_rediska->getOption('namespace') . $key);

            return new Rediska_Connection_Exec($connection, $command);
        }
    }

    /**
     * Parse responses
     *
     * @param array $responses
     * @return mixed
     */
    public function parseResponses($responses)
    {
        if (is_array($this->keyOrKeys)) {
            $result = array();
            if (!empty($responses)) {
                $mergedResponses = array();
                foreach($responses as $response) {
                    $mergedResponses = array_merge($mergedResponses, $response);
                }
                $unsortedResult = array();
                foreach($this->_keysByConnections as $i => $key) {
                    $unsortedResult[$key] = $mergedResponses[$i];
                }
                foreach($this->_keys as $key) {
                    if (isset($unsortedResult[$key])) {
                        $result[$key] = $this->_rediska->getSerializer()->unserialize($unsortedResult[$key]);
                    }
                }
            }

            return $result;
        } else {
            return $this->_rediska->getSerializer()->unserialize($responses[0]);
        }
    }
}