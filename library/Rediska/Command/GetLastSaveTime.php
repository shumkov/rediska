<?php

/**
 * Return the UNIX time stamp of the last successfully saving of the dataset on disk
 *
 * @return array|integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetLastSaveTime extends Rediska_Command_Abstract
{
	protected $_connections = array();
	
    protected function _create() 
    {
    	$command = "LASTSAVE";
    	
        foreach($this->_rediska->getConnections() as $connection) {
        	$this->_connections[] = $connection->getAlias();
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponse($response)
    {
    	$timestamps = array();
    	$count = 0;
    	foreach($this->_connections as $connection) {
    		$timestamps[$connection] = $response[$count];
    		$count++;
    	}

    	if (count($timestamps) == 1) {
            $timestamps = array_values($timestamps);
            $timestamps = $timestamps[0];
        }

        return $timestamps;
    }
}