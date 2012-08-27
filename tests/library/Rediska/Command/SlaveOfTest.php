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

        list($firstServer, $secondServer) = $this->rediska->getConnections();

        $this->_checkRole($firstServer, 'master');
        $this->_checkRole($secondServer, 'master');

        try {
            if ($alias) {
                $this->rediska->on($firstServer)->slaveOf($secondServer);
            } else {
                $this->rediska->on($firstServer)->slaveOf($secondServer->getAlias());
            }

            $this->_checkRole($firstServer, 'slave');
            $this->_checkRole($secondServer, 'master');
    
            $this->rediska->on($firstServer)->slaveOf(false);
    
            $this->_checkRole($firstServer, 'master');
            $this->_checkRole($secondServer, 'master');
        } catch (Exception $e) {
            $this->rediska->on($firstServer)->slaveOf(false);
        }
    }

    protected function _checkRole($server, $role)
    {
        $info = $this->rediska->on($server->getAlias())->info();
        $this->assertEquals($role, $info->role);
    }
}
