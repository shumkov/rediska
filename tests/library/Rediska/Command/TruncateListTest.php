<?php

class Rediska_Command_TruncateListTest extends Rediska_TestCase
{
    public function testTruncateListReturnTrue()
    {
        $this->_appendFourMembers();

        $reply = $this->rediska->truncateList('test', 0, 2);
        $this->assertTrue($reply);
    }

    public function testTruncateList()
    {
        $this->_appendFourMembers();

        $reply = $this->rediska->truncateList('test', 0, 1);
        $this->assertTrue($reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);
    }
    
    public function testTruncateListWithOffset()
    {
        $this->_appendFourMembers();
        
        $this->rediska->appendToList('test', 'iii');
        $this->rediska->appendToList('test', 'fff');

        $this->rediska->truncateList('test', 1, 2);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('bbb', 'ccc'), $reply);
    }

    protected function _appendFourMembers()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');
        $this->rediska->appendToList('test', 'ddd');
    }
}