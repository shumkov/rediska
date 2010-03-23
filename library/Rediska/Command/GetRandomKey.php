<?php

/**
 * Return a random key from the key space
 * 
 * @return null|string
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetRandomKey extends Rediska_Command_Abstract
{
    protected function _create()
    {
        $connections = $this->_rediska->getConnections();
        $index = rand(0, count($connections) - 1);
        $connection = $connections[$index];

        $command = "RANDOMKEY";
        
        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
        $reply = $response[0];

        if ($reply == '') {
            return null;
        } else {
            if (strpos($reply, $this->_rediska->getOption('namespace')) === 0) {
                $reply = substr($reply, strlen($this->_rediska->getOption('namespace')));
            }

            return $reply;
        }
    }
}