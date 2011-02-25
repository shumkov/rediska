<?php

/**
 * Get hash fields and values
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetHash extends Rediska_Command_Abstract
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

        $command = array('HGETALL',
                         $this->_rediska->getOption('namespace') . $key);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param array $response
     * @return array
     */
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