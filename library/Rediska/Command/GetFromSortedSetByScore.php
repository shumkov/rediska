<?php

/**
 * Get members from sorted set by min and max score
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name       Key name
 * @param number  $min        Min score
 * @param number  $max        Max score
 * @param boolean $withScores Get with scores
 * @param integer $limit      Limit
 * @param integer $offset     Offset
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetFromSortedSetByScore extends Rediska_Command_Abstract
{
    protected $_version = '1.1';

    public function create($name, $min, $max, $withScores = false, $limit = null, $offset = null)
    {
        if (!is_null($limit) && !is_integer($limit)) {
            throw new Rediska_Command_Exception("Limit must be integer");
        }

        if (!is_null($offset) && !is_integer($offset)) {
            throw new Rediska_Command_Exception("Offset must be integer");
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = array('ZRANGEBYSCORE', "{$this->_rediska->getOption('namespace')}$name", $min, $max);

        if (!is_null($limit)) {
            if (is_null($offset)) {
                $offset = 0;
            }
            $command[] = 'LIMIT';
            $command[] = $offset;
            $command[] = $limit;
        }
        
        if ($withScores) {
            $this->_throwExceptionIfNotSupported('1.3.4');

            $command[] = 'WITHSCORES';
        }

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        $values = $response;

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