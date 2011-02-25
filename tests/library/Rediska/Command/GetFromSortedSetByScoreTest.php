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
        
        $values = $this->rediska->getFromSortedSetByScore('test', 1, 2, false, 1);
        $this->assertTrue(in_array(1, $values));
        $this->assertFalse(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));
    }
    
    public function testGetWithLimitAndOffset()
    {
        $this->_addThreeMembers();

        $values = $this->rediska->getFromSortedSetByScore('test', 1, 3, false, 1, 1);
        $this->assertFalse(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));
    }

    public function testGetWithScores()
    {
        $this->_addThreeMembers();

        $values = $this->rediska->getFromSortedSetByScore('test', 1, 3, true);

        $this->assertInstanceOf('Rediska_Command_Response_ValueAndScore', $values[0]);

        $this->assertEquals($values[0]['score'], 1);
        $this->assertEquals($values[0]['value'], 1);
        $this->assertEquals($values[1]['score'], 2);
        $this->assertEquals($values[1]['value'], 2);
        $this->assertEquals($values[2]->score, 3);
        $this->assertEquals($values[2]->value, 3);
    }

    protected function _addThreeMembers()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 3, 3);
    }
}