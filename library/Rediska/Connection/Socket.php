<?php

/**
 * Rediska socket connection
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Connection
 * @version 0.5.6
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Connection_Socket extends Rediska_Connection
{
    /**
     * @var Rediska_Connection_Socket_ReadBuffer
     */
    protected $_readBuffer;

    public function __construct(array $options = array())
    {
        if (!function_exists('socket_create')) {
            throw new Rediska_Connection_Exception("Can't use socket connection: socket functions not present.");
        }

        parent::__construct($options);
    }

    /**
     * Connect to redis server
     * 
     * @throws Rediska_Connection_Exception
     * @return boolean
     */
    public function connect() 
    {
        if ($this->isConnected()) {
            return false;
        }

        $this->_socket = $this->_createSocketConnection();

        // Throw exception if can't connect
        if (!is_resource($this->_socket)) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);

            $msg = "Can't connect to Redis server on {$this->getHost()}:{$this->getPort()}";
            if ($errorCode || $errorMessage) {
                $msg .= "," . ($errorCode ? " error $errorCode" : "") . ($errorMessage ? " $errorMessage" : "");
            }

            $this->_socket = null;

            throw new Rediska_Connection_Exception($msg);
        }

        // Set read timeout
        if ($this->_options['readTimeout'] != null) {
            $this->setReadTimeout($this->_options['readTimeout']);
        }

        // Set blocking mode
        //if (1 || $this->_options['blockingMode'] == false) {
        //    $this->setBlockingMode($this->_options['blockingMode']);
        //}

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
    }

    /**
     * Disconnect
     * 
     * @return boolean
     */
    public function disconnect() 
    {
        if (!$this->isConnected()) {
            return false;
        }

        @socket_close($this->_socket);
        $this->_socket = null;
        $this->_readBuffer = null;

        return true;
    }

    /**
     * Write to connection stream
     *
     * @param $string
     * @return bool
     * @throws Rediska_Connection_Exception
     */
    public function write($string) 
    {
        if ($string == '') {
            return false;
        }

        $this->connect();

        $string = (string)$string . Rediska::EOL;

        $offset = 0;
        $length = strlen($string);

        while (true) {
            $sent = @socket_write($this->getSocket(), substr($string, $offset, $length), $length);

            if ($sent === false) {
                $errorCode = socket_last_error();
                $errorMessage = socket_strerror($errorCode);

                // in nonblock mode  EAGAIN is OK
                if ($errorCode == 35) continue;

                $this->disconnect();

                throw new Rediska_Connection_Exception("Can't write to socket: " . $errorMessage);
            }

            $offset += $sent;
            $length -= $sent;

            if ($length <= 0) {
                return true;
            }
        }

        return true;
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

        return $this->_getReadBuffer()->readLine();
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
        if ($flag !== true) {
            throw new Rediska_Connection_Exception('Rediska_Connection_Socket not supported blocking mode');
        }

        return $this;
    }

    /**
     * Get socket
     *
     * @return resource
     */
    public function getSocket()
    {
        return $this->_socket;
    }

    /**
     * Read and throw exception if somthing wrong
     *
     * @param $length Lenght of bytes to read
     * @return string
     */
    protected function _readAndThrowException($length)
    {
        return $this->_getReadBuffer()->read($length);
    }

    /**
     * Create socket connection
     *
     * @return null|resource
     */
    protected function _createSocketConnection()
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));

        socket_set_option($socket, SOL_SOCKET, TCP_NODELAY, 1);

        @socket_set_nonblock($socket);
        $result = @socket_connect($socket, $this->getHost(), $this->getPort());

        if ($result === false) {
            $errorCode = socket_last_error($socket);
            if ($errorCode !== SOCKET_EINPROGRESS) {
                return null;
            }
        }else{
            //@socket_set_block($socket);

            return $socket;
        }

        /* Do whatever we want while the connect is taking place. */
        $result = @socket_select($r = array($socket), $w = array($socket), $e = array($socket), $this->getTimeout());

        if ($result === 0 || $result === false) {
            return null;
        }

        if (!(($r && count($r)) || ($w && count($w))) || @socket_get_option($socket, SOL_SOCKET, SO_ERROR) != 0) {
            return null;
        }

        //@socket_set_block($socket);

        return $socket;
    }

    /**
     * Get read buffer
     *
     * @return Rediska_Connection_Socket_ReadBuffer
     */
    protected function _getReadBuffer()
    {
        if ($this->_readBuffer === null) {
            $this->_readBuffer = new Rediska_Connection_Socket_ReadBuffer($this);
        }

        return $this->_readBuffer;
    }
}