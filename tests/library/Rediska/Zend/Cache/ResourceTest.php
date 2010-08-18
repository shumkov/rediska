<?php

class Rediska_Zend_Cache_ResourceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Rediska_Manager::removeAll();
    }
    
    public function testDefaultWithoutInstance()
    {
        $this->setExpectedException(Zend_Cache_Exception, "You must instance '" . Rediska::DEFAULT_NAME . "' Rediska before or use 'backend' option for specify another");

        $application = new Zend_Application('tests', dirname(__FILE__) . '/application.ini');
        $manager = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('cachemanager');

        $manager->getCache('test')->set(1, 'test');
    }
}