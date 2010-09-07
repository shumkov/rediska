<?php

/**
 * Return and remove the first element of the List at key and block if list is empty or not exists
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_ShiftFromListBlocking extends Rediska_Command_Abstract
{
    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.3.1';

    /**
     * Create command
     *
     * @param string $keyOrKeys   Key name or array of names
     * @param string $timeout     Blocking timeout in seconds. Timeout disabled for default.
     * @return Rediska_Connection_Exec
     */
    public function create($keyOrKeys, $timeout = 0)
    {
        if (!is_array($keyOrKeys)) {
            $keys = array($keyOrKeys);
        } elseif (!empty($keyOrKeys)) {
            $keys = $keyOrKeys;
        } else {
            throw new Rediska_Command_Exception('Not present keys for shift');
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

        // TODO: Implement for many connections
        if (count($keysByConnections) > 1) {
            throw new Rediska_Command_Exception("Blocking shift until worked only with one connection. Try to use Rediska#on() method for specify it.");
        }

        $execs = array();
        foreach($keysByConnections as $connectionAlias => $keys) {
            $command = array('BLPOP');
            foreach($keys as $key) {
                $command[] = $this->_rediska->getOption('namespace') . $key;
            }
            $command[] = $timeout;

            $execs[] = new Rediska_Connection_Exec($connections[$connectionAlias], $command);
        }

        return $execs;
    }

    /**
     * Parse response
     *
     * @param array|string $response
     * @return mixed
     */
    public function parseResponse($response)
    {
        if (!is_array($this->keyOrKeys) && !empty($response)) {
            $result = $this->_rediska->getSerializer()->unserialize($response[1]);
        } else {
            $result = Rediska_Command_Response_ListNameAndValue::factory($this->_rediska, $response);
        }

        return $result;
    }
}