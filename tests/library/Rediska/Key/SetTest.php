<?php

class Rediska_Key_SetTest extends Rediska_TestCase
{
    /**
     * @var Rediska_Key_Set
     */
    private $set;

    protected function setUp()
    {
        parent::setUp();
        $this->set = new Rediska_Key_Set('test');
    }

    public function testAdd()
    {
        $reply = $this->set->add(123);
        $this->assertTrue($reply);

        $values = $this->rediska->getSet('test');
        $this->assertTrue(!empty($values));
        $this->assertEquals(123, $values[0]);
    }
    
    public function testRemove()
    {
        $this->rediska->addToSet('test', 123);
        
        $reply = $this->set->remove(123);
        $this->assertTrue($reply);
        
        $reply = $this->rediska->existsInSet('test', 123);
        $this->assertFalse($reply);
    }
    
    public function testMove()
    {
        $this->rediska->addToSet('test', 123);

        $reply = $this->set->move('test2', 123);
        $this->assertTrue($reply);

        $reply = $this->rediska->existsInSet('test', 123);
        $this->assertFalse($reply);

        $reply = $this->rediska->existsInSet('test2', 123);
        $this->assertTrue($reply);

        $set2 = new Rediska_Key_Set('test2');
        $set2->move($this->set, 123);

        $reply = $this->rediska->existsInSet('test2', 123);
        $this->assertFalse($reply);

        $reply = $this->rediska->existsInSet('test', 123);
        $this->assertTrue($reply);
    }
    
    public function testCount()
    {
        $this->rediska->addToSet('test', 123);
        $this->rediska->addToSet('test', 456);
        
        $this->assertEquals(2, $this->set->count());
        $this->assertEquals(2, count($this->set));
    }
    
    public function testExists()
    {
        $reply = $this->set->exists(123);
        $this->assertFalse($reply);

        $this->rediska->addToSet('test', 123);

        $reply = $this->set->exists(123);
        $this->assertTrue($reply);
    }

    public function testIntersect()
    {
        $this->rediska->addToSet('test', 123);
        $this->rediska->addToSet('test', 456);
        $this->rediska->addToSet('test2', 123);
        $this->rediska->addToSet('test2', 789);

        $values = $this->set->intersect('test2');
        $this->assertEquals(array(123), $values);
        
        $values = $this->set->intersect(new Rediska_Key_Set('test2'));
        $this->assertEquals(array(123), $values);
        
        $values = $this->set->intersect(array('test2'));
        $this->assertEquals(array(123), $values);
    }
    
    public function testUnion()
    {
        $this->rediska->addToSet('test', 123);
        $this->rediska->addToSet('test2', 123);

        $values = $this->set->union('test2');
        $this->assertEquals(array(123), $values);
        
        $values = $this->set->union(new Rediska_Key_Set('test2'));
        $this->assertEquals(array(123), $values);
        
        $values = $this->set->union(array('test2'));
        $this->assertEquals(array(123), $values);
    }
    
    public function testDiff()
    {
        $this->rediska->addToSet('test', 123);
        $this->rediska->addToSet('test', 456);
        $this->rediska->addToSet('test2', 456);

        $values = $this->set->diff('test2');
        $this->assertEquals(array(123), $values);

        $values = $this->set->diff(new Rediska_Key_Set('test2'));
        $this->assertEquals(array(123), $values);

        $values = $this->set->diff(array('test2'));
        $this->assertEquals(array(123), $values);
    }
    
    public function testSort()
    {
        $this->rediska->addToSet('test', 123);
        $this->rediska->addToSet('test', 456);
        $this->rediska->addToSet('test', 789);

        $values = $this->set->sort(array('order' => 'desc', 'limit' => 2));
        $this->assertEquals(array(789, 456), $values);
    }
    
    public function testToArray()
    {
        $values = $this->set->toArray();
        $this->assertEquals(array(), $values);
        
        $this->rediska->addToSet('test', 123);
        
        $values = $this->set->toArray();
        $this->assertEquals(array(123), $values);
    }
    
    public function testFromArray()
    {
        $reply = $this->set->fromArray(array(123));
        $this->assertTrue($reply);
        
        $reply = $this->rediska->existsInSet('test', 123);
        $this->assertTrue($reply);
    }

    public function testIteration()
    {
        $values = array(123, 456, 789);
        
        foreach($values as $value) {
            $this->rediska->addToSet('test', $value);
        }

        $count = 0;
        foreach($this->set as $value) {
            $this->assertTrue(in_array($value, $values));
            $count++;
        }
        $this->assertEquals(3, $count);
    }

    public function testOffsetSet()
    {
        $this->set[] = 123;

        $reply = $this->rediska->existsInSet('test', 123);
        $this->assertTrue($reply);
    }
}