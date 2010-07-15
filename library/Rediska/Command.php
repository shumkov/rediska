<?php

/**
 * Rediska command
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command
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
     * Constructor
     * 
     * @param Rediska_Connection $connection Connection
     * @param array|string       $command    Command
     */
    public function __construct($connection, $command)
    {
        if (is_array($command)) {
            $command = self::transformMultiBulkCommand($command);
        }

        $this->_connection = $connection;
        $this->_command    = $command;
    }

    /**
     * Execute command
     * 
     * @return mixin
     */
    public function execute()
    {
        $this->_connection->write($this->_command);
        return self::readResponseFromConnection($this->_connection);
    }

    /**
     * Transfrom Multi Bulk command to string
     * 
     * @param array $command
     * @return string
     */
    public static function transformMultiBulkCommand(array $command)
    {
        $commandString = '*' . count($command) . Rediska::EOL;
        foreach($command as $argument) {
            $commandString .= '$' . strlen($argument) . Rediska::EOL . $argument . Rediska::EOL;
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
                    throw new Rediska_Command_Exception("Can't convert data ':$data' to integer");
                }

                return $number;
            case self::REPLY_BULK:
                if ($data == '-1') {
                    return null;
                } else {
                    $length = (integer)$data;
        
                    if ((string)$length != $data) {
                        throw new Rediska_Command_Exception("Can't convert bulk reply header '$$data' to integer");
                    }

                    return $connection->read($length);
                }
            case self::REPLY_MULTY_BULK:
                $count = (integer)$data;

                if ((string)$count != $data) {
                    throw new Rediska_Command_Exception("Can't convert multi-response header '$data' to integer");
                }

                $replies = array();
                for ($i = 0; $i < $count; $i++) {
                    $replies[] = self::readResponseFromConnection($connection);
                }

                return $replies;          
            default:
                throw new Rediska_Command_Exception("Invalid reply type: '$type'");
        }
    }
}