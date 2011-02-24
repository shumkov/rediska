<?php

/**
 * Abstract class for union and intersection of sorted sets 
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_Command_CompareSortedSets extends Rediska_Command_Abstract
{
    const SUM = 'sum';
    const MAX = 'max';
    const MIN = 'min';

    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.3.12';

    protected $_command;

    protected $_storeConnection;
    protected $_keys   = array();
    protected $_weights = array();

    /**
     * Create command
     *
     * @param array  $keys       Array of key names or associative array with weights
     * @param string $storeKey   Result sorted set key name
     * @param string $aggregation Aggregation method: SUM (for default), MIN, MAX.
     * @return Rediska_Connection_Exec
     */
    public function create(array $keys, $storeKey, $aggregation = self::SUM)
    {
        if (empty($keys)) {
            throw new Rediska_Command_Exception('You must specify sorted sets');
        }

        // With weights?
        $withWeights = false;
        foreach($keys as $keyOrIndex => $keyOrWeight) {
            if (is_string($keyOrIndex)) {
                $this->_weights = $keys;
                $keys = array_keys($keys);
                $withWeights = true;
                break;
            }
        }

        $connections = array();
        $keysByConnections = array();
        foreach ($keys as $key) {
            $connection = $this->_rediska->getConnectionByKeyName($key);
            $connectionAlias = $connection->getAlias();
            if (!array_key_exists($connectionAlias, $connections)) {
                $connections[$connectionAlias] = $connection;
                $keysByConnections[$connectionAlias] = array();
            }
            $keysByConnections[$connectionAlias][] = $key;
        }

        // If only one connection, compare by redis
        if (count($connections) == 1) {
            $connectionValues = array_values($connections);
            $connection = $connectionValues[0];
            $storeConnection = $this->_rediska->getConnectionByKeyName($storeKey);

            if ($storeConnection === $connection) {
                $command = array($this->_command,
                                 $this->_rediska->getOption('namespace') . $storeKey,
                                 count($keys));

                foreach($keys as $key) {
                    $command[] = $this->_rediska->getOption('namespace') . $key;
                }
    
                if ($withWeights) {
                    $command[] = 'WEIGHTS';
                    $command = array_merge($command, $this->_weights);
                }

                if (strtolower($aggregation) != self::SUM) {
                    $command[] = 'AGGREGATE';
                    $command[] = strtoupper($aggregation);
                }

                return new Rediska_Connection_Exec($connection, $command);
            }
        }

        // Compare by hand
        
        // Set default weights
        if (!$withWeights) {
            $this->_weights = array_fill_keys($keys, 1);
        }

        $this->setAtomic(false);
        $commands = array();
        foreach($keysByConnections as $connectionAlias => $keys) {
            foreach($keys as $key) {
                $this->_keys[] = $key;
                $command = array('ZRANGE',
                                 $this->_rediska->getOption('namespace') . $key,
                                 0,
                                 -1,
                                 'WITHSCORES');
                $commands[] = new Rediska_Connection_Exec($connections[$connectionAlias], $command);
            }
        }

        return $commands;
    }

    /**
     * Parse responses
     *
     * @param array $responses
     * @return integer
     */
    public function parseResponses($responses)
    {
        if ($this->isAtomic()) {
           return $responses[0];
        } else {
            $sets = array();
            $valuesWithScores = array();
            foreach ($this->_keys as $key) {
                $sets[$key] = array();
                $response = current($responses);
                next($responses);
                $isValue = true;
                foreach ($response as $valueOrScore) {
                    if ($isValue) {
                        $value = $valueOrScore;
                        $sets[$key][] = $value;
                        if (!isset($valuesWithScores[$value])) {
                            $valuesWithScores[$value] = array();
                        }
                    } else {
                        $score = $valueOrScore;
                        $valuesWithScores[$value][] = $score * $this->_weights[$key];
                    }
                    $isValue = !$isValue;
                }
            }

            $aggregation = strtolower($this->aggregation);
            
            $pipeline = $this->_rediska->pipeline();

            $count = 0;
            foreach($this->_compareSets($sets) as $value) {
                $scores = $valuesWithScores[$value];
                switch ($aggregation) {
                    case self::SUM:
                        $score = array_sum($scores);
                        break;
                    case self::MIN:
                        $score = min($scores);
                        break;
                    case self::MAX:
                        $score = max($scores);
                        break;
                    default:
                        throw new Rediska_Command_Exception('Unknown aggregation method ' . $this->aggregation);
                }

                $value = $this->_rediska->getSerializer()->unserialize($value);

                $pipeline->addToSortedSet($this->storeKey, $value, $score);

                $count++;
            }

            $pipeline->execute();

            return $count;
        }
    }

    abstract protected function _compareSets($sets);
}