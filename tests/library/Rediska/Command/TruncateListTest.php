<?php

class Rediska_Command_TruncateListTest extends Rediska_TestCase
{
	public function testTrunctateEmptyListReturnFalse()
	{
		$reply = $this->rediska->truncateList('test', 2);
        $this->assertFalse($reply);
	}
	
	public function testTruncateListReturnTrue()
	{
		$this->_appendFourMembers();

        $reply = $this->rediska->truncateList('test', 2);
        $this->assertTrue($reply);
	}

    public function testTruncateList()
    {
        $this->_appendFourMembers();

        $reply = $this->rediska->truncateList('test', 2);
        $this->assertTrue($reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);
    }
    
    public function testTruncateListWithOffset()
    {
        $this->rediska->appendToList('test', 'iii');
        $this->rediska->appendToList('test', 'fff');

        $reply = $this->rediska->truncateList('test', 2, 1);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('bbb', 'iii'), $reply);
    }

    protected function _appendFourMembers()
    {
    	$this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');
        $this->rediska->appendToList('test', 'ddd');
    }
}