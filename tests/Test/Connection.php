<?php

class Test_Connection extends RediskaTestCase
{
	public function testSelectDb()
	{
		$rediska = new Rediska(array('servers' => array(array('host' => REDISKA_HOST, 'port' => REDISKA_PORT, 'db' => 2))));
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
        $this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->set('aaa', 'value');
        $this->assertEquals($this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->get('aaa'), 'value');
    }

    public function testOnTwoServers()
    {
        $this->_addServerOrSkipTest(REDISKA_SECOND_HOST, REDISKA_SECOND_PORT);

        $this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->set('aaa', 'value');
        $this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->set('bbb', 'value');

        $this->assertEquals($this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->get('aaa'), 'value');
        $this->assertNull($this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->get('aaa'));

        $this->assertNull($this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->get('bbb'));
        $this->assertEquals($this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->get('bbb'), 'value');
    }
}