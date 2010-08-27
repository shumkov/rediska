<?php


/**
 * Get List by key
 * 
 * @throws Rediska_Command_Exception

 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string  $key             Key name
     * @param integer $start[optional] Start index. For default is begin of list
     * @param integer $end[optional]   End index. For default is end of list
     * @return Rediska_Connection_Exec
     */
    public function create($key, $start = 0, $end = -1)
    {
        if (!is_integer($start)) {
            throw new Rediska_Command_Exception("Start must be integer");
        }
        if (!is_integer($end)) {
            throw new Rediska_Command_Exception("End must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = "LRANGE {$this->_rediska->getOption('namespace')}$key $start $end";

        return new Rediska_Connection_Exec($connection, $command);
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