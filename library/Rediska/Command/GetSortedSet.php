<?php

/**
 * Get all the members of the Sorted Set value at key
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name        Key name
 * @param integer $withScores  Return values with scores
 * @param integer $start       Start index
 * @param integer $end         End index
 * @param boolean $revert      Revert elements (not used in sorting)
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSortedSet extends Rediska_Command_Abstract
{
    protected $_version = '1.1';

    protected function _create($name, $withScores = false, $start = 0, $end = -1, $revert = false)
    {
        if (!is_integer($start)) {
            throw new Rediska_Command_Exception("Start must be integer");
        }
        if (!is_integer($end)) {
            throw new Rediska_Command_Exception("End must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = array($revert ? 'ZREVRANGE' : 'ZRANGE',
                         "{$this->_rediska->getOption('namespace')}$name",
                         $start,
                         $end);

        if ($withScores) {
        	$command[] = 'WITHSCORES';
        }

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        $values = $responses[0];

        if ($this->withScores) {
        	$values = Rediska_Command_Response_ValueAndScore::combine($this->_rediska, $values);
        } else {
            foreach($values as &$value) {
        	   $value = $this->_rediska->getSerializer()->unserialize($value);
            }
        }

        return $values;
    }
}