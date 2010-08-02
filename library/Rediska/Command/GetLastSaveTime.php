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
    
    public function create() 
    {
        $command = "LASTSAVE";
        $commands = array();
        foreach($this->_rediska->getConnections() as $connection) {
            $this->_connections[] = $connection->getAlias();
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    public function parseResponses($responses)
    {
        $timestamps = array();
        $count = 0;
        foreach($this->_connections as $connection) {
            $timestamps[$connection] = $responses[$count];
            $count++;
        }

        if (count($timestamps) == 1) {
            $timestamps = array_values($timestamps);
            $timestamps = $timestamps[0];
        }

        return $timestamps;
    }
}