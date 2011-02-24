<?php

/**
 * Add member to sorted set
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_AddToSortedSet extends Rediska_Command_Abstract
{
    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.1';

    /**
     * Create command
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @param number $score  Score of member
     * @return Rediska_Connection_Exec
     */
    public function create($key, $member, $score)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $member = $this->_rediska->getSerializer()->serialize($member);

        $command = array('ZADD',
                         $this->_rediska->getOption('namespace') . $key,
                         $score,
                         $member);

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