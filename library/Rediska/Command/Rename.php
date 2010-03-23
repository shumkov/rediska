<?php

/**
 * Rename the old key in the new one
 * 
 * @throws Rediska_Command_Exception
 * @param string $oldName Old key name
 * @param string $newName New key name
 * @param boolean $overwrite Overwrite the new name key if it already exists 
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Rename extends Rediska_Command_Abstract
{
    protected function _create($oldName, $newName, $overwrite = true) 
    {
        $oldNameConnection = $this->_rediska->getConnectionByKeyName($oldName);
        $newNameConnection = $this->_rediska->getConnectionByKeyName($newName);

        if ($oldNameConnection->getAlias() == $newNameConnection->getAlias()) {         
            if ($overwrite) {
                $command = "RENAME";
            } else {
                $command = "RENAMENX";
            }
            $command .= " {$this->_rediska->getOption('namespace')}$oldName {$this->_rediska->getOption('namespace')}$newName";
        } else {
        	$this->setAtomic(false);

            $command = "GET {$this->_rediska->getOption('namespace')}$oldName";
        }

        $this->_addCommandByConnection($oldNameConnection, $command);
    }

    protected function _parseResponse($response)
    {
        if (!$this->isAtomic()) {
            $oldValue = $this->_rediska->unserialize($response[0]);
            if (!is_null($oldValue)) {
                $reply = $this->_rediska->set($this->newName, $oldValue, $this->overwrite);

                if ($reply) {
                    $this->_rediska->delete($this->oldName);
                }

                return $reply;
            } else {
                throw new Rediska_Command_Exception('No such key');
            }
        } else {
            return (boolean)$response[0];
        }
    }
}