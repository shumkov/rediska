<?php

class Rediska_Command_GetLifetimeTest extends Rediska_TestCase
{
    public function testLifetimeNullOnNotExistsKey()
    {
        $reply = $this->rediska->getLifetime('key');
        $this->assertNull($reply);
    }

    public function testLifeTimeNullOnNotViolateKey()
    {
        $this->rediska->set('key', 1);
        $reply = $this->rediska->getLifetime('key');
        $this->assertNull($reply);
    }

    public function testGetLifetime()
    {
        $this->rediska->set('key', 1);
        $this->rediska->expire('key', 2);
        $reply = $this->rediska->getLifetime('key');
        $this->assertEquals(2, $reply);
    }
    
    public function testGetLifetimeTimestamp()
    {
        $this->rediska->set('key', 1);
        $time = time() + 2;
        $this->rediska->expire('key', $time, false);
        $reply = $this->rediska->getLifetime('key');
        $this->assertEquals($time, $reply);
    }
}