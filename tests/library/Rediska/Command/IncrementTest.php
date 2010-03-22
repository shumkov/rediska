<?php

class Rediska_Command_IncrementTest extends Rediska_TestCase
{
    public function testIncrement()
    {
        $this->rediska->increment('a');
        $reply = $this->rediska->get('a');
        $this->assertEquals(1, $reply);

        $this->rediska->increment('a');
        $reply = $this->rediska->increment('a');
        $this->assertEquals(3, $reply);

        $reply = $this->rediska->get('a');
        $this->assertEquals(3, $reply);

        $this->rediska->increment('a', 5);
        $reply = $this->rediska->get('a');
        $this->assertEquals(8, $reply);

        $this->rediska->set('a', 15);
        $reply = $this->rediska->increment('a', 5);
        $this->assertEquals(20, $reply);
    }
}