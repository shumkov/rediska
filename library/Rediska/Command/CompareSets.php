<?php

/**
 * Abstract class for union, intersection and diff of sets 
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_Command_CompareSets extends Rediska_Command_Abstract
{
    protected $_storeConnection;

    protected $_command;
    protected $_storeCommand;

    /**
     * Create command
     *
     * @param array            $keys     Array of key names
     * @param string[optional] $storeKey Store to set with key name
     * @return Rediska_Connection_Exec
     */
    public function create(array $keys, $storeKey = null)
    {
        if (empty($keys)) {
            throw new Rediska_Command_Exception('You must specify sets');
        }

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

        if (count($connections) == 1) {
            $connectionValues = array_values($connections);
            $connection = $connectionValues[0];

            if (!is_null($storeKey)) {
                $storeConnection = $this->_rediska->getConnectionByKeyName($storeKey);
                if ($storeConnection === $connection) {
                    $command = array($this->_storeCommand,
                                     $this->_rediska->getOption('namespace') . $storeKey);
                } else {
                    $this->setAtomic(false);
                    $this->_storeConnection = $storeConnection;
                    $command = array($this->_command);
                }
            } else {
                $command = array($this->_command);
            }

            $connectionKeys = array_keys($connections);
            $connectionAlias = $connectionKeys[0];

            foreach($keysByConnections[$connectionAlias] as $key) {
                $command[] = $this->_rediska->getOption('namespace') . $key;
            }

            return new Rediska_Connection_Exec($connection, $command);
        } else {
            $this->setAtomic(false);

            $commands = array();
            foreach($keysByConnections as $connectionAlias => $keys) {
                foreach ($keys as $key) {
                    $command = array('SMEMBERS',
                                     $this->_rediska->getOption('namespace') . $key);
                    $commands[] = new Rediska_Connection_Exec($connections[$connectionAlias], $command);
                }
            }

            return $commands;
        }
    }

    /**
     * Parse responses
     *
     * @param array $responses
     * @return boolean|array
     */
    public function parseResponses($responses)
    {
        if (!$this->isAtomic()) {
            if ($this->_storeConnection) {
                $values = $responses[0];
            } else {
                $values = array_values($this->_compareSets($responses));
            }

            $unserializedValues = array_map(array($this->_rediska->getSerializer(), 'unserialize'), $values);

            if (is_null($this->storeKey)) {
                return $unserializedValues;
            } else {
                $this->_rediska->delete($this->storeKey);
                foreach($unserializedValues as $value) {
                    $this->_rediska->addToSet($this->storeKey, $value);
                }
                return true;
            }
        } else {
            $reply = $responses[0];
            if (is_null($this->storeKey)) {
                $reply = array_map(array($this->_rediska->getSerializer(), 'unserialize'), $reply);
            } else {
                $reply = (boolean)$reply;
            }

            return $reply;
        }
    }

    abstract protected function _compareSets($sets);
}