<?php

/**
 * Get hash fields and values
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name  Key name
 * @param integer $start Start index
 * @param integer $end   End index
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetHash extends Rediska_Command_Abstract
{
    protected $_version = '1.3.10';

    public function create($name)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = array('HGETALL', $this->_rediska->getOption('namespace') . $name);

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponse($response)
    {
        $isField = true;
        $result = array();
        foreach($response as $fieldOrValue) {
            if ($isField) {
                $field = $fieldOrValue;
            } else {
                $result[$field] = $this->_rediska->getSerializer()->unserialize($fieldOrValue);
            }

            $isField = !$isField;
        }

        return $result;
    }
}