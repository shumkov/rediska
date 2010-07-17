<?php


/**
 * A proxy command for Rediska_PubSub_Context
 *
 * @author Yuriy Bogdanov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_Command extends Rediska_Command_Abstract
{
    public function _create()
    {
        // void
    }

    public function addCommandByConnection(Rediska_PubSub_Connection $connection, $command)
    {
        return $this->_addCommandByConnection($connection, $command);
    }

    public function getCommandsByConnections()
    {
        return $this->_commandsByConnections;
    }

    public function reset()
    {
        $this->_commandsByConnections = array();
    }

    public function readFromConnection(Rediska_PubSub_Connection $connection)
    {
        return Rediska_Command::readResponseFromConnection($connection);
    }
}