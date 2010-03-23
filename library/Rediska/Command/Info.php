<?php

/**
 * Provide information and statistics about the server
 * 
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Info extends Rediska_Command_Abstract
{
	protected $_connections = array();

    protected function _create() 
    {
    	$command = 'INFO';
        $info = array();
        foreach($this->_rediska->getConnections() as $connection) {
        	$this->_connections[] = $connection->getAlias();
        	$this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponse($response)
    {
    	$info = array();
    	$count = 0;
    	foreach($this->_connections as $connection) {
    		$info[$connection] = array();
    		
            foreach (explode(Rediska::EOL, $response[$count]) as $param) {
                if (!$param) {
                    continue;
                }

                list($name, $stringValue) = explode(':', $param, 2);

                if (strpos($stringValue, '.') !== false) {
                    $value = (float)$stringValue;
                } else {
                    $value = (integer)$stringValue;
                }

                if ((string)$value != $stringValue) {
                    $value = $stringValue;
                }

                $info[$connection][$name] = $value;
            }

    		$count++;
    	}

        if (count($info) == 1) {
            $info = array_values($info);
            $info = $info[0];
        }

        return $info;
    }
}