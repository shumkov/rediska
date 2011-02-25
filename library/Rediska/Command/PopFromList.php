<?php

/**
 * Return and remove the last element of the List at key 
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_PopFromList extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string           $name       Key name
     * @param string[optional] $pushToName If not null - push value to another key.
     * @return Rediska_Connection_Exec
     */
    public function create($key, $pushToKey = null)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        if (is_null($pushToKey)) {
            $command = array('RPOP',
                             $this->_rediska->getOption('namespace') . $key);
        } else {
            $toConnection = $this->_rediska->getConnectionByKeyName($pushToKey);

            if ($connection->getAlias() == $toConnection->getAlias()) {
                $this->_throwExceptionIfNotSupported('1.1');

                $command = array('RPOPLPUSH',
                                 $this->_rediska->getOption('namespace') . $key,
                                 $this->_rediska->getOption('namespace') . $pushToKey);
            } else {
                $this->setAtomic(false);

                $command = array('RPOP',
                                 $this->_rediska->getOption('namespace') . $key);
            }
        }

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
        if (!$this->isAtomic()) {
            $value = $this->_rediska->getSerializer()->unserialize($response);

            $this->_rediska->prependToList($this->pushToKey, $value);

            return $value;
        } else {
            return $this->_rediska->getSerializer()->unserialize($response);
        }
    }
}