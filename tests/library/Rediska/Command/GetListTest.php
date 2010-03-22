<?php

class Rediska_Command_GetListTest extends Rediska_TestCase
{
	public function testNotPresentListReturnEmptyArray()
	{
		$reply = $this->rediska->getList('test');
        $this->assertEquals(array(), $reply);
	}
	
    public function testGetList()
    {
        $this->_appendThreeMembers();

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb', 'ccc'), $reply);
    }
    
    public function testGetListWithLimit()
    {
    	$this->_appendThreeMembers();
    	
        $reply = $this->rediska->getList('test', 2);
        $this->assertEquals(array('aaa', 'bbb'), $reply);
    }
    
    public function testGetListWithLimitAndOffset()
    {
    	$this->_appendThreeMembers();
    	
    	$reply = $this->rediska->getList('test', 2, 1);
        $this->assertEquals(array('bbb', 'ccc'), $reply);
    }
    
    public function testGetListWithSort()
    {
    	$this->rediska->appendToList('test', 1);
        $this->rediska->appendToList('test', 2);
        $this->rediska->appendToList('test', 3);
        $this->rediska->appendToList('test', 4);

        $values = $this->rediska->getList('test', 'limit 0 3 desc');
        $this->assertEquals(array(4, 3, 2), $values);
    }

    protected function _appendThreeMembers()
    {
    	$this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');
    }
}