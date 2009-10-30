<?php

class Test_Strings extends RediskaTestCase
{
    public function testSet()
    {	
        $reply = $this->rediska->set('a', 'b');
        $this->assertTrue($reply);
        
        $reply = $this->rediska->set('a', 0);
        $this->assertTrue($reply);
                
        $reply = $this->rediska->set('a', 1);
        $this->assertTrue($reply);
        
        $reply = $this->rediska->set('a', '');
        $this->assertTrue($reply);
        
        $reply = $this->rediska->set('a', null);
        $this->assertTrue($reply);
    }

    public function testSetWithoutOverwrite()
    {       
        $reply = $this->rediska->set('a', 'b', null, false);
        $this->assertTrue($reply);

        $reply = $this->rediska->set('a', 'b', null, false);
        $this->assertFalse($reply);
    }

    public function testSetWithExpire()
    {       
        $reply = $this->rediska->set('a', 'b', 1);
        $this->assertTrue($reply);
        sleep(2);
        $reply = $this->rediska->get('a');
        $this->assertNull($reply);
    }

    public function testSetWithNotExpired()
    {       
        $reply = $this->rediska->set('a', 'b', 20);
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertEquals('b', $reply);
    }
    
    public function testSetAndGet()
    {
    	$this->rediska->set('a', 'a');
    	$reply = $this->rediska->setAndGet('a', 'b');
    	$this->assertEquals('a', $reply);
    	$reply = $this->rediska->get('a');
    	$this->assertEquals('b', $reply);
    }

    public function testSetAndGetNull()
    {
        $reply = $this->rediska->setAndGet('a', 'b');
        $this->assertNull($reply);
    }

    public function testGet()
    {
    	$this->rediska->set('a', 'b');
    	$reply = $this->rediska->get('a');
    	$this->assertEquals('b', $reply);

    	$reply = $this->rediska->set('a', 0);
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertEquals(0, $reply);

        $reply = $this->rediska->set('a', 1);
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertEquals(1, $reply);

        $reply = $this->rediska->set('a', '');
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertEquals('', $reply);

        $reply = $this->rediska->set('a', null);
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertNull($reply);	
    }

    public function testGetWithNotPresentKey()
    {
        $reply = $this->rediska->get('a');
        $this->assertNull($reply);
    }

    public function testGetWithArrayOfKeyNames()
    {
    	$keyNames = array('a', 'b', 'c', 'd');
    	$notExistsKeyName = array('i', 'j');

    	foreach($keyNames as $keyName) {
    		$this->rediska->set($keyName, "value of $keyName");
    	}    	

    	$values = $this->rediska->get(array_merge($keyNames, $notExistsKeyName));

    	foreach($keyNames as $keyName) {
    		$this->assertArrayHasKey($keyName, $values);
    		$this->assertEquals("value of $keyName", $values[$keyName]);
    	}

        foreach($notExistsKeyName as $keyName) {
            $this->assertArrayNotHasKey($keyName, $values);
        }

        // Test order
        $index = 0;
        foreach($values as $key => $value) {
        	$this->assertEquals($key, $keyNames[$index]);
        	$index++;
        }
    }
    
    public function testGetWithArrayOfKeyNamesWithManyConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);
        $this->testGetWithArrayOfKeyNames();
    }

    public function testIncrement()
    {
    	$this->rediska->increment('a');
    	$reply = $this->rediska->get('a');
    	$this->assertEquals(1, $reply);
    	
    	$this->rediska->increment('a');
        $reply = $this->rediska->increment('a');
        $this->assertEquals(3, $reply);

        $reply = $this->rediska->get('a');
        $this->assertEquals(3, $reply);
        
        $this->rediska->increment('a', 5);
        $reply = $this->rediska->get('a');
        $this->assertEquals(8, $reply);
        
        $this->rediska->set('a', 15);
        $reply = $this->rediska->increment('a', 5);
        $this->assertEquals(20, $reply);
    }

    public function testDecrement()
    {
        $this->rediska->decrement('a');
        $reply = $this->rediska->get('a');
        $this->assertEquals(-1, $reply);

        $this->rediska->decrement('a');
        $reply = $this->rediska->decrement('a');
        $this->assertEquals(-3, $reply);

        $reply = $this->rediska->get('a');
        $this->assertEquals(-3, $reply);
        
        $this->rediska->decrement('a', 5);
        $reply = $this->rediska->get('a');
        $this->assertEquals(-8, $reply);
        
        $this->rediska->set('a', 15);
        $reply = $this->rediska->decrement('a', 5);
        $this->assertEquals(10, $reply);
    }

    public function testExists()
    {
    	$this->rediska->set('a', 'b');
    	$reply = $this->rediska->exists('a');
    	$this->assertTrue($reply);
    	
    	$reply = $this->rediska->exists('b');
        $this->assertFalse($reply);
    }

    public function testDelete()
    {
    	$this->rediska->set('a', 'b');
    	$reply = $this->rediska->delete('a');
    	$this->assertTrue($reply);
    	
    	$reply = $this->rediska->get('a');
    	$this->assertNull($reply);

    	$reply = $this->rediska->delete('a');
        $this->assertFalse($reply);
    }

    public function testDeleteWithMultiKeyNames()
    {
    	$this->rediska->set('a', 'a');
    	$this->rediska->set('b', 'b');
        $reply = $this->rediska->delete(array('a', 'b', 'c'));
        $this->assertEquals(2, $reply);
        
        $reply = $this->rediska->get('a');
        $this->assertNull($reply);
        
        $reply = $this->rediska->get('b');
        $this->assertNull($reply);
        
        $reply = $this->rediska->delete(array('a', 'b', 'c'));
        $this->assertEquals(0, $reply);
    }
    
    public function testDeleteWithMultiKeyNamesWithManyConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);
        $this->testDeleteWithMultiKeyNames();
    }
}