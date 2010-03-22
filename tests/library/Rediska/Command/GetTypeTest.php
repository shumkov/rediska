<?php

class Rediska_Command_GetTypeTest extends Rediska_TestCase
{
    public function testGetType()
    {
        $this->rediska->set('a', '123');
        $reply = $this->rediska->getType('a');
        $this->assertEquals('string', $reply);

        $reply = $this->rediska->getType('b');
        $this->assertEquals('none', $reply);
    }
}