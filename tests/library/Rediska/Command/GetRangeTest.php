<?php

class Rediska_Command_GetRangeTest extends Rediska_TestCase
{
    public function testGetRangeFromEmpty()
    {
        $reply = $this->rediska->getRange('test', 0);
        $this->assertNull($reply);
    }

    public function testGetRange()
    {
        $this->rediska->set('test', 'abc');

        $reply = $this->rediska->getRange('test', 0);
        $this->assertEquals('abc', $reply);

        $reply = $this->rediska->getRange('test', 0, 1);
        $this->assertEquals('ab', $reply);
    }
}