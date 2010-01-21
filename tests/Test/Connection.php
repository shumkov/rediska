<?php

class Test_Connection extends RediskaTestCase
{
	public function testSelectDb()
	{
		$rediska = new Rediska(array('servers' => array(array('host' => '127.0.0.1', 'port' => 6379, 'db' => 2))));
		$rediska->set('a', 123);
		$rediska->selectDb(1);
		$reply = $rediska->get('a');
		$this->assertNull($reply);
		$rediska->selectDb(2);
		$reply = $rediska->get('a');
        $this->assertEquals(123, $reply);
	}

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