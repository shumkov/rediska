<?php

class Rediska_Command_InsertToListBeforeTest extends Rediska_TestCase
{
    public function testInsertToListBefore()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->insertToListBefore('test', 'aaa', 'ccc');
        $this->assertEquals(3, $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('ccc', 'aaa', 'bbb'), $reply);
    }
}