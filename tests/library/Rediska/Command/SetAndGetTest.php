<?php

class Rediska_Command_SetAndGetTest extends Rediska_TestCase
{
    public function testSetAndGet()
    {
        $this->rediska->set('a', 'a');
        $reply = $this->rediska->setAndGet('a', 'b');

        $this->assertEquals('a', $reply);

        $reply = $this->rediska->get('a');

        $this->assertEquals('b', $reply);
    }

    public function testSetAndGetWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testSetAndGet();
    }

    public function testSetAndGetNull()
    {
        $reply = $this->rediska->setAndGet('a', 'b');
        $this->assertNull($reply);
    }
}