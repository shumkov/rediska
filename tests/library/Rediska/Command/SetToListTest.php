<?php

class Rediska_Command_SetToListTest extends Rediska_TestCase
{
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
}