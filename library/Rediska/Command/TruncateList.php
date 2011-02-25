<?php

/**
 * Trim the list at key to the specified range of elements
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
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
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('LTRIM',
                         $this->_rediska->getOption('namespace') . $key,
                         $start,
                         $end);
        
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