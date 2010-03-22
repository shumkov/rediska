<?php

class Rediska_Command_GetFromSortedSetByScoreTest extends Rediska_TestCase
{
    public function testGet()
    {
        $this->_addThreeMembers();

        $values = $this->rediska->getFromSortedSetByScore('test', 1, 2);
        $this->assertTrue(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));
    }

    public function testGetWithLimit()
    {
        $this->_addThreeMembers();
        
        $values = $this->rediska->getFromSortedSetByScore('test', 1, 2, 1);
        $this->assertTrue(in_array(1, $values));
        $this->assertFalse(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));
    }
    
    public function testGetWithLimitAndOffset()
    {
        $this->_addThreeMembers();

        $values = $this->rediska->getFromSortedSetByScore('test', 1, 3, 1, 1);
        $this->assertFalse(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));
    }

	protected function _addThreeMembers()
	{
		$this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 3, 3);
	}
}