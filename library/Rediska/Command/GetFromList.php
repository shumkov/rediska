<?php

/**
 * Return element of List by index at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetFromList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string  $key   Key name
     * @param integer $index Index
     * @return Rediska_Connection_Exec
     */
    public function create($key, $index)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('LINDEX',
                         $this->_rediska->getOption('namespace') . $key,
                         $index);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param string $response
     * @return mixed
     */
    public function parseResponse($response)
    {
        return $this->_rediska->getSerializer()->unserialize($response);
    }
}