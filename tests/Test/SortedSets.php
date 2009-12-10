<?php

class Test_SortedSets extends RediskaTestCase
{
    public function testAddToSortedSet()
    {
    	$value = $this->rediska->addToSortedSet('test', 'aaa', 1);
    	$this->assertTrue($value);
    	$this->rediska->addToSortedSet('test', 'bbb', 2);

    	$values = $this->rediska->getSortedSet('test');
    	$this->assertContains('aaa', $values);
    	$this->assertContains('bbb', $values);

    	$reply = $this->rediska->getSortedSetLength('test');
    	$this->assertEquals(2, $reply);
    }

    public function testDeleteFromSortedSet()
    {
    	$this->rediska->addToSortedSet('test', 'aaa', 1);
    	$this->rediska->addToSortedSet('test', 'bbb', 2);

    	$reply = $this->rediska->deleteFromSortedSet('test', 'bbb');
    	$this->assertTrue($reply);

    	$reply = $this->rediska->deleteFromSortedSet('test', 'ccc');
        $this->assertFalse($reply);

    	$values = $this->rediska->getSortedSet('test');
        $this->assertContains('aaa', $values);
        $this->assertNotContains('bbb', $values);
    }

    public function testGetSortedSet()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 3, 3);

        $values = $this->rediska->getSortedSet('test');
        $this->assertTrue(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertTrue(in_array(3, $values));
        $this->assertFalse(in_array('xxxx', $values));

        $values = $this->rediska->getSortedSet('test', 'limit 0 2 desc');
        $this->assertEquals(array(3, 2), $values);
    }
    
    public function testGetSortedSetByScore()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 3, 3);

        $values = $this->rediska->getSortedSetByScore('test', 1, 2);
        $this->assertTrue(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));

        $values = $this->rediska->getSortedSetByScore('test', 1, 2, 1);
        $this->assertTrue(in_array(1, $values));
        $this->assertFalse(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));

        $values = $this->rediska->getSortedSetByScore('test', 1, 2, 1);
        $this->assertTrue(in_array(1, $values));
        $this->assertFalse(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));

        $values = $this->rediska->getSortedSetByScore('test', 1, 3, 1, 1);
        $this->assertFalse(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertFalse(in_array(3, $values));
    }

    public function testGetSortedSetLength()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 3, 3);

        $reply = $this->rediska->getSortedSetLength('test');
        $this->assertEquals(3, $reply);
    }

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