<?php

class Rediska_Command_GetBitTest extends Rediska_TestCase
{
    public function testGetBit()
    {
        $this->rediska->set('test', '01');

        $reply = $this->rediska->getBit('test', 2);
        $this->assertEquals(1, $reply);
    }
}