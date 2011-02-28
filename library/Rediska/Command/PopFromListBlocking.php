<?php

/**
 * Return and remove the last element of the List at key and block if list is empty or not exists
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_PopFromListBlocking extends Rediska_Command_Abstract
{
    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.3.1';

    /**
     * Store connection
     *
     * @var Rediska_Connection
     */
    protected $_storeConnection;

    /**
     * Create command
     *
     * @param string|array $keyOrKeys           Key name or array of names
     * @param integer      $timeout[optional]   Timeout. 0 for default - timeout is disabled.
     * @param string       $pushToKey[optional] If not null - push value to another list.
     * @return Rediska_Connection_Exec
     */
    public function create($keyOrKeys, $timeout = 0, $pushToKey = null)
    {
        // TODO: Refactor this shit

        if ($pushToKey !== null) {
            if (is_array($keyOrKeys)) {
                throw new Rediska_Command_Exception('PopFromListBlocking with $pushToKey argument accept only one key');
            }

            $key = $keyOrKeys;

            $connection = $this->getRediska()->getConnectionByKeyName($key);

            $this->_storeConnection = $this->getRediska()->getConnectionByKeyName($pushToKey);

            if ($connection != $this->_storeConnection) {
                $this->setAtomic(false);

                $command = array('BRPOP',
                                 $this->getRediska()->getOption('namespace') . $key);
            } else {
                $this->_throwExceptionIfNotSupported('2.1.7');

                $command = array('BRPOPLPUSH',
                                 $this->getRediska()->getOption('namespace') . $key,
                                 $this->getRediska()->getOption('namespace') . $pushToKey,
                                 $timeout);
            }

            return new Rediska_Connection_Exec($connection, $command);
        } else {
            $keys = array();
            if (!is_array($keyOrKeys)) {
                $keys = array($keyOrKeys);
            } elseif (!empty($keyOrKeys)) {
                $keys = $keyOrKeys;
            } else {
                throw new Rediska_Command_Exception('Not present keys for pop');
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
                throw new Rediska_Command_Exception("Blocking pop until worked only with one connection. Try to use Rediska#on() method for specify it.");
            }

            $execs = array();
            foreach ($keysByConnections as $connectionAlias => $keys) {
                $command = array('BRPOP');
                foreach($keys as $key) {
                    $command[] = $this->_rediska->getOption('namespace') . $key;
                }
                $command[] = $timeout;

                $execs[] = new Rediska_Connection_Exec($connections[$connectionAlias], $command);
            }

            return $execs;
        }
    }

    /**
     * Parse response
     * 
     * @param string|array $response
     * @return mixed
     */
    public function parseResponse($response)
    {
        if ($this->pushToKey !== null) {
            if (empty($response)) {
                return null;
            }

            if (!$this->isAtomic()) {
                $command = array('LPUSH',
                                 $this->_rediska->getOption('namespace') . $this->pushToKey,
                                 $response[1]);

                $exec = new Rediska_Connection_Exec($this->_storeConnection, $command);
                $exec->execute();

                $value = $response[1];
            } else {
                $value = $response;
            }

            return $this->_rediska->getSerializer()->unserialize($value);
        } else {
            if (!is_array($this->keyOrKeys) && !empty($response)) {
                $result = $this->_rediska->getSerializer()->unserialize($response[1]);
            } else {
                $result = Rediska_Command_Response_ListNameAndValue::factory($this->_rediska, $response);
            }

            return $result;
        }
    }
}