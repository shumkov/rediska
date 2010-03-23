<?php

/**
 * Ask the server to silently close the connection.
 * 
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Quit extends Rediska_Command_Abstract
{
    protected $_affectedConnections = array();
    
    protected function _create() 
    {
    	$command = 'QUIT';

    	$this->_affectedConnections = $this->_rediska->getConnections();

        foreach($this->_affectedConnections as $connection) {
        	$this->_addCommandByConnection($connection, $command);
        }
    }

	public function write()
	{
		parent::write();

		foreach($this->_affectedConnections as $connection) {
            $connection->disconnect();
        }

		return true;
	}

	public function read()
	{
		return true;
	}
}