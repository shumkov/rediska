<?php

/**
 * Rediska connection
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Connection
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Connection extends Rediska_Options
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
     * host         - Redis server host. For default 127.0.0.1
     * port         - Redis server port. For default 6379
     * db           - Redis server DB index. For default 0
     * alias        - Redis server alias for operate keys on specified server. For default [host]:[port]
     * weight       - Weight of Redis server for key distribution. For default 1
     * password     - Redis server password. Optional
     * persistent   - Persistent connection to Redis server. For default false
     * timeout      - Connection timeout for Redis server. Optional
     * readTimeout  - Read timeout for Redis server
     * blockingMode - Blocking/non-blocking mode for reads
     * 
     * @var array
     */
    protected $_options = array(
        'host'         => self::DEFAULT_HOST,
        'port'         => self::DEFAULT_PORT,
        'db'           => self::DEFAULT_DB,
        'alias'        => null,
        'weight'       => self::DEFAULT_WEIGHT,
        'password'     => null,
        'persistent'   => false,
        'timeout'      => null,
        'readTimeout'  => null,
        'blockingMode' => true,
    );

    /**
     * Connect to redis server
     * 
     * @throws Rediska_Connection_Exception
     * @return boolean
     */
    public function connect() 
    {
        if (!$this->isConnected()) {
            $socketAddress = 'tcp://' . $this->getHost() . ':' . $this->getPort();

            if ($this->_options['persistent']) {
                $flag = STREAM_CLIENT_PERSISTENT | STREAM_CLIENT_CONNECT;
            } else {
                $flag = STREAM_CLIENT_CONNECT;
            }

            $this->_socket = @stream_socket_client($socketAddress, $errno, $errmsg, $this->getTimeout(), $flag);

            // Throw exception if can't connect
            if (!is_resource($this->_socket)) {
                $msg = "Can't connect to Redis server on {$this->getHost()}:{$this->getPort()}";
                if ($errno || $errmsg) {
                    $msg .= "," . ($errno ? " error $errno" : "") . ($errmsg ? " $errmsg" : "");
                }

                $this->_socket = null;

                throw new Rediska_Connection_Exception($msg);
            }

            // Set read timeout
            if ($this->_options['readTimeout'] != null) {
                $this->setReadTimeout($this->_options['readTimeout']);
            }

            // Set blocking mode
            if ($this->_options['blockingMode'] == false) {
                $this->setBlockingMode($this->_options['blockingMode']);
            }

            // Send password
            if ($this->getPassword() != '') {
                $auth = new Rediska_Connection_Exec($this, "AUTH {$this->getPassword()}");
                try {
                   $auth->execute();
                } catch (Rediska_Command_Exception $e) {
                    throw new Rediska_Connection_Exception("Password error: {$e->getMessage()}");
                }
            }

            // Set db
            if ($this->_options['db'] !== self::DEFAULT_DB) {
                $selectDb = new Rediska_Connection_Exec($this, "SELECT {$this->_options['db']}");
                try {
                   $selectDb->execute();
                } catch (Rediska_Command_Exception $e) {
                    throw new Rediska_Connection_Exception("Select db error: {$e->getMessage()}");
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

            while ($string) {
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
     * @throws Rediska_Connection_TimeoutException
     * @return string
     */
    public function readLine()
    {
        if (!$this->isConnected()) {
            throw new Rediska_Connection_Exception("Can't read without connection to Redis server. Do connect or write first.");
        }

        $reply = @fgets($this->_socket);

        $info = stream_get_meta_data($this->_socket);
        if ($info['timed_out']) {
            throw new Rediska_Connection_TimeoutException("Connection read timed out.");
        }

        if ($reply === false) {
            if ($this->_options['blockingMode'] || (!$this->_options['blockingMode'] && $info['eof'])) {
                $this->disconnect();
                throw new Rediska_Connection_Exception("Can't read from socket.");
            }

            $reply = null;
        } else {
            $reply = trim($reply);
        }

        return $reply;
    }

    /**
     * Set read timeout
     * 
     * @param $timeout
     * @return Rediska_Connection
     */
    public function setReadTimeout($timeout)
    {
        $this->_options['readTimeout'] = $timeout;

        if ($this->isConnected()) {
            $seconds = floor($this->_options['readTimeout']);
            $microseconds = ($this->_options['readTimeout'] - $seconds) * 1000000;

            stream_set_timeout($this->_socket, $seconds, $microseconds);
        }

        return $this;
    }

    /**
     * Set blocking/non-blocking mode for reads
     * 
     * @param $flag
     * @return Rediska_Connection
     */
    public function setBlockingMode($flag = true)
    {
        $this->_options['blockingMode'] = $flag;

        if ($this->isConnected()) {
            stream_set_blocking($this->_socket, $this->_options['blockingMode']);
        }

        return $this;
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

    /**
     * Read and throw exception if somthing wrong
     * 
     * @param $length Lenght of bytes to read
     * @return string
     */
    protected function _readAndThrowException($length)
    {
        $data = @stream_get_contents($this->_socket, $length);

        $info = stream_get_meta_data($this->_socket);
        if ($info['timed_out']) {
            throw new Rediska_Connection_TimeoutException("Connection read timed out.");
        }

        if ($data === false) {
            $this->disconnect();
            throw new Rediska_Connection_Exception("Can't read from socket.");
        }

        return $data;
    }

    /**
     * Magic method for execute command in connection
     *
     * @return string
     */
    public function __invoke($command)
    {
        $exec = new Rediska_Connection_Exec($this, $command);

        return $exec->execute();
    }

    /**
     * Return alias to strings
     */
    public function __toString()
    {
        return $this->getAlias();
    }

    /**
     * Disconnect on destrcuct connection object
     */
    public function __destruct()
    {
        if (!$this->_options['persistent']) {
            $this->disconnect();
        }
    }

    /**
     * Do not clone socket
     */
    public function __clone()
    {
        $this->_socket = null;
    }
}