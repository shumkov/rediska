<?php

/**
 * Append value to the end of List
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_AppendToList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string $key     Key name
     * @param mixin  $value   Element value
     * @return Rediska_Connection_Exec
     */
    public function create($key, $value)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $command = "RPUSH {$this->_rediska->getOption('namespace')}$key " . strlen($value) . Rediska::EOL . $value;

        return new Rediska_Connection_Exec($connection, $command);
    }
}