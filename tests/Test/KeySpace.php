<?php

class Test_KeySpace extends RediskaTestCase
{    
    public function testGetKeysByPattern()
    {
    	$reply = $this->rediska->getKeysByPattern('h*llo');
    	$this->assertEquals(array(), $reply);

    	$keys = array('hello', 'hallo', 'hillo', 'hiiiiillo');

    	foreach($keys as $index => $key) {
    		$this->rediska->set($key, $index);
    	}

    	$reply = $this->rediska->getKeysByPattern('h*llo');
    	foreach($reply as $key) {
            $this->assertContains($key, $keys);
    	}

        $someKeys = $keys;
        unset($someKeys[3]);

        $reply = $this->rediska->getKeysByPattern('h?llo');
        foreach($reply as $key) {
            $this->assertContains($key, $someKeys);
        }

    	unset($someKeys[2]);
    	$reply = $this->rediska->getKeysByPattern('h[ea]llo');
        foreach($reply as $key) {
            $this->assertContains($key, $someKeys);
        }
    }
    
    public function testGetKeysByPatternWithManyConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);

    	$this->testGetKeysByPattern();
    }

    public function testGetRandomKey()
    {
    	$reply = $this->rediska->getRandomKey();
    	$this->assertNull($reply);
    	
    	$this->rediska->set('a', 'b');
    	$reply = $this->rediska->getRandomKey();
    	$this->assertEquals('a', $reply);
    }

    public function testRename()
    {
    	$this->rediska->set('a', '123');
        $reply = $this->rediska->rename('a', 'b');
        $this->assertTrue($reply);

        $reply = $this->rediska->get('a');
        $this->assertNull($reply);

        $reply = $this->rediska->get('b');
        $this->assertEquals('123', $reply);
    }
    
    public function testRenameWithManyConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);

        $this->testRename();
    }

    public function testRenameWithoutOverwrite()
    {
        $this->rediska->set('a', '123');
        $this->rediska->set('b', '456');
        $reply = $this->rediska->rename('a', 'b', false);
        $this->assertFalse($reply);

        $reply = $this->rediska->get('b');
        $this->assertEquals('456', $reply);
    }
    
    public function testRenameWithoutOverwriteWithManyConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);

        $this->testRenameWithoutOverwrite();
    }

    /**
     * @expectedException Rediska_Exception
     */
    public function testRenameNotPresentKey()
    {
    	$this->rediska->rename('a', 'b');
    }
    
    public function testGetKeysCount()
    {
    	$this->rediska->set('a', '123');
    	$this->rediska->set('b', '123');
    	$this->rediska->set('c', '123');

    	$reply = $this->rediska->getKeysCount();
    	$this->assertEquals(3, $reply);
    }
    
    public function testGetKeysCountWithManyConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);

        $this->testGetKeysCount();
    }

    public function testExpire()
    {
    	$this->rediska->set('a', '123');
    	$this->rediska->expire('a', 1);
    	sleep(2);
    	$reply = $this->rediska->get('a');
    	$this->assertNull($reply);

    	$this->rediska->set('a', '123');
    	$this->rediska->expire('a', 123);
    	$reply = $this->rediska->get('a');
        $this->assertEquals('123', $reply);
    }
    
    public function testGetKeyType()
    {
    	$this->rediska->set('a', '123');
    	$reply = $this->rediska->getType('a');
    	$this->assertEquals('string', $reply);
    	
    	$reply = $this->rediska->getType('b');
    	$this->assertEquals('none', $reply);
    }
}

