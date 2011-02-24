<?php

/**
 * Provide information and statistics about the server
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Info extends Rediska_Command_Abstract
{
    protected $_connections = array();

    /**
     * Create
     *
     * @return array
     */
    public function create() 
    {
        $command = array('INFO');
        $commands = array();
        foreach($this->_rediska->getConnections() as $connection) {
            $this->_connections[] = $connection->getAlias();
            $commands[] = new Rediska_Connection_Exec($connection, $command);
        }

        return $commands;
    }

    /**
     * Parse response
     * 
     * @param array $responses
     * @return array
     */
    public function parseResponses($responses)
    {
        $info = array();
        $count = 0;
        foreach($this->_connections as $connection) {
            $info[$connection] = array();
            
            foreach (explode(Rediska::EOL, $responses[$count]) as $param) {
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