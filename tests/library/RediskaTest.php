<?php

class RediskaTest extends Rediska_TestCase
{
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
}