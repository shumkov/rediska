<?php

require_once 'Rediska/Key/SortedSet.php';

class Rediska_Key_SortedSetTest extends Rediska_TestCase
{
	/**
     * @var Rediska_Key_Set
     */
    private $set;

    protected function setUp()
    {
        parent::setUp();
        $this->set = new Rediska_Key_SortedSet('test');
    }

    public function testAdd()
    {
    	$reply = $this->set->add(123, 1);
    	$this->assertTrue($reply);

    	$values = $this->rediska->getSortedSet('test');
    	$this->assertTrue(!empty($values));
    	$this->assertEquals(123, $values[0]);
    	
    	$this->set[2] = 456;
    	
    	$values = $this->rediska->getSortedSet('test');
        $this->assertTrue(!empty($values));
        $this->assertEquals(456, $values[1]);
    }

    public function testRemove()
    {
    	$this->rediska->addToSortedSet('test', 123, 1);

    	$reply = $this->set->remove(123);
    	$this->assertTrue($reply);

    	$values = $this->rediska->getSortedSet('test');
        $this->assertTrue(empty($values));

        $this->rediska->addToSortedSet('test', 123, 1);

        unset($this->set[1]);

        $values = $this->rediska->getSortedSet('test');
        $this->assertTrue(empty($values));
    }

    public function testCount()
    {
    	$this->rediska->addToSortedSet('test', 123, 1);
    	$this->rediska->addToSortedSet('test', 456, 2);

    	$this->assertEquals(2, $this->set->count());
    	$this->assertEquals(2, count($this->set));
    }

    public function testGetByScore()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);

        $values = $this->set->getByScore(0, 3);
        $this->assertEquals(123, $values[0]);
        $this->assertEquals(456, $values[1]);
    }

    public function testGetScore()
    {
    	$this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);

        $this->assertEquals(1, $this->set->getScore(123));
        $this->assertEquals(2, $this->set->getScore(456));
    }
    
    public function testIncrementScore()
    {
    	$this->rediska->addToSortedSet('test', 123, 1);

    	$reply = $this->set->incrementScore('123', 5);
    	$this->assertEquals(6, $reply);

    	$this->assertEquals(6, $this->set->getScore(123));
    }

    public function testToArray()
    {
    	$values = $this->set->toArray();
    	$this->assertEquals(array(), $values);

    	$this->rediska->addToSortedSet('test', 123, 1);

        $values = $this->set->toArray();
        $this->assertEquals(array(123), $values);
    }

    public function testFromArray()
    {
    	$reply = $this->set->fromArray(array(3 => 123));
        $this->assertTrue($reply);

        $values = $this->rediska->getSortedSet('test');
        $this->assertTrue(!empty($values));
        $this->assertEquals(123, $values[0]);
    }

    public function testIteration()
    {
        $values = array(123, 456, 789);
        
        foreach($values as $score => $value) {
            $this->rediska->addToSortedSet('test', $value, $score);
        }

        $count = 0;
        foreach($this->set as $score => $value) {
            $this->assertTrue(in_array($value, $values));
            $count++;
        }
        $this->assertEquals(3, $count);
    }

    public function testOffsetSet()
    {
    	$this->set[1] = 123;
    	$this->set[2] = 456;

    	$values = $this->rediska->getSortedSet('test');
        $this->assertTrue(!empty($values));
        $this->assertEquals(123, $values[0]);
        $this->assertEquals(456, $values[1]);
    }

    public function testOffsetGet()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);

        $this->assertEquals(123, $this->set[1]);
        $this->assertEquals(456, $this->set[2]);
    }
}