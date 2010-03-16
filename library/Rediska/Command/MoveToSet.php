<?php

/**
 * Move the specified member from one Set to another atomically
 * 
 * @param string $fromName From key name
 * @param string $toName   To key name
 * @param mixin  $value    Value
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_MoveToSet extends Rediska_Command_Abstract
{
	protected $_multi = false;

    protected function _create($fromName, $toName, $value)
    {    	
        $fromNameConnection = $this->_rediska->getConnectionByKeyName($fromName);
        $toNameConnection = $this->_rediska->getConnectionByKeyName($toName);
        
        $value = $this->_rediska->serialize($value);

        if ("$fromNameConnection" == "$toNameConnection") {
            $command = "SMOVE {$this->_rediska->getOption('namespace')}$fromName {$this->_rediska->getOption('namespace')}$toName "  . strlen($value) . Rediska::EOL . $value;
        } else {
        	$this->setAtomic(false);
            $command = "SISMEMBER {$this->_rediska->getOption('namespace')}$fromName " . strlen($value) . Rediska::EOL . $value;
        }

        $this->_addCommandByConnection($fromNameConnection, $command);
    }

    protected function _parseResponse($response)
    {
        if (!$this->isAtomic()) {
        	if ($response[0]) {
        		$this->_rediska->deleteFromSet($this->fromName, $this->value);
        		return $this->_rediska->addToSet($this->toName, $this->value);
        	} else {
        		return false;
        	}
        } else {
            return (boolean)$response[0];
        }
    }
}