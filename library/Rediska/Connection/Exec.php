<?php

/**
 * Rediska connection exec
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Connection
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Connection_Exec
{
    const REPLY_STATUS     = '+';
    const REPLY_ERROR      = '-';
    const REPLY_INTEGER    = ':';
    const REPLY_BULK       = '$';
    const REPLY_MULTY_BULK = '*';

    /**
     * Rediska connection
     * 
     * @var Rediska_Connection
     */
    protected $_connection;

    /**
     * Cloned connection for iterator
     *
     * @var Rediska_Connection
     */
    protected $_connectionClone;

    /**
     * Command
     * 
     * @var array|string
     */
    protected $_command;

    /**
     * Is writed
     * 
     * @var $_isWrited string
     */
    protected $_isWrited = false;

    /**
     * Response callback
     * 
     * @var callback
     */
    protected $_responseCallback;

    /**
     * Retrun iterator as response
     *
     * @var mixin
     */
    protected $_responseIterator;

    /**
     * Constructor
     * 
     * @param Rediska_Connection $connection Connection
     * @param array|string       $command    Command
     */
    public function __construct(Rediska_Connection $connection, $command)
    {
        if (is_array($command)) {
            $command = self::transformMultiBulkCommand($command);
        }

        $this->_connection = $connection;
        $this->_command    = $command;
    }

    /**
     * Write command to connection
     * 
     * @return boolean
     */
    public function write()
    {
        $result = $this->getConnection()->write($this->getCommand());
        $this->_isWrited = true;

        return $result;
    }

    /**
     * Is writed?
     *
     * @return boolean
     */
    public function isWrited()
    {
        return $this->_isWrited;
    }

    /**
     * Read response from connection
     * 
     * @return array|string
     */
    public function read()
    {
        if (!$this->isWrited()) {
            throw new Rediska_Connection_Exec_Exception('You must write command before read');
        }

        $this->_isWrited = false;

        if ($this->getResponseIterator() !== null) {
            if ($this->getResponseIterator() === true) {
                $className = 'Rediska_Connection_Exec_MultiBulkIterator';
            } else {
                $className = $this->getResponseIterator();
            }

            $response = new $className($this->getConnection(), $this->getResponseCallback());
        } else {
            $response = self::readResponseFromConnection($this->getConnection());

            if ($this->_responseCallback !== null) {
                $response = call_user_func($this->getResponseCallback(), $response);
            }
        }

        return $response;
    }

    /**
     * Execute command
     * 
     * @return array|string
     */
    public function execute()
    {
        $this->write();
        return $this->read();
    }

    /**
     * Magic method for execute
     *
     * @return array|string
     */
    public function __invoke()
    {
        return $this->execute();
    }

    /**
     * Get connection
     * 
     * @return Rediska_Connection
     */
    public function getConnection()
    {
        if ($this->_responseIterator === null) {
            return $this->_connection;
        } else {
            if ($this->_connectionClone === null) {
                $this->_connectionClone = clone $this->_connection;
            }

            return $this->_connectionClone;
        }

        return $this->_connection;
    }
    
    /**
     * Get command
     * 
     * @return string
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * Set response callback
     *
     * @param mixin $callback
     * @return Rediska_Connection_Exec
     */
    public function setResponseCallback($callback)
    {
        if ($callback !== null && !is_callable($callback)) {
            throw new Rediska_Connection_Exec_Exception('Bad callback');
        }

        $this->_responseCallback = $callback;

        return $this;
    }

    /**
     * Get response callback
     *
     * @return mixin
     */
    public function getResponseCallback()
    {
        return $this->_responseCallback;
    }

    /**
     * Set response iterator
     *
     * @param boolean $enable
     */
    public function setResponseIterator($responseIterator)
    {
        $this->_responseIterator = $responseIterator;

        return $this;
    }

    /**
     * Is enabled response iterator
     *
     * @return boolean
     */
    public function getResponseIterator()
    {
        return $this->_responseIterator;
    }


    /**
     * Transfrom Multi Bulk command to string
     * 
     * @param array $command
     * @return string
     */
    public static function transformMultiBulkCommand(array $command)
    {
        $commandString = self::REPLY_MULTY_BULK . count($command) . Rediska::EOL;
        foreach($command as $argument) {
            $commandString .= self::REPLY_BULK . strlen($argument) . Rediska::EOL . $argument . Rediska::EOL;
        }
        return $commandString;
    }

    /**
     * Read response from connection
     * 
     * @param Rediska_Connection $connection
     * @return mixed
     */
    public static function readResponseFromConnection(Rediska_Connection $connection)
    {
        $reply = $connection->readLine();

        if ($reply === null) {
            return $reply;
        }

        $type = substr($reply, 0, 1);
        $data = substr($reply, 1);

        switch ($type) {
            case self::REPLY_STATUS:
                if ($data == 'OK') {
                    return true;
                } else {
                    return $data;
                }
            case self::REPLY_ERROR:
                $message = substr($data, 4);

                throw new Rediska_Connection_Exec_Exception($message);
            case self::REPLY_INTEGER:
                if (strpos($data, '.') !== false) {
                    $number = (integer)$data;
                } else {
                    $number = (float)$data;
                }

                return $number;
            case self::REPLY_BULK:
                if ($data == '-1') {
                    return null;
                } else {
                    $length = (integer)$data;

                    return $connection->read($length);
                }
            case self::REPLY_MULTY_BULK:
                $count = (integer)$data;

                $replies = array();
                for ($i = 0; $i < $count; $i++) {
                    $replies[] = self::readResponseFromConnection($connection);
                }

                return $replies;          
            default:
                throw new Rediska_Connection_Exec_Exception("Invalid reply type: '$type'");
        }
    }
}