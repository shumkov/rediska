<?php

/**
 * Return and remove the last element of the List at key 
 * 
 * @param string $name       Key name
 * @param string $pushToName Push value to another key
 * @return mixin
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_PopFromListBlocking extends Rediska_Command_Abstract
{
    public function create($name) 
    {
    	$connection = $this->_rediska->getConnectionByKeyName($name);

        if (is_null($pushToName)) {
            $command = "RPOP {$this->_rediska->getOption('namespace')}$name";
        } else {
            $toConnection = $this->_rediska->getConnectionByKeyName($pushToName);

            if ($connection->getAlias() == $toConnection->getAlias()) {
                $this->_throwExceptionIfNotSupported('1.1');

            	$command = array('RPOPLPUSH',
            	                 "{$this->_rediska->getOption('namespace')}$name",
            	                 "{$this->_rediska->getOption('namespace')}$pushToName");
            } else {
            	$this->setAtomic(false);

                $command = "RPOP {$this->_rediska->getOption('namespace')}$name";
            }
        }

        return new Rediska_Connection_Exec($connection, $command);
    }

    public function parseResponses($responses)
    {
        if (!$this->isAtomic()) {
            $value = $this->_rediska->getSerializer()->unserialize($responses[0]);

            return $this->_rediska->prependToList($this->pushToName, $value);
        } else {
            return $this->_rediska->getSerializer()->unserialize($responses[0]);
        }
    }
}