<?php

class Test_Controls extends RediskaTestCase
{
    public function testSelectDb()
    {
    	$this->rediska->set('a', 1);
    	
    	$reply = $this->rediska->selectDb(1);
    	$this->assertTrue($reply);
    	
    	$value = $this->rediska->get('a');
    	$this->assertNull($value);
    	
    	$this->rediska->selectDb(0);
    	
    	$value = $this->rediska->get('a');
        $this->assertEquals(1, $value);
    }
    
    public function testMoveToDb()
    {
    	$value = $this->rediska->get('a');
        $this->assertNull($value);
        
        $this->rediska->selectDb(1);

    	$this->rediska->set('a', 1);

    	$reply = $this->rediska->moveToDb('a', 0);
        $this->assertTrue($reply);

        $this->rediska->selectDb(0);

        $value = $this->rediska->get('a');
        $this->assertEquals(1, $value);
    }
    
    public function testFlushDb()
    {
    	$this->rediska->set('a', 1);
    	
    	$reply = $this->rediska->flushDb();
    	$this->assertTrue($reply);
    	
    	$value = $this->rediska->get('a');
        $this->assertNull($value);
        
        $this->rediska->set('a', 1);
        
        $this->rediska->selectDb(1);
        
        $reply = $this->rediska->flushDb(true);
        $this->assertTrue($reply);
        
        $this->rediska->selectDb(0);
        
        $value = $this->rediska->get('a');
        $this->assertNull($value);
    }

    public function testSave()
    {    	
    	$reply = $this->rediska->save();
    	$this->assertTrue($reply);

    	$timestamp = $this->rediska->getLastSaveTime();
        $this->assertTrue($timestamp > time() - 1);
    }

    public function testGetLastSaveTime()
    {
    	$timestamp = $this->rediska->getLastSaveTime();
    	$this->assertTrue(is_numeric($timestamp));
    }

    public function testGetLastSaveTimeWithManyConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);

    	$timestamp = $this->rediska->getLastSaveTime();
    	$this->assertTrue(is_array($timestamp));
        $this->assertArrayHasKey('127.0.0.1:6380', $timestamp);
        $this->assertTrue(is_numeric($timestamp['127.0.0.1:6380']));
    }

    public function testShutdown()
    {
    	$this->markTestIncomplete('Not tested!');
    }

    public function testInfo()
    {
    	$info = $this->rediska->info();
    	$this->assertTrue(is_array($info));
    	$this->assertArrayHasKey('redis_version', $info);
    }

    public function testInfoWithManyServers()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);

        $info = $this->rediska->info();
        $this->assertTrue(is_array($info));
        $this->assertArrayHasKey('127.0.0.1:6380', $info);
        $this->assertArrayHasKey('redis_version', $info['127.0.0.1:6380']);
    }
}