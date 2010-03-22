<?php

class Rediska_Command_ShiftFromListTest extends Rediska_TestCase
{
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
}