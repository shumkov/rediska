<?php

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

        $one = Rediska_Manager::get()->get('test');

        $this->assertEquals('1', $one[0]);
    }
    
    public function testNamedInstance()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application3.ini');
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $manager->getCache('test')->save('1', 'test');

        $one = Rediska_Manager::get('test')->get('test');

        $this->assertEquals('1', $one[0]);

        $this->assertFalse(Rediska_Manager::has('default'));
    }

    public function testNewInstance()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application4.ini');
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $manager->getCache('test')->save('1', 'test');
        
        $rediska = new Rediska(array('redisVersion' => '2.0', 'addToManager' => false));
        $one = $rediska->get('test');

        $this->assertEquals('1', $one[0]);
        
        $this->assertEquals(array(), Rediska_Manager::getAll());
    }
}