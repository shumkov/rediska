<?php

class Rediska_Command_GetLengthTest extends Rediska_TestCase
{
    public function testGetLengthFromEmpty()
    {
        $reply = $this->rediska->getLength('test');
        $this->assertEquals(0, $reply);
    }

    public function testGetLength()
    {
        $this->rediska->set('test', 'abc');

        $reply = $this->rediska->getLength('test');
        $this->assertEquals(3, $reply);
    }
}