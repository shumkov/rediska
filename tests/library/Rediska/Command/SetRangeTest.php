<?php

class Rediska_Command_SetRangeTest extends Rediska_TestCase
{
    public function testSetRangeToEmpty()
    {
        $reply = $this->rediska->setRange('test', 2, 'hello');
        $this->assertEquals(7, $reply);
    }

    public function testSetRange()
    {
        $this->rediska->set('test', 'abc');

        $reply = $this->rediska->setRange('test', 2, 'z');
        $this->assertEquals(3, $reply);

        $reply = $this->rediska->get('test');
        $this->assertEquals('abz', $reply);
    }
}