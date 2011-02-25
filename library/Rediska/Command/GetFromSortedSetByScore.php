<?php

/**
 * Get members from sorted set by min and max score
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetFromSortedSetByScore extends Rediska_Command_Abstract
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
     * @param string            $key        Key name
     * @param number            $min        Min score
     * @param number            $max        Max score
     * @param boolean[optional] $withScores Get with scores. For default is false
     * @param integer[optional] $limit      Limit. For default is no limit
     * @param integer[optional] $offset     Offset. For default is no offset
     * @return Rediska_Connection_Exec
     */
    public function create($key, $min, $max, $withScores = false, $limit = null, $offset = null)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('ZRANGEBYSCORE',
                         $this->_rediska->getOption('namespace') . $key,
                         $min,
                         $max);

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