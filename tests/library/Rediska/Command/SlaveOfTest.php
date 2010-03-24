<?php

class Rediska_Command_SlaveOfTest extends Rediska_TestCase
{
    public function testSlaveOfAcceptAlias()
    {
        $this->_testSlaveOf();
    }

    public function testSlaveOfAcceptConnectionObject()
    {
        $this->_testSlaveOf(false);
    }

    protected function _testSlaveOf($alias = true)
    {
        $this->_addSecondServerOrSkipTest();

        $this->_checkRoleOnFirstServer('master');

        $firstAlias = REDISKA_HOST . ':' . REDISKA_PORT;
        $secondAlias = REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT;

        try {
            if ($alias) {
                $connection = $this->rediska->getConnectionByAlias($secondAlias);
                $this->rediska->on($firstAlias)->slaveOf($connection);
            } else {
                $this->rediska->on($firstAlias)->slaveOf($secondAlias);
            }
    
            $this->_checkRoleOnFirstServer('slave');
    
            $this->rediska->on($firstAlias)->slaveOf(false);
    
            $this->_checkRoleOnFirstServer('master');
        } catch (Exception $e) {
            $this->rediska->on($firstAlias)->slaveOf(false);
        }
    }
    
    protected function _checkRoleOnFirstServer($role)
    {
        $info = $this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->info();
        $this->assertEquals($role, $info['role']);
    }
}