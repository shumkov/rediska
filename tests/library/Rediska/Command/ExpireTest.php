<?php

class Rediska_Command_ExpireTest extends Rediska_TestCase
{
    public function testExpiredBySeconds()
    {
        $this->rediska->set('a', '123');
        $this->rediska->expire('a', 1);
        sleep(2);
        $reply = $this->rediska->get('a');
        $this->assertNull($reply);
    }
    
    public function testNotExpiredWithManySeconds()
    {
        $this->rediska->set('a', '123');
        $this->rediska->expire('a', 123);
        $reply = $this->rediska->get('a');
        $this->assertEquals('123', $reply);
    }

    public function testExpiredByTimestamp()
    {
        $this->rediska->set('b', 1);
        $this->rediska->expire('b', time() + 1, true);
        sleep(2);
        $reply = $this->rediska->get('b');
        $this->assertNull($reply);
    }

    public function testNotExpiredByBigTimestamp()
    {
        $this->rediska->set('b', 1);
        $this->rediska->expire('b', time() + 1, true);
        $reply = $this->rediska->get('b');
        $this->assertEquals(1, $reply);
    }
}