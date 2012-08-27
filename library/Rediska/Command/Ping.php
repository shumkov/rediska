<?php
/**
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
/**
 * This command is often used to test if a connection is still alive, or to
 * measure latency.
 *
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Ping extends Rediska_Command_Abstract
{
    protected $_connections = array();

    /**
     * Create
     *
     * @return array
     */
    public function create()
    {
        $this->_throwExceptionIfNotSupported('Ping', '1.0');
        $command = array('PING');
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
        if(count($responses) == 1){
            $responses = array_shift($responses);
        }
        return $responses;
    }
}
