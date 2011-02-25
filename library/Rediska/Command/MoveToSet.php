<?php

/**
 * Move the specified member from one Set to another atomically
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_MoveToSet extends Rediska_Command_Abstract
{
    protected $_multi = false;

    /**
     * Create command
     *
     * @param string $fromKey From key name
     * @param string $toKey   To key name
     * @param mixed  $member  Member
     * @return Rediska_Connection_Exec
     */
    public function create($fromKey, $toKey, $member)
    {        
        $fromKeyConnection = $this->_rediska->getConnectionByKeyName($fromKey);
        $toKeyConnection = $this->_rediska->getConnectionByKeyName($toKey);
        
        $member = $this->_rediska->getSerializer()->serialize($member);

        if ($fromKeyConnection === $toKeyConnection) {
            $command = array('SMOVE',
                             $this->_rediska->getOption('namespace') . $fromKey,
                             $this->_rediska->getOption('namespace') . $toKey,
                             $member);
        } else {
            $this->setAtomic(false);
            $command = array('SISMEMBER',
                             $this->_rediska->getOption('namespace') . $fromKey,
                             $member);
        }

        return new Rediska_Connection_Exec($fromKeyConnection, $command);
    }

    /**
     * Parse response
     *
     * @param array $responses
     * @return boolean
     */
    public function parseResponses($responses)
    {
        if (!$this->isAtomic()) {
            if ($responses[0]) {
                $this->_rediska->deleteFromSet($this->fromKey, $this->member);
                return $this->_rediska->addToSet($this->toKey, $this->member);
            } else {
                return false;
            }
        } else {
            return (boolean)$responses[0];
        }
    }
}