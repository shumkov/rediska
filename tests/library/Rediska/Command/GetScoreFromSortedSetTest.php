<?php

class Rediska_Command_GetScoreFromSortedSetTest extends Rediska_TestCase
{
    public function testGetScoreFromSortedSet()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 'three', 3);

        $reply = $this->rediska->getScoreFromSortedSet('test', 1);
        $this->assertEquals(1, $reply);
        $reply = $this->rediska->getScoreFromSortedSet('test', 2);
        $this->assertEquals(2, $reply);
        $reply = $this->rediska->getScoreFromSortedSet('test', 'three');
        $this->assertEquals(3, $reply);

        $reply = $this->rediska->getScoreFromSortedSet('test', 'three3');
        $this->assertNull($reply);

        $reply = $this->rediska->getScoreFromSortedSet('test3', 'three3');
        $this->assertNull($reply);
    }
}