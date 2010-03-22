<?php

class Rediska_Command_DeleteTest extends Rediska_TestCase
{
    public function testDelete()
    {
        $this->rediska->set('a', 'b');
        $reply = $this->rediska->delete('a');
        $this->assertEquals(1, $reply);

        $reply = $this->rediska->get('a');
        $this->assertNull($reply);

        $reply = $this->rediska->delete('a');
        $this->assertEquals(0, $reply);
    }

    public function testDeleteWithMultiKeyNames()
    {
        $this->rediska->set('a', 'a');
        $this->rediska->set('b', 'b');
        $reply = $this->rediska->delete(array('a', 'b', 'c'));
        $this->assertEquals(2, $reply);

        $reply = $this->rediska->get('a');
        $this->assertNull($reply);

        $reply = $this->rediska->get('b');
        $this->assertNull($reply);

        $reply = $this->rediska->delete(array('a', 'b', 'c'));
        $this->assertEquals(0, $reply);
    }
    
    public function testDeleteWithMultiKeyNamesWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testDeleteWithMultiKeyNames();
    }
}