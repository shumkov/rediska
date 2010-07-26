<?php

/**
 * Trim the list at key to the specified range of elements
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name  Key name
 * @param integer $start Start index
 * @param integer $end   End index
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_TruncateList extends Rediska_Command_Abstract
{
    public function create($name, $start, $end)
    {
        if (!is_integer($start)) {
            throw new Rediska_Command_Exception("Start must be integer");
        }
        if (!is_integer($end)) {
            throw new Rediska_Command_Exception("End must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "LTRIM {$this->_rediska->getOption('namespace')}$name $start $end";
        
        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}