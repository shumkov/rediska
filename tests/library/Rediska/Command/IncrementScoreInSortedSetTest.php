<?php

class Rediska_Command_IncrementScoreInSortedSetTest extends Rediska_TestCase
{
    public function testIncrementScoreInSortedSet()
    {
        $this->rediska->addToSortedSet('test', 'aaa', 1);

        $reply = $this->rediska->incrementScoreInSortedSet('test', 'aaa', 5);
        $this->assertEquals(6, $reply);

        $reply = $this->rediska->getScoreFromSortedSet('test', 'aaa');
        $this->assertEquals(6, $reply);
    }
}