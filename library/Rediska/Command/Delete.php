<?php

/**
 * Delete a key or keys
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Delete extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string|array $keyOrKeys Key name or array of key names
     * @return Rediska_Connection_Exec
     */
    public function create($keyOrKeys)
    {
        if (is_array($keyOrKeys)) {
            $keys = $keyOrKeys;

            if (empty($keys)) {
               throw new Rediska_Command_Exception('Not present keys for delete');
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

            $commands = array();
            foreach($keysByConnections as $connectionAlias => $keys) {
                $command = array('DEL');
                foreach($keys as $key) {
                    $command[] = $this->_rediska->getOption('namespace') . $key;
                }
                $commands[] = new Rediska_Connection_Exec($connections[$connectionAlias], $command);
            }

            return $commands;
        } else {
            $key = $keyOrKeys;

            $connection = $this->_rediska->getConnectionByKeyName($key);

            $command = array('DEL',
                             $this->_rediska->getOption('namespace') . $key);

            return new Rediska_Connection_Exec($connection, $command);
        }
    }

    /**
     * Parse responses
     * 
     * @param array $responses
     * @return integer
     */
    public function parseResponses($responses)
    {
        return array_sum($responses);
    }
}