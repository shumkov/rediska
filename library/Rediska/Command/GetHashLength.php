<?php

/**
 * Return the number of fields in hash
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetHashLength extends Rediska_Command_Abstract
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
     * @param string $key Key name
     * @return Rediska_Connection_Exec
     */
    public function create($key) 
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('HLEN',
                         $this->_rediska->getOption('namespace') . $key);

        return new Rediska_Connection_Exec($connection, $command);
    }
}