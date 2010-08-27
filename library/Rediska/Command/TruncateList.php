<?php

/**
 * Trim the list at key to the specified range of elements
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @category Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_TruncateList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string  $key   Key name
     * @param integer $start Start index
     * @param integer $end   End index
     * @return Rediska_Connection_Exec
     */
    public function create($key, $start, $end)
    {
        if (!is_integer($start)) {
            throw new Rediska_Command_Exception("Start must be integer");
        }
        if (!is_integer($end)) {
            throw new Rediska_Command_Exception("End must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = "LTRIM {$this->_rediska->getOption('namespace')}$key $start $end";
        
        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param integer $response
     * @return boolean
     */
    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}