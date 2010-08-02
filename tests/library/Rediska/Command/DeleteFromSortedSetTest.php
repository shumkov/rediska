<?php

class Rediska_Command_DeleteFromSortedSetTest extends Rediska_TestCase
{
    public function testDeleteFromNotExistsSetReturnFalse()
    {
        $reply = $this->rediska->deleteFromSortedSet('test', 'bbb');
        $this->assertFalse($reply);
    }
    
    public function testDeleteReturnTrue()
    {
        $this->rediska->addToSortedSet('test', 'aaa', 1);
        $reply = $this->rediska->deleteFromSortedSet('test', 'aaa');
        $this->assertTrue($reply);
    }
    
    public function testDeleteFromSortedSet()
    {
        $this->rediska->addToSortedSet('test', 'aaa', 1);
        $this->rediska->addToSortedSet('test', 'bbb', 2);

        $this->rediska->deleteFromSortedSet('test', 'bbb');

        $values = $this->rediska->getSortedSet('test');
        $this->assertContains('aaa', $values);
        $this->assertNotContains('bbb', $values);
    }
}