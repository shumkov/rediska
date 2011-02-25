<?php

/**
 * Get key lifetime
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetLifetime extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string $key Key name
     * @return Rediska_Connection_Exec
     */
    public function create($key)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('TTL',
                         $this->_rediska->getOption('namespace') . $key);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param string $response
     * @return string|null
     */
    public function parseResponse($response)
    {
        if ($response == -1) {
            $response = null;
        }

        return $response;
    }
}