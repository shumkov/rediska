<?php

require_once 'Zend/Cache.php';
/**
 *
 *
 * @group Zend_Cache
 */
class Rediska_Zend_Cache_BackendTest extends Rediska_TestCase
{
    /**
     * @var Zend_Cache_Core
     */
    protected $cache;

    protected function setUp()
    {
        parent::setUp();
        $backendOptions = array(
            'storage' => array(
                'set_ids'         => 'zc-test:ids',
                'set_tags'        => 'zc-test:tags',
                'prefix_key'      => 'zc-test:k:',
                'prefix_tag_ids'  => 'zc-test:ti:',
            )
        );
        $this->cache    = Zend_Cache::factory(
            'Core', 'Rediska_Zend_Cache_Backend_Redis', array(), $backendOptions,
            false, true, true
        );
    }

    public function testLoad()
    {
        $this->cache->save('aaa', 'test');
        $value = $this->cache->load('test');
        $this->assertEquals('aaa', $value);

        $value = $this->cache->load('test2');
        $this->assertFalse($value);
    }

    public function testTest()
    {
        $this->cache->save('aaa', 'test');
        $value = $this->cache->test('test');
        $this->assertTrue(is_integer($value));

        $value = $this->cache->test('test2');
        $this->assertFalse($value);
    }

    public function testSave()
    {
        $reply = $this->cache->save('aaa', 'test');
        $this->assertTrue($reply);

        $value = $this->cache->load('test');

        $this->assertEquals('aaa', $value);

        $reply = $this->cache->save('aaa', 'test', array(), 2);
        $this->assertTrue($reply);

        sleep(3);

        $value = $this->cache->load('test');
        $this->assertFalse($value);
    }

    /**
     *
     * @group cache_remove
     */
    public function testRemove()
    {
        $this->cache->save('', 'test_data');
        $reply = $this->cache->load('test_data');
        $this->assertFalse($reply);

        $this->cache->save('aaa', 'test_data');
        $reply = $this->cache->load('test_data');
        $this->assertEquals('aaa', $reply);

        $this->cache->remove('test_data');
        $reply = $this->cache->load('test_data');
        $this->assertFalse($reply);
    }

    /**
     * @group all
     */
    public function testClean()
    {
        $this->setKeys();
        $reply = $this->cache->clean();
        $this->assertTrue($reply);
        $reply = $this->cache->load('test_aaa');
        $this->assertFalse($reply);
    }

    public function testCleanAll()
    {
        $this->setKeys();
        $this->assertTrue((bool)$this->cache->getIds());
        $reply = $this->cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        $this->assertFalse((bool)$this->cache->getIds());
    }

    /**
     *
     */
    public function testGetMetadats()
    {
        $this->setKeys();
        $array = $this->cache->getMetadatas('test_bbb');
        $this->assertTrue(is_array($array));
        $this->assertGreaterThan(time(), $array['expire']);
        $this->assertLessThanOrEqual(time(), $array['mtime']);
        $this->assertEquals(array('tag_a1', 'tag_a2'), $array['tags']);
    }

    /**
     * @group touch
     */
    public function testTouch()
    {
        $this->cache->save('aaa', 'test_id', array(), 100);
        $reply = $this->cache->touch('test_id', 200);
        $this->assertTrue($reply);
        $meta     = $this->cache->getMetadatas('test_id');
        $lifetime = $meta['expire'] - time();
        $this->assertTrue($lifetime > 290);
        $this->assertEquals(300, $lifetime);
    }

    /**
     * @group tags
     */
    public function testGetTags()
    {
        $this->setKeys();
        $actual = $this->cache->getTags();
        sort($actual);
        $this->assertEquals(array('tag_a1', 'tag_a2', 'tag_a3'), $actual);
    }

    /**
     * @group tags
     */
    public function testGetIdsMatchingAnyTag()
    {
        $this->setKeys();
        $ids = $this->cache->getIdsMatchingAnyTags(array('tag_a1', 'tag_a2'));
        sort($ids);
        $this->assertEquals(array('test_aaa', 'test_bbb', 'test_ccc'), $ids);
    }

    /**
     * @group tags
     */
    public function testGetIdsNotMatchingTag()
    {
        $this->setKeys();
        $this->cache->save('nnn', 'no_tag');
        $ids = $this->cache->getIdsNotMatchingTags(array('tag_a1'));
        sort($ids);
        $this->assertEquals(array('no_tag', 'test_ccc', 'test_ddd'), $ids);
    }

    /**
     * @group tags
     */
    public function testGetIdsMatchingTag()
    {
        $this->setKeys();
        $ids = $this->cache->getIdsMatchingTags(array('tag_a1', 'tag_a2'));
        sort($ids);
        $this->assertEquals(array('test_bbb'), $ids);
    }

    /**
     * @group tags
     */
    public function testGetIds()
    {
        $this->setKeys();
        $ids = $this->cache->getIds();
        sort($ids);
        $this->assertEquals(array('test_aaa', 'test_bbb', 'test_ccc', 'test_ddd'), $ids);
    }

    /**
     * @group tags
     */
    public function testCleanMatchingTag()
    {
        $this->setKeys();
        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('tag_a1'));
        $ids = $this->cache->getIds();
        sort($ids);
        $this->assertEquals(array('test_ccc', 'test_ddd'), $ids);
    }

    /**
     * @group tags
     */
    public function testCleanNotMatchingTag()
    {
        $this->setKeys();
        $this->cache->clean(Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG, array('tag_a1'));
        $ids = $this->cache->getIdsNotMatchingTags(array('tag_a1'));
        $ids = $this->cache->getIds();
        sort($ids);
        $this->assertEquals(array('test_aaa', 'test_bbb'), $ids);
    }

    /**
     * @group tags
     */
    public function testCleanAnyMatchingTag()
    {
        $this->setKeys();
        $this->cache->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('tag_a1', 'tag_a3')
        );
        $ids = $this->cache->getIds();
        sort($ids);
        $this->assertEquals(array('test_ccc'), $ids);
    }

    protected function setKeys()
    {
        $this->cache->save('aaa', 'test_aaa', array('tag_a1'));
        $this->cache->save('bbb', 'test_bbb', array('tag_a1', 'tag_a2'));
        $this->cache->save('ccc', 'test_ccc', array('tag_a2'));
        $this->cache->save('ddd', 'test_ddd', array('tag_a3'));
    }
}
