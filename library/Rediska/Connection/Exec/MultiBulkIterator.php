<?php

/**
 * Rediska MultiBulk iterator
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Connection
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Connection_Exec_MultiBulkIterator implements Iterator, Countable
{
    /**
     * Connection
     *
     * @var Rediska_Connection
     */
    protected $_connection;

    /**
     * Count
     *
     * @var integer
     */
    protected $_count;

    /**
     * Current
     *
     * @var integer
     */
    protected $_current = 0;

    /**
     * Callback
     *
     * @var mixin
     */
    protected $_callback;

    /**
     * Constructor
     *
     * @param Rediska_Connection $connection
     * @param mixin $callback
     */
    public function __construct(Rediska_Connection $connection, $callback = null)
    {
        $this->_connection = $connection;
        $this->_callback   = $callback;
    }

    public function rewind()
    {
        if ($this->_count !== null && $this->_connection->isConnected()) {
            $this->_connection->disconnect();
        }
        $this->_count = null;
        $this->_current = 0;
    }

    public function valid()
    {
        if ($this->_count === null) {
            $reply = $this->_connection->readLine();

            if ($reply === null) {
                return false;
            }

            $type = substr($reply, 0, 1);
            $count = (integer)substr($reply, 1);

            if ($type == Rediska_Connection_Exec::REPLY_MULTY_BULK) {
                $this->_count = $count;
            } else {
                throw new Rediska_Connection_Exec_Exception($reply);
            }
        }

        return $this->_current < $this->_count;
    }

    public function current()
    {
        if ($this->_count === null || $this->_count == 0) {
            throw new Rediska_Connection_Exception('call valid before');
        }

        $response = Rediska_Connection_Exec::readResponseFromConnection($this->_connection);

        if ($this->_callback !== null) {
            $response = call_user_func($this->_callback, $response);
        }

        return $response;
    }

    public function key()
    {
        return $this->_current;
    }

    public function next()
    {
        $this->_current++;
    }

    public function count()
    {
        if ($this->valid()) {
            return $this->_count;
        } else {
            return 0;
        }
    }
}