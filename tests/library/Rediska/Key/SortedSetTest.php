<?php

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

    public function testGetLength()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);

        $this->assertEquals(2, $this->set->getLength());
        $this->assertEquals(2, count($this->set));
    }

    public function testGetLengthByScore()
    {
        $this->rediska->addToSortedSet('test', 1, 1);
        $this->rediska->addToSortedSet('test', 2, 2);
        $this->rediska->addToSortedSet('test', 3, 3);

        $reply = $this->set->getLengthByScore(2, 3);
        $this->assertEquals(2, $reply);
    }

    public function testGetByScore()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);

        $values = $this->set->getByScore(0, 3);
        $this->assertEquals(123, $values[0]);
        $this->assertEquals(456, $values[1]);
    }
    
    public function testRemoveByScore()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);
        $this->rediska->addToSortedSet('test', 789, 3);

        $count = $this->set->removeByScore(0, 2);
        $this->assertEquals(2, $count);

        $values = $this->set->toArray();
        $this->assertEquals(789, $values[0]);
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
    
    public function removeByRank()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);
        $this->rediska->addToSortedSet('test', 789, 3);

        $count = $this->set->removeByRank(0, 1);
        $this->assertEquals(2, $count);

        $values = $this->rediska->getSortedSet('test');
        $this->assertEquals(789, $values[0]);
    }
    
    public function testGetRank()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);
        $this->rediska->addToSortedSet('test', 789, 3);

        $rank = $this->set->getRank(456);
        $this->assertEquals(1, $rank);
    }

    public function testIntersect()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);
        $this->rediska->addToSortedSet('test2', 123, 3);
        $this->rediska->addToSortedSet('test2', 789, 4);

        $count = $this->set->intersect('test2', 'result');
        $this->assertEquals(1, $count);
        $values = $this->rediska->getSortedSet('result', true);
        $this->assertEquals(123, $values[0]->value);
        $this->assertEquals(4, $values[0]->score);
        $this->rediska->delete('result');
        
        $count = $this->set->intersect(new Rediska_Key_SortedSet('test2'), 'result', 'max');
        $this->assertEquals(1, $count);
        $values = $this->rediska->getSortedSet('result', true);
        $this->assertEquals(123, $values[0]->value);
        $this->assertEquals(3, $values[0]->score);
        $this->rediska->delete('result');
        
        $count = $this->set->intersect(array('test2'), 'result', 'min');
        $this->assertEquals(1, $count);
        $values = $this->rediska->getSortedSet('result', true);
        $this->assertEquals(123, $values[0]->value);
        $this->assertEquals(1, $values[0]->score);
        $this->rediska->delete('result');
        
        $count = $this->set->intersect(array('test2' => 2), 'result');
        $this->assertEquals(1, $count);
        $values = $this->rediska->getSortedSet('result', true);
        $this->assertEquals(123, $values[0]->value);
        $this->assertEquals(7, $values[0]->score);
    }
    
    public function testUnion()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 0.5);
        $this->rediska->addToSortedSet('test2', 123, 3);
        $this->rediska->addToSortedSet('test2', 789, 5);

        $count = $this->set->union('test2', 'result');
        $this->assertEquals(3, $count);
        $values = $this->rediska->getSortedSet('result', true);
        $this->assertEquals(123, $values[1]->value);
        $this->assertEquals(4, $values[1]->score);
        $this->rediska->delete('result');

        $count = $this->set->union(new Rediska_Key_SortedSet('test2'), 'result', 'max');
        $this->assertEquals(3, $count);
        $values = $this->rediska->getSortedSet('result', true);
        $this->assertEquals(123, $values[1]->value);
        $this->assertEquals(3, $values[1]->score);
        $this->rediska->delete('result');

        $count = $this->set->union(array('test2'), 'result', 'min');
        $this->assertEquals(3, $count);
        $values = $this->rediska->getSortedSet('result', true);
        $this->assertEquals(123, $values[1]->value);
        $this->assertEquals(1, $values[1]->score);
        $this->rediska->delete('result');

        $count = $this->set->union(array('test2' => 2), 'result');
        $this->assertEquals(3, $count);
        $values = $this->rediska->getSortedSet('result', true);
        $this->assertEquals(123, $values[1]->value);
        $this->assertEquals(7, $values[1]->score);
    }

    public function testSort()
    {
        $this->rediska->addToSortedSet('test', 123, 1);
        $this->rediska->addToSortedSet('test', 456, 2);
        $this->rediska->addToSortedSet('test', 789, 3);

        $values = $this->set->sort(array('order' => 'desc', 'limit' => 2));
        $this->assertEquals(array(789, 456), $values);
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