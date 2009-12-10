<?php

class Test_Sets extends RediskaTestCase
{
    public function testAddToSet()
    {
    	$value = $this->rediska->addToSet('test', 'aaa');
    	$this->assertTrue($value);
    	$this->rediska->addToSet('test', 'bbb');
    	
    	$values = $this->rediska->getSet('test');
    	$this->assertContains('aaa', $values);
    	$this->assertContains('bbb', $values);
    	
    	$reply = $this->rediska->getSetLength('test');
    	$this->assertEquals(2, $reply);
    }
    
    public function testDeleteFromSet()
    {
    	$this->rediska->addToSet('test', 'aaa');
    	$this->rediska->addToSet('test', 'bbb');

    	$reply = $this->rediska->deleteFromSet('test', 'bbb');
    	$this->assertTrue($reply);
    	
    	$reply = $this->rediska->deleteFromSet('test', 'ccc');
        $this->assertFalse($reply);

    	$values = $this->rediska->getSet('test');
        $this->assertContains('aaa', $values);
        $this->assertNotContains('bbb', $values);
    }
    
    public function testGetRandomFromSet()
    {
    	$value = $this->rediska->getRandomFromSet('test');
        $this->assertNull($value);

    	$this->rediska->addToSet('test', 'aaa');
        
        $value = $this->rediska->getRandomFromSet('test');
        $this->assertEquals('aaa', $value);
        
        $value = $this->rediska->getRandomFromSet('test', true);
        $this->assertEquals('aaa', $value);
        
        $values = $this->rediska->getSet('test');
        $this->assertEquals(array(), $values);
    }

    public function testMoveToSet()
    {
    	$this->rediska->addToSet('test', 'aaa');
    	$this->rediska->addToSet('test', 'bbb');
    	
    	$reply = $this->rediska->moveToSet('test', 'test1', 'aaa');
    	$this->assertTrue($reply);

    	$this->rediska->moveToSet('test', 'test1', 'bbb');
    	
    	$values = $this->rediska->getSet('test');
    	$this->assertEquals(array(), $values);
    	
    	$values = $this->rediska->getSet('test1');
        $this->assertContains('aaa', $values);
        $this->assertContains('bbb', $values);
    }
    
    public function testMoveToSetWithMayConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);
    	$this->testMoveToSet();
    }
    
    public function testGetSetLength()
    {
    	$this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');
        $this->rediska->addToSet('test', 'ccc');
        
        $reply = $this->rediska->getSetLength('test');
        $this->assertEquals(3, $reply);
    }
    
    public function testExistsInSet()
    {
    	$this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');
        $this->rediska->addToSet('test', 'ccc');
        
        $reply = $this->rediska->existsInSet('test', 'aaa');
        $this->assertTrue($reply);
        
        $reply = $this->rediska->existsInSet('test', 'xxx');
        $this->assertFalse($reply);
    }
    
    public function testIntersectSets()
    {
    	$myIntersection = array_intersect($this->_sets['set1'], $this->_sets['set2'], $this->_sets['set2']);
        sort($myIntersection);
    	
    	$keys = $this->_createThreeSets();
    	
    	$intersection = $this->rediska->intersectSets($keys);
    	sort($intersection);
    	$this->assertEquals($myIntersection, $intersection);
    	
    	$reply = $this->rediska->intersectSets($keys, 'new-set');
    	$this->assertTrue($reply);
    	
    	$intersection = $this->rediska->getSet('new-set');
    	sort($intersection);
    	$this->assertEquals($myIntersection, $intersection);
    }
    
    public function testIntersectSetsWithManyConnection()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);
        $this->testIntersectSets();
    }
    
    public function testUnionSets()
    {
    	$myUnion = array_unique(array_merge($this->_sets['set1'], $this->_sets['set2'], $this->_sets['set3']));
        sort($myUnion);

    	$keys = $this->_createThreeSets();

        $union = $this->rediska->unionSets($keys);
        sort($union);
        $this->assertEquals($myUnion, $union);
        
        $reply = $this->rediska->unionSets($keys, 'new-set');
        $this->assertTrue($reply);
        
        $union = $this->rediska->getSet('new-set');
        sort($union);
        $this->assertEquals($myUnion, $union);
    }
    
    public function testUnionSetsWithManyConnection()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);
        $this->testUnionSets();
    }
    
    public function testDiffSets()
    {
    	$myDiff = array_diff($this->_sets['set1'], $this->_sets['set2'], $this->_sets['set3']);
        sort($myDiff);

        $keys = $this->_createThreeSets();

        $diff = $this->rediska->diffSets($keys);
        sort($diff);
        $this->assertEquals($myDiff, $diff);

        $reply = $this->rediska->diffSets($keys, 'new-set');
        $this->assertTrue($reply);

        $diff = $this->rediska->getSet('new-set');
        sort($diff);
        $this->assertEquals($myDiff, $diff);
    }
    
    public function testDiffSetsWithManyConnection()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);
        $this->testDiffSets();
    }
    
    public function testGetSet()
    {
    	$this->rediska->addToSet('test', 1);
        $this->rediska->addToSet('test', 2);
        $this->rediska->addToSet('test', 3);

        $values = $this->rediska->getSet('test');
        $this->assertTrue(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertTrue(in_array(3, $values));
        $this->assertFalse(in_array('xxxx', $values));

        $values = $this->rediska->getSet('test', 'limit 0 2 desc');
        $this->assertEquals(array(3, 2), $values);
    }

    protected $_sets = array(
        'set1' => array(1, 3, 6, 4, 2, 'fds', 312, array('1a', 1, 2)),
        'set2' => array(1, 5, 3, 7, 'aaa', 534),
        'set3' => array('asdas', 1, 6, 3, 'y', 'aaa', 4)
    );

    public function _createThreeSets()
    {
    	foreach($this->_sets as $key => $values) {
    		foreach($values as $value) {
    			$this->rediska->addToSet($key, $value);
    		}
    	}
    	
    	return array_keys($this->_sets);
    }
}