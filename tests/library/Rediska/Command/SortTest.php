<?php

class Rediska_Command_SortTest extends Rediska_TestCase
{
    public function testSortWithArrayOptions()
    {       
        $this->_appendThreeMembers();
        
        $values = $this->rediska->sort('test', array('alpha' => true, 'order' => 'desc', 'limit' => 2));
        $this->assertEquals(array('ccc', 'bbb'), $values);
    }

    protected function _appendThreeMembers()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');
    }
}