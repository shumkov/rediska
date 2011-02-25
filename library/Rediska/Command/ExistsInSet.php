<?php

/**
 * Test if the specified value is a member of the Set at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_ExistsInSet extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string $key    Key value
     * @param mixed  $member Member
     * @return Rediska_Connection_Exec
     */
    public function create($key, $member)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $member = $this->_rediska->getSerializer()->serialize($member);

        $command = array('SISMEMBER',
                         $this->_rediska->getOption('namespace') . $key,
                         $member);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     * 
     * @param integer $response
     * @return boolean
     */
    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}