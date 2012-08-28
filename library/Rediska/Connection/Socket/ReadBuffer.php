<?php

class Rediska_Connection_Socket_ReadBuffer
{
    /**
     * @var Rediska_Connection_Socket
     */
    protected $_connection;

    protected $_data = array();
    protected $_length = 0;

    public function __construct(Rediska_Connection_Socket $connection)
    {
        $this->_connection = $connection;
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function append($data)
    {
        $this->_data[] = $data;
        $this->_length += strlen($data);
    }

    public function prepend($data)
    {
        array_unshift($this->_data, $data);
        $this->_length += strlen($data);
    }

    public function getLength()
    {
        return $this->_length;
    }

    public function readLine()
    {
        $eol = false;
        $data = '';

        while (!$eol) {
            // check buffer ready
            if ($this->getLength() <= 0) {
                $this->_readFromSocket();
            }

            $data .= $this->_getChunk();

            $eolPosition = strpos($data, Rediska::EOL);

            // need more data
            if ($eolPosition === false){
                continue;
            }

            $eol = true;// got EOL

            // fix buffer data
            if ($eolPosition + 2 < strlen($data)) {
                $this->prepend(substr($data, $eolPosition + 2));
            }
        }

        return substr($data, 0, $eolPosition);
    }

    public function read($length)
    {
        $got = 0;
        $data = array();

        while ($got < $length) {
            $need = $length - $got;

            // check buffer ready
            if ($need > $this->getLength()){
                $this->_readFromSocket();
            }

            $chunk = $this->_getChunk();
            $chunkLength = strlen($chunk);

            // chunk too big
            if ($got + $chunkLength > $length){
                //split chunk to fix length
                $data[] = substr($chunk, 0, $need);

                $got += $need;

                // fix buffer
                $this->prepend(substr($chunk, $need, $chunkLength - $need));
            } else {
                // small chunk
                $data[] = $chunk;
                $got += $chunkLength;
            }

            if ($got == $length){
                break;
            }
        }

        return implode('', $data);
    }

    public function _readFromSocket()
    {
        // Get read timeout
        $timeout = $this->getConnection()->getOption('readTimeout');
        if ($timeout !== null) {
            $timeoutSeconds = floor($timeout);
            $timeoutMicroseconds = ($timeout - $timeoutSeconds) * 1000000;
        } else {
            $timeoutSeconds = null;
            $timeoutMicroseconds = null;
        }

        $r = array($this->getConnection()->getSocket());
        $w = null;
        $e = null;
        $result = socket_select($r, $w, $e, $timeoutSeconds, $timeoutMicroseconds);

        if ($result === false) {
            $this->getConnection()->disconnect();
            throw new Rediska_Connection_Exception("Can't read from socket.");
        }

        if ($result === 0) {
            throw new Rediska_Connection_TimeoutException("Connection read timed out.");
        }

        while (true) {
            $data = socket_read($this->getConnection()->getSocket(), 1024);

            if ($data === false){
                $errorCode = socket_last_error();
                $errorMessage = socket_strerror($errorCode);

                // EAGAIN, it's OK
                if ($errorCode == 35) {
                    break;
                }

                $this->getConnection()->disconnect();

                throw new Rediska_Connection_Exception("Can't read from socket: " . $errorMessage);
            }

            if ($data == '') {
                break;
            }

            $this->append($data);
        }
    }

    protected function _getChunk()
    {
        $chunk = array_shift($this->_data);
        $this->_length -= strlen($chunk);

        return $chunk;
    }
}
