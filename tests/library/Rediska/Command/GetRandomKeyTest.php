<?php


class Rediska_Command_GetRandomKeyTest extends Rediska_TestCase
{
    public function testNoKeysReturnNull()
    {
        $reply = $this->rediska->getRandomKey();
        $this->assertNull($reply);
    }

    public function testGetRandomKey()
    {
        $this->rediska->set('a', 'b');
        $reply = $this->rediska->getRandomKey();
        $this->assertEquals('a', $reply);
    }
}