<?php

class Rediska_Command_SubstringTest extends Rediska_TestCase
{
    public function testSubstringToEmpty()
    {
        $reply = $this->rediska->substring('test', 0);
        $this->assertNull($reply);
    }

    public function testSubstring()
    {
        $this->rediska->set('test', 'abc');

        $reply = $this->rediska->substring('test', 0);
        $this->assertEquals('abc', $reply);

        $reply = $this->rediska->substring('test', 0, 1);
        $this->assertEquals('ab', $reply);
    }
}