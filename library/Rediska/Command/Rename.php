<?php

/**
 * Rename the old key in the new one
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Rename extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string            $oldKey    Old key name
     * @param string            $newKey    New key name
     * @param boolean[optional] $overwrite Overwrite the new name key if it already exists. For default is false.
     * @return Rediska_Connection_Exec
     */
    public function create($oldKey, $newKey, $overwrite = true)
    {
        $oldKeyConnection = $this->_rediska->getConnectionByKeyName($oldKey);
        $newKeyConnection = $this->_rediska->getConnectionByKeyName($newKey);

        $command = '';
        if ($oldKeyConnection === $newKeyConnection) {
            if ($overwrite) {
                $command = array();
            } else {
                $command = "";
            }
            $command = array($overwrite ? 'RENAME' : 'RENAMENX',
                             $this->_rediska->getOption('namespace') . $oldKey,
                             $this->_rediska->getOption('namespace') . $newKey);
        } else {
            $this->setAtomic(false);

            $command = array('GET',
                             $this->_rediska->getOption('namespace') . $oldKey);
        }

        return new Rediska_Connection_Exec($oldKeyConnection, $command);
    }

    /**
     * Parse responses
     *
     * @param array $responses
     * @return boolean
     */
    public function parseResponses($responses)
    {
        if (!$this->isAtomic()) {
            $oldValue = $this->_rediska->getSerializer()->unserialize($responses[0]);
            if (!is_null($oldValue)) {
                $reply = $this->_rediska->set($this->newKey, $oldValue, $this->overwrite);

                if ($reply) {
                    $this->_rediska->delete($this->oldKey);
                }

                return $reply;
            } else {
                throw new Rediska_Command_Exception('No such key');
            }
        } else {
            return (boolean)$responses[0];
        }
    }
}