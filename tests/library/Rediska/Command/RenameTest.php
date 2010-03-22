<?php

class Rediska_Command_RenameTest extends Rediska_TestCase
{
    public function testRename()
    {
        $this->rediska->set('a', '123');
        $reply = $this->rediska->rename('a', 'b');
        $this->assertTrue($reply);

        $reply = $this->rediska->get('a');
        $this->assertNull($reply);

        $reply = $this->rediska->get('b');
        $this->assertEquals('123', $reply);
    }
    
    public function testRenameWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testRename();
    }

    public function testRenameWithoutOverwrite()
    {
        $this->rediska->set('a', '123');
        $this->rediska->set('b', '456');
        $reply = $this->rediska->rename('a', 'b', false);
        $this->assertFalse($reply);

        $reply = $this->rediska->get('b');
        $this->assertEquals('456', $reply);
    }
    
    public function testRenameWithoutOverwriteWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testRenameWithoutOverwrite();
    }

    /**
     * @expectedException Rediska_Exception
     */
    public function testRenameNotPresentKey()
    {
        $this->rediska->rename('a', 'b');
    }
}