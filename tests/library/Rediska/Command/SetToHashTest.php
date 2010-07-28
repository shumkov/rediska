<?php

class Rediska_Command_SetToHashTest extends Rediska_TestCase
{
    public function testSetToHashNotExists()
    {
        $reply = $this->rediska->setToHash('test', 'a', 1);
        $this->assertEquals(true, $reply);
        
        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals(1, $reply);
    }
    
    public function testSetToHash()
    {
        $this->rediska->setToHash('test', 'a', 1);

        $reply = $this->rediska->setToHash('test', 'a', 2);
        $this->assertEquals(false, $reply);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals(2, $reply);
    }
    
    public function testSetToHashNoOverwrite()
    {
        $this->rediska->setToHash('test', 'a', 1);

        $reply = $this->rediska->setToHash('test', 'a', 2, false);
        $this->assertEquals(false, $reply);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals(1, $reply);
    }

    public function testSetMulti()
    {
        $data = array('a' => array(1, 5), 'b' => 2, 3 => 'c');

        $reply = $this->rediska->setToHash('test', $data);
        $this->assertEquals(true, $reply);
        
        $dataFromHash = $this->rediska->getHash('test');
        $this->assertEquals($data, $dataFromHash);
    }
}