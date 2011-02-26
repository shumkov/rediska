<?php

class Rediska_Command_AppendToListTest extends Rediska_TestCase
{
    public function testReturnListLength()
    {
        $this->rediska->appendToList('test', 'aaa');
        $reply = $this->rediska->appendToList('test', 'bbb');
        $this->assertEquals(2, $reply);
    }

    public function testAppended()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        
        $reply = $this->rediska->getFromList('test', 1);
        $this->assertEquals('bbb', $reply);
        
        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(2, $reply);
    }

    public function testCreateIfNotExists()
    {
        $retry = $this->rediska->appendToList('test', 'aaa', false);
        $this->assertEquals(0, $retry);

        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(0, $reply);
    }
}