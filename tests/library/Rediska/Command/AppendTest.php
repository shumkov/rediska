<?php

class Rediska_Command_AppendTest extends Rediska_TestCase
{
    public function testAppendToEmpty()
    {
        $reply = $this->rediska->append('test', 'abc');
        $this->assertEquals(3, $reply);
    }
    
    public function testAppend()
    {
        $this->rediska->set('test', 'abc');

        $reply = $this->rediska->append('test', 'abc');
        $this->assertEquals(6, $reply);

        $reply = $this->rediska->get('test');
        $this->assertEquals('abcabc', $reply);
    }
}