<?php

/**
 * Delete a key or keys
 * 
 * @param string|array Key name or array of key names
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Delete extends Rediska_Command_Abstract
{
    protected function _create($nameOrNames)
    {
        if (is_array($nameOrNames)) {
        	$names = $nameOrNames;

        	if (empty($names)) {
        	   throw new Rediska_Command_Exception('Not present keys for delete');
        	}

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

            foreach($keysByConnections as $connectionAlias => $keys) {
                $command = "DEL ";
                foreach($keys as $key) {
                    $command .= " {$this->_rediska->getOption('namespace')}$key";
                }

                $this->_addCommandByConnection($connections[$connectionAlias], $command);
            }
        } else {
            $name = $nameOrNames;

            $connection = $this->_rediska->getConnectionByKeyName($name);

            $command = "DEL {$this->_rediska->getOption('namespace')}$name";

            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponse($response)
    {
        $count = 0;
        foreach($response as $result) {
            $count += $result;
        }
        return $count;
    }
}