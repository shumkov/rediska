<?php

class Rediska_Command_SetAndExpireTest extends Rediska_TestCase
{
    public function testSetAndExpire()
    {
        $reply = $this->rediska->setAndExpire('test', 123, 1);
        $this->assertTrue($reply);
        
        $reply = $this->rediska->get('test');
        $this->assertEquals(123, $reply);
        
        sleep(2);
        
        $reply = $this->rediska->get('test');
        $this->assertNull($reply);
    }
}