<?php

require_once 'Zend/Application.php';
require_once 'Zend/Registry.php';

class Rediska_Zend_Application_ResourceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Rediska_Manager::removeAll();
        Zend_Registry::_unsetInstance();
    }
    
    public function testOldStyleDefaultAndAnother()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('rediska');

        $default = Rediska_Manager::get('default');
        $this->assertEquals('defaultInstance', $default->getOption('namespace'));       

        $another = Rediska_Manager::get('another');
        $this->assertEquals('anotherInstance', $another->getOption('namespace'));
        
        $this->assertEquals(2, count(Rediska_Manager::getAll()));
        
        $this->assertEquals($default, Zend_Registry::get('rediska'));
    }

    public function testDefaultInstanceOverwriteOldStyle()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application2.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('rediska');

        $default = Rediska_Manager::get('default');
        $this->assertEquals('anotherInstance', $default->getOption('namespace'));

        $this->assertEquals(1, count(Rediska_Manager::getAll()));
        
        $this->assertEquals($default, Zend_Registry::get('rediska'));
    }
    
    public function testTwoInstancesWithoutDefault()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application3.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('rediska');

        $default = Rediska_Manager::get('one');
        $this->assertEquals('one', $default->getOption('namespace'));

        $another = Rediska_Manager::get('two');
        $this->assertEquals('two', $another->getOption('namespace'));
        
        $this->assertEquals(2, count(Rediska_Manager::getAll()));
        
        $this->assertFalse(Zend_Registry::isRegistered('rediska'));
    }
    
    public function testDefaultAndAnother()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application4.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('rediska');

        $default = Rediska_Manager::get('default');
        $this->assertEquals('defaultInstance', $default->getOption('namespace'));

        $another = Rediska_Manager::get('another');
        $this->assertEquals('anotherInstance', $another->getOption('namespace'));
        
        $this->assertEquals(2, count(Rediska_Manager::getAll()));
        
        $this->assertEquals($default, Zend_Registry::get('rediska'));
    }
}