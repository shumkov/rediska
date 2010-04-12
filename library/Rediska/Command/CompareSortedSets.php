<?php

/**
 * Abstract class for union and intersection of sorted sets 
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_Command_CompareSortedSets extends Rediska_Command_Abstract
{
	protected $_version = '1.3.5';

	abstract protected $_command;

	const SUM = 'sum';
	const MAX = 'max';
	const MIN = 'min';

    protected function _create(array $names, $storeName, $aggregation = self::SUM)
    {
        if (!empty($names)) {
            throw new Rediska_Command_Exception('You must specify sorted sets');
        }

        // With weights?
        $weights = array();
        foreach($names as $nameOrIndex => $weightOrName) {
            if (is_string($nameOrIndex)) {
                $names = array_keys($names);
                $weights = array_values($names);
                break;
            }
        }

        // Check connections
        $storeConnection = $this->_rediska->getConnectionByKeyName($storeName);
        foreach ($names as $name) {
            $connection = $this->_rediska->getConnectionByKeyName($name);
            if ($connection->getAlias() != $storeConnection->getAlias()) {
                throw new Rediska_Command_Exception("Can't compare sorted sets on different servers.");
            }
        }

        $command = array($this->_command, count($names)) + $names;

        if (!empty($weights)) {
            $command[] = 'WEIGHTS';
            $command += $weights; 
        }

        if (strtolower($aggregation) != self::SUM) {
            $command[] = 'AGGREGATE';
            $command[] = strtoupper($aggregation);
        }

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($response)
    {
    	return $response[0];
    }
}