<?php

/**
 * Test if field is present in hash
 * 
 * @param string $name  Key name
 * @prarm mixin  $field Field
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_ExistsInHash extends Rediska_Command_Abstract
{
    protected $_version = '1.3.10';
    
    public function create($name, $field)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = array('HEXISTS', $this->_rediska->getOption('namespace') . $name, $field);

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}