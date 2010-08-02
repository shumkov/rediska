<?php

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
    const QUEUED = 'QUEUED';

    /**
     * Command version
     * 
     * @var $_version string
     */
    protected $_version = '1.0';

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
     * Execs
     * 
     * @var array
     */
    protected $_execs = array();
    
    /**
     * Atomic flag for pipelines
     * 
     * @var boolean
     */
    protected $_atomic = true;

    /**
     * Command is writed to connection
     * 
     * @var unknown_type
     */
    protected $_isWrited = false;
    
    /**
     * Is queued to transaction
     * 
     * @var boolean
     */
    protected $_isQueued = false;

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

        $this->_throwExceptionIfNotSupported();

        $this->_validateArguments($arguments);

        $this->_execs = call_user_func_array(array($this, 'create'), $arguments);

        if (!is_array($this->_execs)) {
            $this->_execs = array($this->_execs);
        }
    }

    /**
     * Write commands
     * 
     * @return boolean
     */
    public function write()
    {
        foreach($this->_execs as $exec) {
            $exec->write();
        }

        $this->_isWrited = true;

        return true;
    }

    /**
     * Read reponses from connection
     * 
     * @return array
     */
    public function read()
    {
        $responses = array();

        foreach ($this->_execs as $exec) {
            $responses[] = $exec->read();
        }

        if (isset($responses[0]) && $responses[0] === self::QUEUED) {
            $this->_isQueued = true;

            return true;
        } else {
            $this->_isWrited = false;
            return $this->parseResponses($responses);
        }
    }

    /**
     * Execute a command
     *
     * @return mixin
     */
    public function execute()
    {
        $this->write();
        return $this->read();
    }

    /**
     * Magic method for execute
     *
     * @return mixin
     */
    public function __invoke()
    {
        return $this->execute();
    }

    /**
     * Parse responses
     *
     * @param array $responses
     * @return mixin
     */
    public function parseResponses($responses)
    {
        foreach($responses as &$response) {
            $response = $this->parseResponse($response);
        }

        if (sizeof($responses) == 1) {
            return $responses[0];
        }
    }

    /**
     * Parse response
     *
     * @param string|array $response
     * @return mixin
     */
    public function parseResponse($response)
    {
        return $response;
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

    /**
     * Get command name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Is command queued in transaction
     *
     * @return boolean
     */
    public function isQueued()
    {
        return $this->_isQueued;
    }

    /**
     * Magic method for get command argument
     *
     * @param string $name
     * @return mixin
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_arguments)) {
            return $this->_arguments[$name];
        } else {
            throw new Rediska_Command_Exception("Argument '$name' not present for command '$this->_name'");
        }
    }

    /**
     * Magic method for test if has command argument
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_arguments[$name]);
    }

    /**
     * Validate command arguments
     *
     * @param array $arguments
     * @return array
     */
    protected function _validateArguments($arguments)
    {
        $className = get_class($this);
        if (!isset(self::$_argumentNames[$className])) {
            $reflection = new ReflectionMethod($this, 'create');
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
    }

    /**
     * Throw exception if command not supported by this version of Redis
     *
     * @param string $version
     */
    protected function _throwExceptionIfNotSupported($version = null)
    {
        if (null === $version) {
            $version = $this->_version;
        }

        $redisVersion = $this->_rediska->getOption('redisVersion');

        if (version_compare($version, $redisVersion) == 1) {
            throw new Rediska_Command_Exception("Command '{$this->_name}' requires {$version}+ version of Redis server. Current version is {$redisVersion}. To change it specify 'redisVersion' option.");
        }
    }
}