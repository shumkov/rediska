<?php

require_once 'Rediska/Key.php';

class Test_Key_Abstract extends PHPUnit_Framework_TestCase
{
	/**
     * @var Rediska_Key
     */
    private $key;
    
    /**
     * @var Rediska
     */
    private $rediska;

    protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Tests_', 'servers' => array(array('host' => REDISKA_HOST, 'port' => REDISKA_PORT))));
        $this->key = new Rediska_Key('test');
    }

    protected function tearDown()
    {
        $this->rediska->flushDb();
        $this->rediska = null;
    }

    public function testDefaultRediskaInstance()
    {
    	$this->assertType('Rediska', $this->key->getRediska());
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

    public function testGetLifetime()
    {
    	$this->key->getRediska()->set($this->key->getName(), 1);
    	$this->key->getRediska()->expire($this->key->getName(), 50);

    	$reply = $this->key->getLifetime();
    	$this->assertGreaterThan(45, $reply);
    }
}