<?php

class Rediska_Command_InsertToListTest extends Rediska_TestCase
{
    public function testInsertToListAfter()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->insertToList('test', 'after', 'aaa', 'ccc');
        $this->assertEquals(3, $reply);
        
        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'ccc', 'bbb'), $reply);
    }

    public function testInsertToListBefore()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->insertToList('test', 'before', 'aaa', 'ccc');
        $this->assertEquals(3, $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('ccc', 'aaa', 'bbb'), $reply);
    }

    public function testInsertToListAShit()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->insertToList('test', 'before', 'xxx', 'ccc');
        $this->assertFalse($reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);
    }
}