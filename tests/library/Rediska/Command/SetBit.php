<?php

class Rediska_Command_SetBitTest extends Rediska_TestCase
{
    public function testSetBit()
    {
        $this->rediska->setBit('test', 0, 1);
        $this->rediska->setBit('test', 4, 1);

        $reply = $this->rediska->getBit('test', 0);
        $this->assertEquals(1, $reply);

        $reply = $this->rediska->getBit('test', 1);
        $this->assertEquals(0, $reply);

        $reply = $this->rediska->getBit('test', 4);
        $this->assertEquals(1, $reply);
    }
}