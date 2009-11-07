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
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.1
 * @link http://code.google.com/p/rediska
 * @link http://github.com/RJ/ketama
 * @licence http://opensource.org/licenses/gpl-3.0.html
 */
class Rediska_KeyDistributor_Ketama implements Rediska_KeyDistributor_Interface
{
	public function __construct()
	{
		throw new Rediska_KeyDistributor_Exception('Not implemented yet!');
	}

	/**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#addConnection
     */
    public function addConnection($connectionString, $weight);

    /**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#removeConnection
     */
    public function removeConnection($connectionString);

    /**
     * (non-PHPdoc)
     * @see Rediska_KeyDistributor_Interface#getConnectionByKeyName
     */
    public function getConnectionByKeyName($name);
}