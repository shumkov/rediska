<?php

/**
 * Return and remove the last element of the List at key and block if list is empty or not exists
 * 
 * @param string $nameOrNames Key name or array of names
 * @param string $timeout     Blocking timeout in seconds. Timeout disabled for default.
 * @return mixin
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_PopFromListBlocking extends Rediska_Command_Abstract
{
    protected $_version = '1.3.1';

    public function create($nameOrNames, $timeout = 0) 
    {
        if (!is_array($nameOrNames)) {
            $names = array($nameOrNames);
        } elseif (!empty($nameOrNames)) {
            $names = $nameOrNames;
        } else {
            throw new Rediska_Command_Exception('Not present keys for pop');
        }

        $connections = array();
        $namesByConnections = array();
        foreach ($names as $name) {
            $connection = $this->_rediska->getConnectionByKeyName($name);
            $connectionAlias = $connection->getAlias();
            if (!array_key_exists($connectionAlias, $connections)) {
                $connections[$connectionAlias] = $connection;
                $namesByConnections[$connectionAlias] = array();
            }
            $namesByConnections[$connectionAlias][] = $name;
        }

        // TODO: Implement for many connections
        if (count($namesByConnections) > 1) {
            throw new Rediska_Command_Exception("Blocking pop until worked only with one connection. Try to use Rediska#on() method for specify it.");
        }

        $execs = array();
        foreach($namesByConnections as $connectionAlias => $names) {
            $command = array('BRPOP');
            foreach($names as $name) {
                $command[] = "{$this->_rediska->getOption('namespace')}$name";
            }
            $command[] = $timeout;

            $execs[] = new Rediska_Connection_Exec($connections[$connectionAlias], $command);
        }

        return $execs;
    }

    public function parseResponse($response)
    {
        if (!is_array($this->nameOrNames) && !empty($response)) {
            $result = $this->_rediska->getSerializer()->unserialize($response[1]);
        } else {
            $result = Rediska_Command_Response_ListNameAndValue::factory($this->_rediska, $response);
        }

        return $result;
    }
}