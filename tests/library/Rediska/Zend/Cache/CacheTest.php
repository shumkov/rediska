<?php

require_once 'Zend/Cache.php';

class Rediska_Zend_Cache_BackendTest extends Rediska_TestCase
{
    /**
     * @var Zend_Cache_Core
     */
    protected $cache;

    protected function setUp()
    {
        parent::setUp();

        $this->cache = Zend_Cache::factory('Core', 'Rediska_Zend_Cache_Backend_Redis', array(), array(), false, true, true);
    }
    protected function tearDown()
    {
        $this->rediska->flushDb();
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
    /**
     * @group tags
     */
    public function testGetTags()
    {
        $this->setKeys();
        $actual = $this->cache->getTags();
        sort($actual);
        $this->assertEquals(array('tag_a1','tag_a2','tag_a3'), $actual);
    }
    /**
     * @group tags
     */
    public function testGetIdsMatchingAnyTag()
    {
        $this->setKeys();
        $ids = $this->cache->getIdsMatchingAnyTags(array('tag_a1','tag_a2'));
        sort($ids);
        $this->assertEquals(array('test_aaa','test_bbb','test_ccc'), $ids);
    }
    /**
     * @group tags1
     */
    public function testGetIdsNotMatchingTag()
    {
        $this->setKeys();
        $ids = $this->cache->getIdsNotMatchingTags(array('tag_a1'));
        sort($ids);
        $this->assertEquals(array('test_ccc', 'test_ddd'), $ids);
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
        $this->assertEquals(array('test_aaa','test_bbb','test_ccc','test_ddd'), $ids);
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
        $this->assertEquals(array('test_ccc','test_ddd'), $ids);
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
        $this->assertEquals(array('test_aaa','test_bbb'), $ids);
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
    protected function setKeys(){
        $this->cache->save('aaa', 'test_aaa', array('tag_a1'));
        $this->cache->save('bbb', 'test_bbb', array('tag_a1', 'tag_a2'));
        $this->cache->save('ccc', 'test_ccc', array('tag_a2'));
        $this->cache->save('ddd', 'test_ddd', array('tag_a3'));
    }
}
