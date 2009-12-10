<?php

require_once 'Zend/Cache.php';
require_once 'Rediska.php';

class Test_Zend_Cache extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Zend_Cache_Core
	 */
	protected $cache;

	/** 
	 * @var Rediska
	 */
	protected $rediska;

    protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Tests_'));
        $this->cache = Zend_Cache::factory('Core', 'Rediska_Zend_Cache_Backend_Redis', array('cache_id_prefix' => 'Rediska_Tests_'), array(), false, true);
    }

    protected function tearDown()
    {
        $this->rediska->flushDb(true);
        $this->rediska = null;
        $this->cache = null;
    }
	
	public function testLoad()
    {
        $this->rediska->set('test', array('aaa', time(), null));
        $value = $this->cache->load('test');
        $this->assertEquals('aaa', $value);

        $value = $this->cache->load('test2');
        $this->assertFalse($value);
    }

    public function testTest()
    {
    	$this->rediska->set('test', array('aaa', time(), null));
        $value = $this->cache->test('test');
        $this->assertTrue(is_integer($value));

        $value = $this->cache->test('test2');
        $this->assertFalse($value);
    }
    
    public function testSave()
    {
    	$reply = $this->cache->save('aaa', 'test');
    	$this->assertTrue($reply);

    	$value = $this->rediska->get('test');
    	$this->assertTrue(is_array($value));
    	$this->assertEquals('aaa', $value[0]);

    	$reply = $this->cache->save('aaa', 'test', array(), 2);
        $this->assertTrue($reply);

        sleep(3);

        $value = $this->rediska->get('test');
        $this->assertNull($value);
    }
    
    public function testRemove()
    {
    	$this->rediska->set('test', array('aaa', time(), null));
    	$this->cache->remove('test');
    	$reply = $this->rediska->get('test');
    	$this->assertNull($reply);
    }
    
    public function testClean()
    {
    	$this->rediska->set('test', array('aaa', time(), null));
        $reply = $this->cache->clean();
        $this->assertTrue($reply);
        $reply = $this->rediska->get('test');
        $this->assertNull($reply);
    }
    
    public function testGetMetadats()
    {
    	$this->rediska->set('test', array('aaa', time(), 100));
    	$this->rediska->expire('test', 100);
    	
    	$array = $this->cache->getMetadatas('test');
    	$this->assertTrue(is_array($array));
    	$this->assertGreaterThan(time(), $array['expire']);
    	$this->assertLessThanOrEqual(time(), $array['mtime']);
    	$this->assertEquals(array(), $array['tags']);
    }
    
    public function testTouch()
    {
    	$this->rediska->set('test', array('aaa', time(), 100));
    	$this->rediska->expire('test', 100);

    	$reply = $this->cache->touch('test', 200);
    	$this->assertTrue($reply);

    	$lifetime = $this->rediska->getLifetime('test');
    	$this->assertTrue($lifetime > 290);

    	$values = $this->rediska->get('test');
        $this->assertEquals(300, $values[2]); 
    }
}