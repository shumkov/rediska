<?php

class Rediska_Zend_SessionTest extends Rediska_TestCase
{
    /**
     * @var Rediska_Zend_Session_SaveHandler_Redis
     */
    protected $saveHandler;

    protected function setUp()
    {
        parent::setUp();
        $this->saveHandler = new Rediska_Zend_Session_SaveHandler_Redis(array('keyPrefix' => 's_'));
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

        $values = $this->rediska->getSortedSet('s_sessions');
        $this->assertEquals(array('123'), $values);
    }

    public function testDestroy()
    {
        $this->rediska->set('s_123', 'aaa');
        $this->rediska->addToSortedSet('s_sessions', '123', time());

        $reply = $this->saveHandler->destroy('123');
        $this->assertEquals(1, $reply);

        $values = $this->rediska->getSortedSet('s_sessions');
        $this->assertEquals(array(), $values);

        $reply = $this->rediska->get('s_123');
        $this->assertNull($reply);
    }

    public function testGC()
    {
        $this->saveHandler->write('123', 123);

        $reply = $this->saveHandler->gc(null);
        $this->assertEquals(0, $reply);

        $this->saveHandler->setOption('lifetime', 1);
 
        $this->saveHandler->write('123', 123);

        sleep(2);

        $reply = $this->saveHandler->gc(null);
        $this->assertEquals(1, $reply);

        $values = $this->rediska->getSortedSet('s_sessions');
        $this->assertEquals(array(), $values);
    }
}