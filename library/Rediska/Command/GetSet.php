<?php

/**
 * Return all the members of the Set value at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSet extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string  $key Key name
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created 
     * @return Rediska_Connection_Exec
     */
    public function create($key, $responseIterator = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('SMEMBERS',
                         $this->_rediska->getOption('namespace') . $key);

        $exec = new Rediska_Connection_Exec($connection, $command);
        
        if ($responseIterator) {
            $exec->setResponseIterator(true);
            $exec->setResponseCallback(array($this->getRediska()->getSerializer(), 'unserialize'));
        }
        
        return $exec;
    }

    /**
     * Parse response
     *
     * @param array $response
     * @return array
     */
    public function parseResponse($response)
    {
        return array_map(array($this->_rediska->getSerializer(), 'unserialize'), $response);
    }
}