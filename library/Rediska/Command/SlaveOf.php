<?php

/**
 * Change the replication settings of a slave on the fly
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_SlaveOf extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string|Rediska_Connection|false $aliasOrConnection Server alias, Rediska_Connection object or false if not slave
     * @return Rediska_Connection_Exec
     */
    public function create($aliasOrConnection)
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

        $command = array('SLAVEOF',
                         $host,
                         $port);
        $commands = array();
        foreach($this->_rediska->getConnections() as $connection) {
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }
        
        return $commands;
    }

    /**
     * Parse responses
     *
     * @param array $responses
     * @return boolean
     */
    public function parseResponses($responses)
    {
        return true;
    }
}