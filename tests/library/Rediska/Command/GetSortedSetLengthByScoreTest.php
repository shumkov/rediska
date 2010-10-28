<?php

class Rediska_Command_GetSortedSetLengthByScoreTest extends Rediska_TestCase
{
    public function testGetSortedSetLengthByScore()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 3, 3);

        $reply = $this->rediska->getSortedSetLengthByScore('test', 2, 3);
        $this->assertEquals(2, $reply);
    }
}