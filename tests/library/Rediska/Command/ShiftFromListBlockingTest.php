<?php

class Rediska_Command_ShiftFromListBlockingTest extends Rediska_TestCase
{
    public function testShiftFromOneList()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');

        $reply = $this->rediska->shiftFromListBlocking('test');
        $this->assertType('Rediska_Command_Response_ListNameAndValue', $reply);
        $this->assertEquals('aaa', $reply->value);
        $this->assertEquals('test', $reply['name']);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('bbb', 'ccc'), $reply);

        $reply = $this->rediska->shiftFromListBlocking('test');
        $this->assertEquals('bbb', $reply->value);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('ccc'), $reply);

        $reply = $this->rediska->shiftFromListBlocking('test');
        $this->assertEquals('ccc', $reply->value);

        $reply = $this->rediska->shiftFromListBlocking('test', 1);
        $this->assertNull($reply);
    }
    
    public function testShiftFromTwoList()
    {
        $this->rediska->appendToList('test', 'aaa');

        $reply = $this->rediska->shiftFromListBlocking(array('test', 'test2'));
        $this->assertEquals('aaa', $reply->value);

        $this->rediska->appendToList('test2', 'bbb');

        $reply = $this->rediska->shiftFromListBlocking(array('test', 'test2'));
        $this->assertEquals('bbb', $reply->value);

        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test2', 'bbb');

        $reply = $this->rediska->popFromListBlocking(array('test', 'test2'));
        $this->assertEquals('aaa', $reply->value);
    }
}