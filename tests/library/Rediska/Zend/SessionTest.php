<?php

require_once 'Rediska/Zend/Session/SaveHandler/Redis.php';

class Rediska_Zend_SessionTest extends Rediska_TestCase
{
    /**
     * @var Rediska_Zend_Session_SaveHandler_Redis
     */
    protected $saveHandler;

    protected function setUp()
    {
        parent::setUp();
        $this->saveHandler = new Rediska_Zend_Session_SaveHandler_Redis(array('keyprefix' => 's_'));
    }

    public function testConstructWithRedisOptions()
    {
        $saveHandler = new Rediska_Zend_Session_SaveHandler_Redis(array('keyprefix' => 's_', 'rediskaOptions' => array('namespace' => '123')));
        $this->assertEquals('123', $saveHandler->getRediska()->getOption('namespace'));
    }

    public function testRead()
    {
    	$this->rediska->set('s_123', 'aaa');
    	
    	$value = $this->saveHandler->read('123');
    	$this->assertEquals('aaa', $value);
    }

    public function testWrite()
    {
    	$reply = $this->saveHandler->write('123', 'aaa');
    	$this->assertTrue($reply);

    	$value = $this->rediska->get('s_123');
    	$this->assertEquals('aaa', $value);

    	$values = $this->rediska->getSet('s_sessions');
    	$this->assertEquals(array('123'), $values);
    }

    public function testDestroy()
    {
    	$this->rediska->set('s_123', 'aaa');
    	$this->rediska->addToSet('s_sessions', '123');

    	$reply = $this->saveHandler->destroy('123');
        $this->assertTrue($reply);

        $values = $this->rediska->getSet('s_sessions');
        $this->assertEquals(array(), $values);

        $reply = $this->rediska->get('s_123');
        $this->assertNull($reply);
    }

    public function testGC()
    {
        $this->rediska->set('s_123', 'aaa');
        $this->rediska->addToSet('s_sessions', '123');

        $reply = $this->saveHandler->gc(0);
        $this->assertTrue($reply);

        $values = $this->rediska->getSet('s_sessions');
        $this->assertEquals(array('123'), $values);

        $value = $this->rediska->get('s_123');
        $this->assertEquals('aaa', $value);

        $this->rediska->delete('s_123');

        $reply = $this->saveHandler->gc(0);
        $this->assertTrue($reply);

        $values = $this->rediska->getSet('s_sessions');
        $this->assertEquals(array(), $values);
    }
}