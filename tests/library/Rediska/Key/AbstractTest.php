<?php

require_once 'Rediska/Key.php';

class Rediska_Key_AbstractTest extends Rediska_TestCase
{
	/**
     * @var Rediska_Key
     */
    private $key;

    protected function setUp()
    {
    	parent::setUp();
        $this->key = new Rediska_Key('test');
    }

    public function testDefaultRediskaInstance()
    {
    	$this->assertType('Rediska', $this->key->getRediska());
    }

    public function testSetExpireOnConstruct()
    {
        $key = new Rediska_Key('test', 2);

        $reply = $key->setValue(123);
        $this->assertTrue($reply);

        $value = $key->getValue();
        $this->assertEquals(123, $value);

        sleep(3);

        $value = $key->getValue();
        $this->assertNull($value);
    }

    public function testSpecifiedServerAlias()
    {
        $this->_addSecondServerOrSkipTest();

        $key1 = new Rediska_Key('test', null, REDISKA_HOST . ':' . REDISKA_PORT);
        $key1->setValue(1);
        $key2 = new Rediska_Key('test', null, REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT);
        $key2->setValue(2);

        $reply = $this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->get('test');
        $this->assertEquals(1, $reply);

        $reply = $this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->get('test');
        $this->assertEquals(2, $reply);
    }

    public function testDelete()
    {
    	$this->key->getRediska()->set($this->key->getName(), 1);
    	
    	$reply = $this->key->getRediska()->exists($this->key->getName());
        $this->assertTrue($reply);

    	$this->key->delete();
    	
    	$reply = $this->key->getRediska()->exists($this->key->getName());
    	$this->assertFalse($reply);
    }

    public function testIsExists()
    {
    	$reply = $this->key->isExists();
    	$this->assertFalse($reply);
    	
    	$this->key->getRediska()->set($this->key->getName(), 1);
    	
    	$reply = $this->key->isExists();
        $this->assertTrue($reply);
    }

    public function testGetType()
    {
    	$reply = $this->key->getType();
        $this->assertEquals('none', $reply);

        $this->key->getRediska()->set($this->key->getName(), 1);

        $reply = $this->key->getType();
        $this->assertEquals('string', $reply);
    }

    public function testRename()
    {
    	$reply = $this->key->rename('test2');
    	$this->assertFalse($reply);

    	$this->key->getRediska()->set($this->key->getName(), 1);

    	$reply = $this->key->rename('test2');
        $this->assertTrue($reply);

        $this->assertEquals('test2', $this->key->getName());
    }

    public function testExpire()
    {
    	$this->key->getRediska()->set($this->key->getName(), 1);

    	$this->key->expire(1);

    	sleep(2);

    	$reply = $this->key->getRediska()->get($this->key->getName());
    	$this->assertNull($reply);
    }
    
    public function testExpireByTimestamp()
    {
        $this->key->getRediska()->set($this->key->getName(), 1);
        
        $this->key->expire(time() + 1, true);
        
        sleep(2);

        $reply = $this->key->getRediska()->get($this->key->getName());
        $this->assertNull($reply);
    }

    public function testGetLifetime()
    {
    	$this->key->getRediska()->set($this->key->getName(), 1);
    	$this->key->getRediska()->expire($this->key->getName(), 50);

    	$reply = $this->key->getLifetime();
    	$this->assertGreaterThan(45, $reply);
    }
    
    public function testSetExpire()
    {
        $this->key->setExpire(1);
        
        $expire = $this->key->getExpire();
        $this->assertEquals(1, $expire);
        $this->assertFalse($this->key->isExpireTimestamp());
        
        $this->key->setValue(1);
        
        sleep(2);
        
        $reply = $this->key->getRediska()->get($this->key->getName());
        $this->assertNull($reply);
    }

    public function testSetExpireTimestamp()
    {
        $time = time() + 1;
        $this->key->setExpire($time, true);

        $expire = $this->key->getExpire();
        $this->assertEquals($time, $expire);
        $this->assertTrue($this->key->isExpireTimestamp());

        $this->key->setValue(1);

        sleep(2);

        $reply = $this->key->getRediska()->get($this->key->getName());
        $this->assertNull($reply);
    }
}