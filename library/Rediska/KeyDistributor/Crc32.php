<?php

/**
 * @see Rediska_Connection
 */
require_once 'Rediska/Connection.php';

/**
 * @see Rediska_KeyDistributor_Interface
 */
require_once 'Rediska/KeyDistributor/Interface.php';

/**
 * @see Rediska_KeyDistributor_Exception
 */
require_once 'Rediska/KeyDistributor/Exception.php';

/**
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_KeyDistributor_Crc32 implements Rediska_KeyDistributor_Interface
{
    protected $_connections = array();

    protected $_connectionCount = 0;

    protected $_connectionPositions = array();

    protected $_connectionPositionCount = 0;

	/**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#addConnection
     */
	public function addConnection($connectionString, $weight = Rediska_Connection::DEFAULT_WEIGHT)
	{
        if (in_array($connectionString, $this->_connections)) {
            throw new Rediska_KeyDistributor_Exception("Connection '$connectionString' already exists.");
        }

        $this->_connections[] = $connectionString;
        $this->_connectionCount++;

        // add connection positions
        for ($index = 0; $index < $weight; $index++) {
            $this->_connectionPositions[] = $connectionString;
            $this->_connectionPositionCount++;
        }

        return $this;
	}

	/**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#removeConnection
     */
    public function removeConnection($connectionString)
    {
        if (!in_array($connectionString, $this->_connections)) {
            throw new Rediska_KeyDistributor_Exception("Connection '$connectionString' does not exist.");
        }
        
        $index = array_search($connectionString, $this->_connections);
        unset($this->_connections[$index]);
        $this->_connectionCount--;

        // remove connection positions
        $connectionPositions = $this->_connectionPositions;
        $this->_connectionPositions = array();
        $this->_connectionPositionCount = 0;
    	foreach($connectionPositions as $connection) {
    		if ($connection != $connectionString) {
    			$this->_connectionPositions[] = $connection;
    			$this->_connectionPositionCount++;
    		}
    	}

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#getConnectionByKeyName
     */
	public function getConnectionByKeyName($name)
	{
	    if (empty($this->_connections)) {
            throw new Rediska_KeyDistributor_Exception("No connection exists.");
        }

		if ($this->_connectionCount == 1) {
			return $this->_connections[0];
		}

		$index = abs(crc32($name) % $this->_connectionPositionCount);

		return $this->_connectionPositions[$index];
	}
}