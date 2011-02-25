<?php

/**
 * Get random element from the Set value at key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetRandomFromSet extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string            $key  Key name
     * @param boolean[optional] $pop  If true - pop value from the set. For default is false
     * @return Rediska_Connection_Exec
     */
    public function create($key, $pop = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        if ($pop) {
            $command = array('SPOP');
        } else {
            $command = array('SRANDMEMBER');
        }

        $command[] = $this->_rediska->getOption('namespace') . $key;

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param string $response
     * @return mixed
     */
    public function parseResponse($response)
    {
        return $this->_rediska->getSerializer()->unserialize($response);
    }
}