<?php

class Rediska_Command_FlushDbTest extends Rediska_TestCase
{
    public function testFlushDb()
    {
        $this->rediska->set('a', 1);
        
        $reply = $this->rediska->flushDb();
        $this->assertTrue($reply);
        
        $value = $this->rediska->get('a');
        $this->assertNull($value);
        
        $this->rediska->set('a', 1);
        
        $this->rediska->selectDb(1);
        
        $reply = $this->rediska->flushDb(true);
        $this->assertTrue($reply);
        
        $this->rediska->selectDb(0);
        
        $value = $this->rediska->get('a');
        $this->assertNull($value);
    }
}