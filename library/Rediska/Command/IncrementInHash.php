<?php

/**
 * Increment field value in hash
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_IncrementInHash extends Rediska_Command_Abstract
{
    /**
     * Supported version
     *
     * @var string
     */
    protected $_version = '1.3.10';

    /**
     * Create command
     *
     * @param string $key              Key name
     * @param mixed  $field            Field
     * @param number $amount[optional] Increment amount. One for default
     * @return Rediska_Connection_Exec
     */
    public function create($key, $field, $amount = 1)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('HINCRBY',
                         $this->_rediska->getOption('namespace') . $key,
                         $field,
                         $amount);

        return new Rediska_Connection_Exec($connection, $command);
    }
}