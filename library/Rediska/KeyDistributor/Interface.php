<?php

/**
 * Rediska key distributor interface
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
interface Rediska_KeyDistributor_Interface
{
	/**
	 * Add connection
	 * 
	 * @param string $connectionString Connection string: '127.0.0.1:6379'
	 * @param integer $weight Connection weight
	 * @return $this
	 */
    public function addConnection($connectionString, $weight = Rediska_Connection::DEFAULT_WEIGHT);

    /**
     * Remove connection
     * @param string $connectionString Connection string: '127.0.0.1:6379'
     * @return $this
     */
    public function removeConnection($connectionString);

    /**
     * Get connection by key name
     * @param string $name Key name
     * @return string Connection string
     */
    public function getConnectionByKeyName($name);
}