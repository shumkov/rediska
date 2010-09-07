<?php

/**
 * Get all the members of the Sorted Set value at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSortedSet extends Rediska_Command_Abstract
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
     * @param string  $key                  Key name
     * @param integer $withScores[optional] Return values with scores. For default is false.
     * @param integer $start[optional]      Start index. For default is begin of set.
     * @param integer $end[optional]        End index. For default is end of set.
     * @param boolean $revert[optional]     Revert elements (not used in sorting). For default is false
     * @return Rediska_Connection_Exec
     */
    public function create($key, $withScores = false, $start = 0, $end = -1, $revert = false)
    {
        if (!is_integer($start)) {
            throw new Rediska_Command_Exception("Start must be integer");
        }
        if (!is_integer($end)) {
            throw new Rediska_Command_Exception("End must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array($revert ? 'ZREVRANGE' : 'ZRANGE',
                         "{$this->_rediska->getOption('namespace')}$key",
                         $start,
                         $end);

        if ($withScores) {
            $command[] = 'WITHSCORES';
        }

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param array $response
     * @return array
     */
    public function parseResponse($response)
    {
        $values = $response;

        if ($this->withScores) {
            $values = Rediska_Command_Response_ValueAndScore::combine($this->_rediska, $values);
        } else {
            $values = array_map(array($this->_rediska->getSerializer(), 'unserialize'), $values);
        }

        return $values;
    }
}