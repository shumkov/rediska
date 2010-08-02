<?php

class Rediska_Command_ExistsInSetTest extends Rediska_TestCase
{
    public function testNotExistsInNotExistsSetReturnFalse()
    {
        $reply = $this->rediska->existsInSet('test', 'xxx');
        $this->assertFalse($reply);
    }

    public function testNotExistsReturnFalse()
    {
        $this->rediska->addToSet('test', 'aaa');

        $reply = $this->rediska->existsInSet('test', 'xxx');
        $this->assertFalse($reply);
    }

    public function testExistsInSetReturnTrue()
    {
        $this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');
        $this->rediska->addToSet('test', 'ccc');

        $reply = $this->rediska->existsInSet('test', 'aaa');
        $this->assertTrue($reply);
        
        $reply = $this->rediska->existsInSet('test', 'bbb');
        $this->assertTrue($reply);
        
        $reply = $this->rediska->existsInSet('test', 'ccc');
        $this->assertTrue($reply);
    }
}