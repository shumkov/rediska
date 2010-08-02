<?php

class Rediska_Command_ExistsTest extends Rediska_TestCase
{
    public function testExists()
    {
        $this->rediska->set('a', 'b');
        $reply = $this->rediska->exists('a');
        $this->assertTrue($reply);
    }
    
    public function testNotExists()
    {
        $reply = $this->rediska->exists('a');
        $this->assertFalse($reply);
    }
}