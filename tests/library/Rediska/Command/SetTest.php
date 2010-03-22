<?php

class Rediska_Command_SetTest extends Rediska_TestCase
{
    public function testSet()
    {   
        $reply = $this->rediska->set('a', 'b');
        $this->assertTrue($reply);
        
        $reply = $this->rediska->set('a', 0);
        $this->assertTrue($reply);
                
        $reply = $this->rediska->set('a', 1);
        $this->assertTrue($reply);
        
        $reply = $this->rediska->set('a', '');
        $this->assertTrue($reply);
        
        $reply = $this->rediska->set('a', null);
        $this->assertTrue($reply);
    }

    public function testSetWithoutOverwrite()
    {
        $reply = $this->rediska->set('a', 'b', false);
        $this->assertTrue($reply);

        $reply = $this->rediska->set('a', 'b', false);
        $this->assertFalse($reply);
    }

    public function testMultiSet()
    {
        $data = array('a' => 'first data', 'b' => 'second data');
        $reply = $this->rediska->set($data);
        $this->assertTrue($reply);

        $reply = $this->rediska->get('a');
        $this->assertEquals('first data', $reply);

        $reply = $this->rediska->get('b');
        $this->assertEquals('second data', $reply);
    }

    public function testMultiSetWithoutOverwrite()
    {       
        $this->rediska->set('a', 1);
        
        $data = array('a' => 'first data', 'b' => 'second data');
        $reply = $this->rediska->set($data, false);
        $this->assertFalse($reply);

        $reply = $this->rediska->get('a');
        $this->assertEquals(1, $reply);

        $reply = $this->rediska->get('b');
        $this->assertNull($reply);
    }
}