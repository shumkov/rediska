<?php


class Rediska_Command_GetFromListTest extends Rediska_TestCase
{
    public function testNotMemberReturnNull()
    {
        $reply = $this->rediska->getFromList('test', 0);
        $this->assertNull($reply);
    }
    
    public function testGetFromList()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->getFromList('test', 1);
        $this->assertEquals('bbb', $reply);
    }
}