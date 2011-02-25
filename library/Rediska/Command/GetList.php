<?php


/**
 * Get List by key
 * 
 * @throws Rediska_Command_Exception

 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string  $key                         Key name
     * @param integer $start[optional]             Start index. For default is begin of list
     * @param integer $end[optional]               End index. For default is end of list
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created 
     * @return Rediska_Connection_Exec
     */
    public function create($key, $start = 0, $end = -1, $responseIterator = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('LRANGE',
                         $this->_rediska->getOption('namespace') . $key,
                         $start,
                         $end);

        $exec = new Rediska_Connection_Exec($connection, $command);
        
        if ($responseIterator) {
            $exec->setResponseIterator(true);
            $exec->setResponseCallback(array($this->getRediska()->getSerializer(), 'unserialize'));
        }
        
        return $exec;
    }

    /**
     * Parse responses
     *
     * @param array $response
     * @return array
     */
    public function parseResponse($response)
    {
        return array_map(array($this->_rediska->getSerializer(), 'unserialize'), $response);
    }
}