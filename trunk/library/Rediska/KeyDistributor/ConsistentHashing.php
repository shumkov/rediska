<?php

/**
 * @see Rediska_KeyDistributor_Interface
 */
require_once 'Rediska/KeyDistributor/Interface.php';

/**
 * @see Rediska_KeyDistributor_Exception
 */
require_once 'Rediska/KeyDistributor/Exception.php';

/**
 * @package Rediska
 * @version 0.4.1
 * @author Paul Annesley
 * @link http://github.com/pda/flexihash
 * @link http://code.google.com/p/rediska
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Rediska_KeyDistributor_ConsistentHashing implements Rediska_KeyDistributor_Interface
{
	/**
     * The number of positions to hash each connection to
     *
     * @var integer
     */
	protected $_replicas = 64;

    /**
     * Internal counter for current number of connections
     * 
     * @var integer
     */
    protected $_connectionCount = 0;

    /**
     * Internal map of positions (hash outputs) to connections
     * 
     * @var array
     */
    protected $_positionToConnection = array();

    /**
     * Internal map of connections to lists of positions that connection is hashed to
     * 
     * @var array
     */
    protected $_connectionToPositions = array();

    /**
     * Whether the internal map of positions to connections is already sorted
     * 
     * @var boolean
     */
    protected $_positionToConnectionSorted = false;

    /**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#addConnection
     */
    public function addConnection($connectionString, $weight = Rediska_Connection::DEFAULT_WEIGHT)
    {
        if (isset($this->_connectionToPositions[$connectionString])) {
            throw new Rediska_KeyDistributor_Exception("Connection '$connectionString' already exists.");
        }

        $this->_connectionToPositions[$connectionString] = array();

        // hash the connection into multiple positions
        for ($i = 0; $i < round($this->_replicas * $weight); $i++) {
            $position = crc32($connectionString . $i);
            $this->_positionToConnection[$position] = $connectionString; // lookup
            $this->_connectionToPositions[$connectionString] []= $position; // connection removal
        }

        $this->_positionToConnectionSorted = false;
        $this->_connectionCount++;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#removeConnection
     */
    public function removeConnection($connectionString)
    {
        if (!isset($this->_connectionToPositions[$connectionString])) {
            throw new Rediska_KeyDistributor_Exception("Connection '$connectionString' does not exist.");
        }

        foreach ($this->_connectionToPositions[$connectionString] as $position) {
            unset($this->_positionToConnection[$position]);
        }

        unset($this->_connectionToPositions[$connectionString]);

        $this->_connectionCount--;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#getConnectionByKeyName
     */
    public function getConnectionByKeyName($name)
    {
        $connections = $this->getConnectionsByKeyName($name, 1);
        if (empty($connections)) {
        	throw new Rediska_KeyDistributor_Exception('No connections exist');
        }
        return $connections[0];
    }

    /**
     * Get a list of connections by key name
     *
     * @param string $name Key name
     * @param int $requestedCount The length of the list to return
     * @return array List of connections
     */
    public function getConnectionsByKeyName($name, $requestedCount)
    {
        if (!$requestedCount) {
            throw new Rediska_KeyDistributor_Exception('Invalid count requested');
        }

        // handle no targets
        if (empty($this->_positionToConnection)) {
            return array();
        }

        // optimize single connection
        if ($this->_connectionCount == 1)
            return array_unique(array_values($this->_positionToConnection));

        // hash key to a position
        $keyPosition = crc32($name);

        $results = array();
        $collect = false;

        $this->_sortPositionConnections();

        // search values above the keyPosition
        foreach ($this->_positionToConnection as $key => $value)
        {
            // start collecting connections after passing key position
            if (!$collect && $key > $keyPosition)
            {
                $collect = true;
            }

            // only collect the first instance of any connection
            if ($collect && !in_array($value, $results))
            {
                $results[]= $value;
            }

            // return when enough results, or list exhausted
            if (count($results) == $requestedCount || count($results) == $this->_connectionCount)
            {
                return $results;
            }
        }

        // loop to start - search values below the keyPosition
        foreach ($this->_positionToConnection as $key => $value)
        {
            if (!in_array($value, $results))
            {
                $results []= $value;
            }

            // return when enough results, or list exhausted
            if (count($results) == $requestedCount || count($results) == $this->_connectionCount)
            {
                return $results;
            }
        }

        // return results after iterating through both "parts"
        return $results;
    }

    /**
     * Sorts the internal mapping (positions to connections) by position
     */
    protected function _sortPositionConnections()
    {
        // sort by key (position) if not already
        if (!$this->_positionToConnectionSorted) {
            ksort($this->_positionToConnection, SORT_REGULAR);
            $this->_positionToConnectionSorted = true;
        }
    }
}