<?php

/**
 * Rediska connection exec
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
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
        $result = $this->_connection->write($this->_command);
        $this->_isWrited = true;

        return $result;
    }

    /**
     * Read response from connection
     * 
     * @return array|string
     */
    public function read()
    {
        if (!$this->_isWrited) {
            throw new Rediska_Connection_Exec_Exception('You must write command before read');
        }

        $response = self::readResponseFromConnection($this->_connection);

        $this->_isWrited = false;

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
     * Get connection
     * 
     * @return Rediska_Connection
     */
    public function getConnection()
    {
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
     * @return mixin
     */
    public static function readResponseFromConnection(Rediska_Connection $connection)
    {
        $reply = $connection->readLine();

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

                throw new Rediska_Command_Exception($message);
            case self::REPLY_INTEGER:
                if (strpos($data, '.') !== false) {
                    $number = (integer)$data;
                } else {
                    $number = (float)$data;
                }

                if ((string)$number != $data) {
                    throw new Rediska_Connection_Exec_Exception("Can't convert data ':$data' to integer");
                }

                return $number;
            case self::REPLY_BULK:
                if ($data == '-1') {
                    return null;
                } else {
                    $length = (integer)$data;
        
                    if ((string)$length != $data) {
                        throw new Rediska_Connection_Exec_Exception("Can't convert bulk reply header '$$data' to integer");
                    }

                    return $connection->read($length);
                }
            case self::REPLY_MULTY_BULK:
                $count = (integer)$data;

                if ((string)$count != $data) {
                    throw new Rediska_Connection_Exec_Exception("Can't convert multi-response header '$data' to integer");
                }

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