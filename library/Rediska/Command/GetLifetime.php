<?php

/**
 * Get key lifetime
 * 
 * @param string $name
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetLifetime extends Rediska_Command_Abstract
{
    public function create($name)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);
        $command = "TTL {$this->_rediska->getOption('namespace')}$name";

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        if ($response == -1) {
            $response = null;
        }

        return $response;
    }
}