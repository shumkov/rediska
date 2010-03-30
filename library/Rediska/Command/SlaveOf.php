<?php

/**
 * Change the replication settings of a slave on the fly
 * 
 * @throws Rediska_Command_Exception
 * @param string|Rediska_Connection|false $aliasOrConnection Server alias, Rediska_Connection object or false if not slave
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SlaveOf extends Rediska_Command_Abstract
{
    protected function _create($aliasOrConnection)
    {
        if ($aliasOrConnection === false) {
            $host = 'no';
            $port = 'one';
        } else {
            if ($aliasOrConnection instanceof Rediska_Connection) {
                $connection = $aliasOrConnection;
            } else {
                $alias = $aliasOrConnection;
                $connection = $this->_rediska->getConnectionByAlias($alias);
            }

            $host = $connection->getHost();
            $port = $connection->getPort();
        }

        $command = "SLAVEOF $host $port";

        foreach($this->_rediska->getConnections() as $connection) {
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponse($response)
    {
        return true;
    }
}