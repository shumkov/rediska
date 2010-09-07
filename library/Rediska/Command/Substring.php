<?php

/**
 * Return a subset of the string from offset start to offset end (both offsets are inclusive)
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Substring extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string            $key   Key name
     * @param integer           $start Start
     * @param integer[optional] $end   End. If end is omitted, the substring starting from $start until the end of the string will be returned. For default end of string
     * @return Rediska_Connection_Exec
     */
    public function create($key, $start, $end = -1)
    {
        $command = array('SUBSTR', $this->_rediska->getOption('namespace') . $key, $start, $end);

        $connection = $this->_rediska->getConnectionByKeyName($key);

        return new Rediska_Connection_Exec($connection, $command);
    }
}