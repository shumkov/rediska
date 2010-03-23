<?php

/**
 * @see Rediska_Connection_Exception
 */
require_once 'Rediska/Connection/Exception.php';

/**
 * Rediska connection
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Connection
{
	const DEFAULT_HOST   = '127.0.0.1';
    const DEFAULT_PORT   = 6379;
    const DEFAULT_WEIGHT = 1;
    const DEFAULT_DB     = 0;

    /**
     * Socket
     * 
     * @var stream
     */
    protected $_socket;

    /**
     * Options
     * 
     * host       - Redis server host. For default 127.0.0.1
     * port       - Redis server port. For default 6379
     * db         - Redis server DB index. For default 0
     * weight     - Weight of Redis server for key distribution. For default 1
     * persistent - Persistent connection to Redis server. For default false
     * password   - Redis server password. Optional
     * timeout    - Connection timeout for Redis server. Optional
     * alias      - Redis server alias for operate keys on specified server. For default [host]:[port]
     * 
     * @var array
     */
	protected $_options = array(
	   'host'       => self::DEFAULT_HOST,
	   'port'       => self::DEFAULT_PORT,
	   'weight'     => self::DEFAULT_WEIGHT,
	   'persistent' => false,
	   'password'   => null,
	   'timeout'    => null,
	   'alias'      => null,
	   'db'         => self::DEFAULT_DB,
	);

	/**
     * Contruct Rediska connection
     * 
     * @param Rediska $rediska
     * @param array $options Options (see $_options description)
     */
	public function __construct(array $options = array())
	{
		$options = array_change_key_case($options, CASE_LOWER);
        $options = array_merge($this->_options, $options);

		$this->setOptions($options);
	}

	/**
	 * Disconnect on destrcuct connection object
	 */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Set options array
     * 
     * @param array $options Options (see $_options description)
     * @return Rediska_Connection
     */
    public function setOptions(array $options)
    {
        foreach($options as $name => $value) {
            if (method_exists($this, "set$name")) {
                call_user_func(array($this, "set$name"), $value);
            } else {
                $this->setOption($name, $value);
            }
        }

        return $this;
    }

    /**
     * Set option
     * 
     * @throws Rediska_Connection_Exception
     * @param string $name Name of option
     * @param mixed $value Value of option
     * @return Rediska_Connection
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->_options)) {
            throw new Rediska_Connection_Exception("Unknown option '$name'");
        }

        $this->_options[$name] = $value;

        return $this;
    }

    /**
     * Get option
     * 
     * @throws Rediska_Connection_Exception 
     * @param string $name Name of option
     * @return mixed
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->_options)) {
            throw new Rediska_Connection_Exception("Unknown option '$name'");
        }

        return $this->_options[$name];
    }

    /**
     * Connect to redis server
     * 
     * @throws Rediska_Connection_Exception
     * @return boolean
     */
    public function connect() 
    {
        if (!$this->isConnected()) {
        	// TODO: stream_set_timeout() ?

        	$socketAddress = 'tcp://' . $this->getHost() . ':' . $this->getPort();
        	
        	if ($this->_options['persistent']) {
                $flag = STREAM_CLIENT_PERSISTENT | STREAM_CLIENT_CONNECT;
            } else {
                $flag = STREAM_CLIENT_CONNECT;
            }

            $this->_socket = @stream_socket_client($socketAddress, $errno, $errmsg, $this->getTimeout(), $flag);

	        if (!is_resource($this->_socket)) {
	            $msg = "Can't connect to Redis server on {$this->getHost()}:{$this->getPort()}";
	            if ($errno || $errmsg) {
	                $msg .= "," . ($errno ? " error $errno" : "") . ($errmsg ? " $errmsg" : "");
	            }

	            $this->_socket = null;

	            throw new Rediska_Connection_Exception($msg);
	        }

	        if ($this->getPassword() != '') {
	        	$this->write('AUTH ' . $this->getPassword());
	        	$reply = $this->readLine();
                if (substr($reply, 0, 1) == '-') {
                    throw new Rediska_Connection_Exception("Password error: " . substr($reply, 5));
                }
	        }

	        if ($this->_options['db'] !== self::DEFAULT_DB) {
	        	$this->write('SELECT ' . $this->_options['db']);
	        	$reply = $this->readLine();
	        	if (substr($reply, 0, 1) == '-') {
	        		throw new Rediska_Connection_Exception("Select db error: " . substr($reply, 5));
	        	}
	        }

	        return true;
        } else {
        	return false;
        }
    }
    
    /**
     * Disconnect
     * 
     * @return boolean
     */
    public function disconnect() 
    {
        if ($this->isConnected()) {
            @fclose($this->_socket);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Is connected
     * 
     * @return boolean
     */
    public function isConnected()
    {
        return is_resource($this->_socket);
    }

    /**
     * Write to connection stream
     * 
     * @param $string
     * @return boolean
     */
    public function write($string) 
    {
        if ($string !== '') {
            $string = (string)$string . Rediska::EOL;

            $this->connect();

	        while ($string !== '') {
	            $bytes = @fwrite($this->_socket, $string);
	
	            if ($bytes === false) {
	                $this->disconnect();
	                throw new Rediska_Connection_Exception("Can't write to socket.");
	            }
	
	            if ($bytes == 0) {
	                return true;
	            }
	
	            $string = substr($string, $bytes);
	        }

	        return true;
        } else {
        	return false;
        }
    }
    
    /**
     * Read length bytes from connection stram
     * 
     * @throws Rediska_Connection_Exception
     * @param integer $length
     * @return boolean
     */
    public function read($length)
    {
        if (!$this->isConnected()) {
            throw new Rediska_Connection_Exception("Can't read without connection to Redis server. Do connect or write first.");
        }

        if ($length > 0) {
        	$data = $this->_readAndThrowException($length);
        } else {
        	$data = null;
        }

        if ($length !== -1) {
            $this->_readAndThrowException(2);
        }

        return $data;
    }

    /**
     * Read line from connection stream
     * 
     * @throws Rediska_Connection_Exception
     * @return string
     */
    public function readLine()
    {
    	if (!$this->isConnected()) {
            throw new Rediska_Connection_Exception("Can't read without connection to Redis server. Do connect or write first.");
    	}

    	$string = @fgets($this->_socket);

        if ($string === false) {
            $this->disconnect();
            throw new Rediska_Connection_Exception("Can't read from socket.");
        }

        return trim($string);
    }

    /**
     * Get option host
     * 
     * @return string
     */
    public function getHost()
    {
        return $this->_options['host'];
    }

    /**
     * Get option port
     * 
     * @return string
     */
    public function getPort()
    {
    	return $this->_options['port'];
    }

    /**
     * Get option weight
     * 
     * @return string
     */
    public function getWeight()
    {
    	return $this->_options['weight'];
    }

    /**
     * Get option password
     * 
     * @return string
     */
    public function getPassword()
    {
        return $this->_options['password'];
    }
    
    /**
     * Get option timout
     * 
     * @return string
     */
    public function getTimeout()
    {
    	if (null !== $this->_options['timeout']) {
    		return $this->_options['timeout'];
    	} else {
    		return ini_get('default_socket_timeout');
    	}
    }
    
    /**
     * Connection alias
     * 
     * @return string
     */
    public function getAlias()
    {
    	if ($this->_options['alias'] != '') {
            return $this->_options['alias'];
        } else {
            return $this->_options['host'] . ':' . $this->_options['port'];
        }
    }
    
    protected function _readAndThrowException($length)
    {
        $data = @stream_get_contents($this->_socket, $length);

        if ($data === false) {
            $this->disconnect();
            throw new Rediska_Connection_Exception("Can't read from socket.");
        }

        return $data;
    }

    public function __toString()
    {
        return $this->getAlias();
    }
}