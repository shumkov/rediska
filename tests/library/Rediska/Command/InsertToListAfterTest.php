<?php

class Rediska_Command_InsertToListAfterTest extends Rediska_TestCase
{
    public function testInsertToListAfter()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->insertToListAfter('test', 'aaa', 'ccc');
        $this->assertEquals(3, $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'ccc', 'bbb'), $reply);
    }
}