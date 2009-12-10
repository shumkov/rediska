<?php

class Test_Lists extends RediskaTestCase
{
    public function testAppendToList()
    {
    	$reply = $this->rediska->appendToList('test', 'aaa');
    	$this->assertTrue($reply);

    	$this->rediska->appendToList('test', 'bbb');

    	$reply = $this->rediska->getFromList('test', 1);
    	$this->assertEquals('bbb', $reply);

    	$reply = $this->rediska->getListLength('test');
        $this->assertEquals(2, $reply);
    }

    public function testPrependToList()
    {
        $reply = $this->rediska->prependToList('test', 'aaa');
        $this->assertTrue($reply);

        $this->rediska->prependToList('test', 'bbb');

        $reply = $this->rediska->getFromList('test', 0);
        $this->assertEquals('bbb', $reply);

        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(2, $reply);
    }

    public function testGetListLength()
    {
        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(0, $reply);

        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(2, $reply);
    }
    
    public function testGetList()
    {
    	$reply = $this->rediska->getList('test');
    	$this->assertEquals(array(), $reply);

    	$this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb', 'ccc'), $reply);

        $reply = $this->rediska->getList('test', 2);
        $this->assertEquals(array('aaa', 'bbb'), $reply);

        $reply = $this->rediska->getList('test', 2, 1);
        $this->assertEquals(array('bbb', 'ccc'), $reply);
        
        $this->rediska->delete('test');
        
        $this->rediska->appendToList('test', 1);
        $this->rediska->appendToList('test', 2);
        $this->rediska->appendToList('test', 3);
        $this->rediska->appendToList('test', 4);
        
        $values = $this->rediska->getList('test', 'limit 0 3 desc');
        $this->assertEquals(array(4, 3, 2), $values);
    }
    
    public function testTruncateList()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');
        $this->rediska->appendToList('test', 'ddd');

        $reply = $this->rediska->truncateList('test', 2);
        $this->assertTrue($reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);
        
        $this->rediska->appendToList('test', 'iii');
        $this->rediska->appendToList('test', 'fff');

        $reply = $this->rediska->truncateList('test', 2, 1);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('bbb', 'iii'), $reply);
    }

    public function testGetFromList()
    {
        $reply = $this->rediska->getFromList('test', 0);
        $this->assertNull($reply);

        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->getFromList('test', 1);
        $this->assertEquals('bbb', $reply);
    }

    public function testSetToList()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->setToList('test', 0, 'ccc');
        $this->assertTrue($reply);

        $reply = $this->rediska->getFromList('test', 0);
        $this->assertEquals('ccc', $reply);
    }
    
    public function testSetToNotExistsList()
    {
    	$this->setExpectedException('Rediska_Exception');
        $reply = $this->rediska->setToList('test', 1, 0);
    }

    public function testShiftFromList()
    {
    	$this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');

        $reply = $this->rediska->shiftFromList('test');
        $this->assertEquals('aaa', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('bbb', 'ccc'), $reply);
        
        $reply = $this->rediska->shiftFromList('test');
        $this->assertEquals('bbb', $reply);
        
        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('ccc'), $reply);
    }
    
    public function testPopFromList()
    {
    	$this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');

        $reply = $this->rediska->popFromList('test');
        $this->assertEquals('ccc', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);

        $reply = $this->rediska->popFromList('test');
        $this->assertEquals('bbb', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa'), $reply);
    }
    
    public function testPopFromListAndPushToAnother()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');

        $reply = $this->rediska->popFromList('test', 'test2');
        $this->assertEquals('ccc', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);

        $reply = $this->rediska->getList('test2');
        $this->assertEquals(array('ccc'), $reply);
    }

    public function testPopFromListAndPushToAnotherWithManyConnection()
    {
        $this->_addServerOrSkipTest('127.0.0.1', 6380);
        $this->testPopFromListAndPushToAnother();
    }
}