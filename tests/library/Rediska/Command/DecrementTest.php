<?php

class Rediska_Command_DecrementTest extends Rediska_TestCase
{
    public function testDecrement()
    {
        $this->rediska->decrement('a');
        $reply = $this->rediska->get('a');
        $this->assertEquals(-1, $reply);

        $this->rediska->decrement('a');
        $reply = $this->rediska->decrement('a');
        $this->assertEquals(-3, $reply);

        $reply = $this->rediska->get('a');
        $this->assertEquals(-3, $reply);
        
        $this->rediska->decrement('a', 5);
        $reply = $this->rediska->get('a');
        $this->assertEquals(-8, $reply);
        
        $this->rediska->set('a', 15);
        $reply = $this->rediska->decrement('a', 5);
        $this->assertEquals(10, $reply);
    }
}