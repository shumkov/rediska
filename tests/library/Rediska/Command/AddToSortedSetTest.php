<?php

class Rediska_Command_AddToSortedSetTest extends Rediska_TestCase
{
    public function testReturnTrue()
    {
        $value = $this->rediska->addToSortedSet('test', 'aaa', 1);
        $this->assertTrue($value);
    }
    
    public function testAddToSortedSet()
    {
        $this->rediska->addToSortedSet('test', 'aaa', 1);
        $this->rediska->addToSortedSet('test', 'bbb', 2);

        $values = $this->rediska->getSortedSet('test');
        $this->assertContains('aaa', $values);
        $this->assertContains('bbb', $values);

        $reply = $this->rediska->getSortedSetLength('test');
        $this->assertEquals(2, $reply);
    }
}