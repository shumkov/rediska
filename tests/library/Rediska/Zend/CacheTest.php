<?php

require_once 'Zend/Cache.php';

class Rediska_Zend_CacheTest extends Rediska_TestCase
{
	/**
	 * @var Zend_Cache_Core
	 */
	protected $cache;

    protected function setUp()
    {
        parent::setUp();
        $config = $GLOBALS['rediskaConfigs'][0];
        if (isset($config['namespace'])) {
            unset($config['namespace']);
        }
        $this->cache = Zend_Cache::factory('Core', 'Rediska_Zend_Cache_Backend_Redis', array('cache_id_prefix' => $this->rediska->getOption('namespace')), $config, false, true);
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