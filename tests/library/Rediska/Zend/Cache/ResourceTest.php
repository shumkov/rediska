<?php
/**
 * @group Zend_Cache
 * @group Zend_Cache_Resource
 *
 */
class Rediska_Zend_Cache_ResourceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Rediska_Manager::removeAll();
    }

    public function testDefaultWithoutInstance()
    {
        $this->setExpectedException('Zend_Cache_Exception', "You must instance '" . Rediska::DEFAULT_NAME . "' Rediska before or use 'backend' option for specify another");

        $application = new Zend_Application('tests', dirname(__FILE__) . '/application.ini');
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $manager->getCache('test')->save('1', 'test');
    }

    public function testDefault()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application2.ini');
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $manager->getCache('test')->save('1', 'test');
        $options = $manager->getCache('test')->getBackend()->getOption('storage');
        $one = Rediska_Manager::get()->getHashValues($options['prefix_key'] . 'test');

        $this->assertEquals('1', $one[0]);
    }

    public function testNamedInstance()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application3.ini');
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $manager->getCache('test')->save('1', 'test');

        $options = $manager->getCache('test')->getBackend()->getOption('storage');
        $one = Rediska_Manager::get('test')->getHashValues($options['prefix_key'] . 'test');

        $this->assertEquals('1', $one[0]);

        $this->assertFalse(Rediska_Manager::has('default'));
    }
    /**
     * @group resource
     */
    public function testNewInstance()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application4.ini');
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $manager->getCache('test')->save('1', 'test');

        $options = $manager->getCache('test')->getBackend()->getOption('storage');
        $rediska = new Rediska(array('redisVersion' => '2.0', 'addToManager' => false));
        $one = $rediska->getHashValues($options['prefix_key'].'test');
        $this->assertEquals('1', $one[0]);

        $this->assertEquals(array(), Rediska_Manager::getAll());
    }
    /**
     * @group resource
     * @group auto_serialize
     */
    public function testNewInstanceWithAutoSerialization()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application5.ini');
        /* @var Zend_Cache_Manager $manager */
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $manager->getCache('test')->save('321', 'test');
        $options = $manager->getCache('test')->getBackend()->getOption('storage');
        $rediska = new Rediska(array('redisVersion' => '2.0', 'addToManager' => false));
        $one = $rediska->getHashValues($options['prefix_key'].'test');

        $this->assertEquals('321', $one[0]);

        $this->assertEquals(array(), Rediska_Manager::getAll());
    }

    public function testCleanWithNoIdsToClean()
    {

        $application = new Zend_Application('tests', dirname(__FILE__) . '/application5.ini');
        /* @var Zend_Cache_Manager $manager */
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $actual = $manager->getCache('test')->clean(Zend_Cache::CLEANING_MODE_ALL);

        $this->assertFalse($actual);
    }
}
