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

        $reply = $this->rediska->getList('test', 0, 1);
        $this->assertEquals(array('aaa', 'bbb'), $reply);
    }
    
    public function testGetListWithLimitAndOffset()
    {
        $this->_appendThreeMembers();
        
        $reply = $this->rediska->getList('test', 1);
        $this->assertEquals(array('bbb', 'ccc'), $reply);
    }

    protected function _appendThreeMembers()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');
    }
}