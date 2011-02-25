<?php

class Rediska_Command_GetSortedSetTest extends Rediska_TestCase
{
    public function testEmptySetReturnEmptySet()
    {
        $values = $this->rediska->getSortedSet('test');
        $this->assertEquals(array(), $values);
    }

    public function testGetSortedSet()
    {
        $this->_addThreeMembers();

        $values = $this->rediska->getSortedSet('test');
        $this->assertTrue(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertTrue(in_array(3, $values));
        $this->assertFalse(in_array('xxxx', $values));
    }

    public function testGetSortedSetWithLimit()
    {
        $this->_addThreeMembers();

        $values = $this->rediska->getSortedSet('test', false, 0, 1);
        $this->assertEquals(array(1, 2), $values);
    }

    public function testGetSortedSetWithScores()
    {
        $this->_addThreeMembers();

        $values = $this->rediska->getSortedSet('test', true);

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