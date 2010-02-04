<?php

require_once 'Rediska/Zend/Session/SaveHandler/Redis.php';

class Test_Zend_Session extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rediska_Zend_Session_SaveHandler_Redis
     */
    protected $saveHandler;

    /** 
     * @var Rediska
     */
    protected $rediska;

    protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Tests_', 'servers' => array(array('host' => REDISKA_HOST, 'port' => REDISKA_PORT))));
        $this->saveHandler = new Rediska_Zend_Session_SaveHandler_Redis(array('keyprefix' => 's_'));
    }

    protected function tearDown()
    {
        $this->rediska->flushDb(true);
        $this->rediska = null;
        $this->saveHandler = null;
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