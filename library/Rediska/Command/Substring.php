<?php

/**
 * Return a subset of the string from offset start to offset end (both offsets are inclusive)
 * 
 * @param $name          Key name
 * @paran $start         Start
 * @param $end[optional] End. If end is omitted, the substring starting from $start until the end of the string will be returned.
 * @return string
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Substring extends Rediska_Command_Abstract
{
    public function create($name, $start, $end = null)
    {
        if ($end === null) {
            $end = -1;
        }

        $command = array('SUBSTR', $this->_rediska->getOption('namespace') . $name, $start, $end);

        $connection = $this->_rediska->getConnectionByKeyName($name);

        return new Rediska_Connection_Exec($connection, $command);
    }
}