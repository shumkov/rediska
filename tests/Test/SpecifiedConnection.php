<?php

class Test_SpecifiedConnection extends RediskaTestCase
{
    public function testOn()
    {
        $this->rediska->on('127.0.0.1:6379')->set('aaa', 'value');
        $this->assertEquals($this->rediska->on('127.0.0.1:6379')->get('aaa'), 'value');
    }

    public function testOnTwoServers()
    {
        $this->_addServerOrSkipTest('127.0.0.1', 6380);

        $this->rediska->on('127.0.0.1:6379')->set('aaa', 'value');
        $this->rediska->on('127.0.0.1:6380')->set('bbb', 'value');

        $this->assertEquals($this->rediska->on('127.0.0.1:6379')->get('aaa'), 'value');
        $this->assertNull($this->rediska->on('127.0.0.1:6380')->get('aaa'));

        $this->assertNull($this->rediska->on('127.0.0.1:6379')->get('bbb'));
        $this->assertEquals($this->rediska->on('127.0.0.1:6380')->get('bbb'), 'value');
    }
}