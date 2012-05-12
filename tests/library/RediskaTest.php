<?php

class RediskaTest extends Rediska_TestCase
{
    public function testSetServers()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testAddServer()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testGetConnectionByKeyName()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testGetConnectionByAlias()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testGetConnections()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testOn()
    {   
        list($firstServer) = $this->rediska->getConnections();

        $this->rediska->on($firstServer)->set('aaa', 'value');
        $this->assertEquals($this->rediska->on($firstServer->getAlias())->get('aaa'), 'value');
    }

    public function testOnTwoServers()
    {
        $this->_addSecondServerOrSkipTest();

        list($firstServer, $secondServer) = $this->rediska->getConnections();

        $this->rediska->on($firstServer)->set('aaa', 'value');
        $this->rediska->on($secondServer)->set('bbb', 'value');

        $this->assertEquals($this->rediska->on($firstServer->getAlias())->get('aaa'), 'value');
        $this->assertNull($this->rediska->on($secondServer->getAlias())->get('aaa'));

        $this->assertNull($this->rediska->on($firstServer->getAlias())->get('bbb'));
        $this->assertEquals($this->rediska->on($secondServer->getAlias())->get('bbb'), 'value');
    }
    
    public function testPipeline()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testTransaction()
    {
        $transaction = $this->rediska->transaction();
        $this->assertInstanceOf('Rediska_Transaction', $transaction);
    }

    public function testTransactionWithManyServersByAlias()
    {
        $this->_addSecondServerOrSkipTest();
        $transaction = $this->rediska->transaction('127.0.0.1:6380');
        $this->assertInstanceOf('Rediska_Transaction', $transaction);
    }
    
    public function testTransactionWithManyServersByOn()
    {
        $this->_addSecondServerOrSkipTest();
        $transaction = $this->rediska->on('127.0.0.1:6380')->transaction();
        $this->assertInstanceOf('Rediska_Transaction', $transaction);
    }

    public function testTransactionWithManyServersWithoutSpecifiedServer()
    {
        $this->_addSecondServerOrSkipTest();
        $this->setExpectedException('Rediska_Transaction_Exception');
        $transaction = $this->rediska->transaction();
    }
    
    public function testAddCommand()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testRemoveCommand()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testGetCommand()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testCallCommandByMagicMethod()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testSetKeyDistributor()
    {
        $this->markTestIncomplete('Write me!');
    }
    
    public function testSetSerializerAdapter()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testGetSerializer()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testRegisterAutoload()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testUnregisterAutoload()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testIsRegisteredAutoload()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testAutoload()
    {
        $this->markTestIncomplete('Write me!');
    }
}
