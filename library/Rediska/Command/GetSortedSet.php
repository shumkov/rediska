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
     * @param string  $key                         Key name
     * @param integer $withScores[optional]        Return values with scores. For default is false.
     * @param integer $start[optional]             Start index. For default is begin of set.
     * @param integer $end[optional]               End index. For default is end of set.
     * @param boolean $revert[optional]            Revert elements (not used in sorting). For default is false
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created 
     * @return Rediska_Connection_Exec
     */
    public function create($key, $withScores = false, $start = 0, $end = -1, $revert = false, $responseIterator = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array($revert ? 'ZREVRANGE' : 'ZRANGE',
                         $this->_rediska->getOption('namespace') . $key,
                         $start,
                         $end);

        if ($withScores) {
            $command[] = 'WITHSCORES';
        }

        $exec = new Rediska_Connection_Exec($connection, $command);

        if ($responseIterator) {
            if ($withScores) {
                $responseIterator = 'Rediska_Command_GetSortedSet_WithScoresIterator';
            }
            $exec->setResponseIterator($responseIterator);
            $exec->setResponseCallback(array($this, 'parseIteratorResponse'));
        }

        return $exec;
    }

    /**
     * Parse response
     *
     * @param array $response
     * @return array
     */
    public function parseResponse($response)
    {
        if ($this->responseIterator) {
            return $response;
        } else {
            $values = $response;

            if ($this->withScores) {
                $values = Rediska_Command_Response_ValueAndScore::combine($this->_rediska, $values);
            } else {
                $values = array_map(array($this->_rediska->getSerializer(), 'unserialize'), $values);
            }

            return $values;
        }
    }

    public function parseIteratorResponse($response)
    {
        if ($this->withScores) {
            list($value, $score) = $response;

            $value = $this->getRediska()->getSerializer()->unserialize($value);

            return new Rediska_Command_Response_ValueAndScore(array('value' => $value, 'score' => $score));
        } else {
            return $this->getRediska()->getSerializer()->unserialize($response);
        }
    }
}