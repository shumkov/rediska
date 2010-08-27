<?php

/**
 * Append value to the head of List
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_PrependToList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @return Rediska_Connection_Exec
     */
    public function create($key, $member)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $member = $this->_rediska->getSerializer()->serialize($member);

        $command = "LPUSH {$this->_rediska->getOption('namespace')}$key " . strlen($member) . Rediska::EOL . $member;

        return new Rediska_Connection_Exec($connection, $command);
    }
}