<?php

class Rediska_Command_PopFromListBlockingTest extends Rediska_TestCase
{
    public function testPopFromOneList()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');

        $reply = $this->rediska->popFromListBlocking('test');
        $this->assertEquals('ccc', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);

        $reply = $this->rediska->popFromListBlocking('test');
        $this->assertEquals('bbb', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa'), $reply);

        $reply = $this->rediska->popFromListBlocking('test');
        $this->assertEquals('aaa', $reply);

        $reply = $this->rediska->popFromListBlocking('test', 1);
        $this->assertNull($reply);
    }
    
    public function testPopFromTwoList()
    {
        $this->rediska->appendToList('test', 'aaa');

        $reply = $this->rediska->popFromListBlocking(array('test', 'test2'));
        $this->assertInstanceOf('Rediska_Command_Response_ListNameAndValue', $reply);
        $this->assertEquals('aaa', $reply->value);
        $this->assertEquals('test', $reply['name']);

        $this->rediska->appendToList('test2', 'bbb');

        $reply = $this->rediska->popFromListBlocking(array('test', 'test2'));
        $this->assertEquals('bbb', $reply->value);

        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test2', 'bbb');

        $reply = $this->rediska->popFromListBlocking(array('test', 'test2'));
        $this->assertEquals('aaa', $reply->value);
    }

    public function testPopFromListAndPushToAnother()
    {
        $this->rediska->appendToList('list1', 'aaa');
        $this->rediska->appendToList('list1', 'bbb');

        $reply = $this->rediska->popFromListBlocking('list1', 0, 'list2');
        $this->assertEquals('bbb', $reply);

        $reply = $this->rediska->getList('list1');
        $this->assertEquals(array('aaa'), $reply);

        $reply = $this->rediska->getList('list2');
        $this->assertEquals(array('bbb'), $reply);

        $reply = $this->rediska->popFromListBlocking('emptyList', 1, 'list2');
        $this->assertNull($reply);
    }

    public function testPopFromListAndPushToAnotherWithSecondConnection()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testPopFromListAndPushToAnother();
    }
}