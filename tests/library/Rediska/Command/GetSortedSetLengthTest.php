<?php

class Rediska_Command_GetSortedSetLengthTest extends Rediska_TestCase
{
    public function testGetSortedSetLength()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 3, 3);

        $reply = $this->rediska->getSortedSetLength('test');
        $this->assertEquals(3, $reply);
    }
}