<?php

class Rediska_Command_GetKeysCountTest extends Rediska_TestCase
{
    public function testGetKeysCount()
    {
        $this->rediska->set('a', '123');
        $this->rediska->set('b', '123');
        $this->rediska->set('c', '123');

        $reply = $this->rediska->getKeysCount();
        $this->assertEquals(3, $reply);
    }
    
    public function testGetKeysCountWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testGetKeysCount();
    }
}