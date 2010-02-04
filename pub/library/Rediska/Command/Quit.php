<?php

/**
 * Ask the server to silently close the connection.
 * 
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.3.0
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Quit extends Rediska_Command_Abstract
{
    protected function _create() 
    {
    	$command = 'QUIT';
        foreach($this->_rediska->getConnections() as $connection) {
        	$this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponse($response)
    {
    	return true;
    }
}