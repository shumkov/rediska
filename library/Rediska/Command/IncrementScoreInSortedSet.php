<?php

/**
 * Increment score of sorted set element
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_IncrementScoreInSortedSet extends Rediska_Command_Abstract
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
     * @param string        $key   Key name
     * @param mixed         $value Member
     * @param integer|float $score Score to increment
     * @return Rediska_Connection_Exec
     */
    public function create($key, $value, $score)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = array('ZINCRBY',
                         $this->_rediska->getOption('namespace') . $key,
                         $score,
                         $value);

        return new Rediska_Connection_Exec($connection, $command);
    }
}