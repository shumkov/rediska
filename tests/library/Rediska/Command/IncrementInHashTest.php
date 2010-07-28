<?php

class Rediska_Command_IncrementInHashTest extends Rediska_TestCase
{
    public function testIncrementNotExists()
    {
        $reply = $this->rediska->incrementInHash('test', 'a', 2);
        $this->assertEquals(2, $reply);
        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals(2, $reply);
    }

    public function testIncrement()
    {
        $data = array('a' => array(1, 5), 'b' => 2, 3 => 'c');

        $this->rediska->setToHash('test', $data);
        
        $reply = $this->rediska->incrementInHash('test', 'b', 2);
        $this->assertEquals(4, $reply);
        $reply = $this->rediska->getFromHash('test', 'b');
        $this->assertEquals(4, $reply);
    }
}