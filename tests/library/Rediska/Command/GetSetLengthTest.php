<?php

class Rediska_Command_GetSetLengthTest extends Rediska_TestCase
{
    public function testEmptySetReturnZero()
    {
        $reply = $this->rediska->getSetLength('test');
        $this->assertEquals(0, $reply);
    }

    public function testReturnLength()
    {
        $this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');
        $this->rediska->addToSet('test', 'ccc');

        $reply = $this->rediska->getSetLength('test');
        $this->assertEquals(3, $reply);
    }
}