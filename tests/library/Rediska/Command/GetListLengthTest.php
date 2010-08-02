<?php

class Rediska_Command_GetListLengthTest extends Rediska_TestCase
{
    public function testEmptyListReturnZero()
    {
        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(0, $reply);
    }
    
    public function testGetListLength()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(2, $reply);
    }
}