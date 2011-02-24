<?php

/**
 * Delete field from hash
 * 

 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DeleteFromHash extends Rediska_Command_Abstract
{
    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.3.10';

    /**
     * Create command
     *
     * @param string $key   Key name
     * @param mixed  $field Field
     * @return Rediska_Connection_Exec
     */
    public function create($key, $field)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('HDEL',
                         $this->_rediska->getOption('namespace') . $key,
                         $field);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param string $response
     * @return boolean
     */
    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}