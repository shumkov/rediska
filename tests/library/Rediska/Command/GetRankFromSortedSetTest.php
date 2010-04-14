<?php

class Rediska_Command_GetRankFromSortedSetTest extends Rediska_TestCase
{
    public function testGetRank()
    {
        $this->_addThreeMembers();

        $reply = $this->rediska->getRankFromSortedSet('test', 1);
        $this->assertEquals(0, $reply);
        $reply = $this->rediska->getRankFromSortedSet('test', 2);
        $this->assertEquals(1, $reply);
        $reply = $this->rediska->getRankFromSortedSet('test', 'three');
        $this->assertEquals(2, $reply);
    }
    
    public function testGetRankReverted()
    {
        $this->_addThreeMembers();

        $reply = $this->rediska->getRankFromSortedSet('test', 1, true);
        $this->assertEquals(2, $reply);
        $reply = $this->rediska->getRankFromSortedSet('test', 2, true);
        $this->assertEquals(1, $reply);
        $reply = $this->rediska->getRankFromSortedSet('test', 'three', true);
        $this->assertEquals(0, $reply);
    }

    public function testGetRankOfNoneMembers()
    {
        $reply = $this->rediska->getRankFromSortedSet('test', 'three3');
        $this->assertNull($reply);

        $reply = $this->rediska->getRankFromSortedSet('test3', 'three3');
        $this->assertNull($reply);
    }

    protected function _addThreeMembers()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 'three', 3);
    }
}