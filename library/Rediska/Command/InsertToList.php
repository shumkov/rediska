<?php

/**
 * Insert a new value as the element before or after the reference value
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_InsertToList extends Rediska_Command_Abstract
{
    protected $_version = '2.1.1';

    const BEFORE = 'BEFORE';
    const AFTER  = 'AFTER';

    /**
     * Create command
     *
     * @param string  $key            Key name
     * @param string  $position       BEFORE or AFTER
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return Rediska_Connection_Exec
     */
    public function create($key, $position, $referenceValue, $value)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $value = $this->_rediska->getSerializer()->serialize($value);

        $referenceValue = $this->_rediska->getSerializer()->serialize($referenceValue);

        $command = array('LINSERT',
                         $this->_rediska->getOption('namespace') . $key,
                         $position,
                         $referenceValue,
                         $value);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param integer $response
     * @return integer|boolean
     */
    public function parseResponse($response)
    {
        if ($response == -1) {
            $response = false;
        }

        return $response;
    }
}