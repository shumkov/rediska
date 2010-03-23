<?php

/**
 * @see Rediska_Command_Exception
 */
require_once 'Rediska/Command/Exception.php';

/**
 * Rediska command abstract class
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_Command_Abstract implements Rediska_Command_Interface
{
    const REPLY_STATUS     = '+';
    const REPLY_ERROR      = '-';
    const REPLY_INTEGER    = ':';
    const REPLY_BULK       = '$';
    const REPLY_MULTY_BULK = '*';

    /**
     * Rediska instance
     * 
     * @var Rediska
     */
    protected $_rediska;

    /**
     * Name of command
     * 
     * @var string
     */
    protected $_name;

    /**
     * Arguments
     * 
     * @var array
     */
    protected $_arguments = array();

    /**
     * Arguments name
     * 
     * @var unknown_type
     */
    static protected $_argumentNames = array();

    /**
     * Commands sorted by connection
     * 
     * @var array
     */
    protected $_commandsByConnections = array();
    
    /**
     * Atomic flag for pipelines
     * 
     * @var boolean
     */
    protected $_atomic = true;

    /**
     * Constructor
     * 
     * @param Rediska $rediska   Rediska instance
     * @param string  $name      Command name
     * @param array   $arguments Command arguments
     */
    public function __construct(Rediska $rediska, $name, $arguments)
    {
        $this->_rediska = $rediska;
        $this->_name    = $name;

		$className = get_class($this);
        if (!isset(self::$_argumentNames[$className])) {
    		$reflection = new ReflectionMethod($this, '_create');
    		self::$_argumentNames[$className] = array();
    		foreach($reflection->getParameters() as $parameter) {
    			self::$_argumentNames[$className][] = $parameter;
    		}
    	}

    	$count = 0;
    	foreach(self::$_argumentNames[$className] as $parameter) {
    		if (array_key_exists($count, $arguments)) {
    			$value = $arguments[$count];
    		} else if ($parameter->isOptional()) {
    			$value = $parameter->getDefaultValue();
    		} else {
    			throw new Rediska_Command_Exception("Argument '{$parameter->getName()}' not present for command '$this->_name'");
    		}
    		$this->_arguments[$parameter->getName()] = $value;
    		$count++;
    	}

        call_user_func_array(array($this, '_create'), $arguments);
    }

    /**
     * Write command to connection
     * 
     * @return boolean
     */
    public function write()
    {
        foreach($this->_commandsByConnections as $commandByConnection) {
        	list($connection, $command) = $commandByConnection;
            $connection->write($command);
        }

        return true;
    }

    /**
     * Read command from connection
     * 
     * @return array
     */
    public function read()
    {
        $response = array();

        foreach ($this->_commandsByConnections as $commandByConnection) {
        	list($connection, $command) = $commandByConnection;
            $response[] = $this->_readResponseFromConnection($connection);
        }

        return $this->_parseResponse($response);
    }

    /**
     * Check atomic command
     * 
     * @return boolean
     */
    public function isAtomic()
    {
    	return $this->_atomic;
    }

    /**
     * Set atomic command flag
     * 
     * @param boolean $flag
     * @return Rediska_Command_Abstract
     */
    public function setAtomic($flag = true)
    {
    	$this->_atomic = $flag;

    	return $this;
    }

    public function __get($name)
    {
    	if (array_key_exists($name, $this->_arguments)) {
    		return $this->_arguments[$name];
    	} else {
    		throw new Rediska_Command_Exception("Argument '$name' not present for command '$this->_name'");
    	}
    }

    public function __isset($name)
    {
    	return isset($this->_arguments[$name]);
    }

    protected function _addCommandByConnection(Rediska_Connection $connection, $command)
    {
        if (is_array($command)) {
            $commandString = '*' . count($command) . Rediska::EOL;
            foreach($command as $argument) {
                $commandString .= '$' . strlen($argument) . Rediska::EOL . $argument . Rediska::EOL;
            }
            $command = $commandString;
        }

        $this->_commandsByConnections[] = array($connection, $command);
    }

    protected function _readResponseFromConnection(Rediska_Connection $connection)
    {
        $reply = $connection->readLine();

        $type = substr($reply, 0, 1);
        $data = substr($reply, 1);

        switch($type) {
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
                    $replies[] = $this->_readResponseFromConnection($connection);
                }

                return $replies;          
            default:
                throw new Rediska_Command_Exception("Invalid reply type: '$type'");
        }
    }

    protected function _parseResponse($response)
    {
        return $response;
    }
}