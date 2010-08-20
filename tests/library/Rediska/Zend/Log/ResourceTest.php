<?php

class Rediska_Zend_Log_ResourceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $rediska = new Rediska();
        $rediska->flushDb();
        
        Rediska_Manager::removeAll();
    }
    
    public function testDefaultWithoutInstance()
    {
        $this->setExpectedException('Rediska_Key_Exception', "You must instance '" . Rediska::DEFAULT_NAME . "' Rediska before or use 'rediska' option for specify another");

        $application = new Zend_Application('tests', dirname(__FILE__) . '/application.ini');
        $log = $application->bootstrap()
                           ->getBootstrap()
                           ->getResource('log');

        $log->err('123');
    }

    public function testDefault()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application2.ini');
        $log = $application->bootstrap()
                           ->getBootstrap()
                           ->getResource('log');

        $log->err('123');
        $log->info('123');

        $rediska = Rediska_Manager::get();

        $count = $rediska->getListLength('log');
        $this->assertEquals(2, $count);
    }
    
    public function testNamedInstance()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application3.ini');
        $log = $application->bootstrap()
                           ->getBootstrap()
                           ->getResource('log');

        $log->err('123');
        $log->info('123');
        
        $rediska = Rediska_Manager::get('test');

        $count = $rediska->getListLength('log');
        $this->assertEquals(2, $count);

        $this->assertFalse(Rediska_Manager::has('default'));
    }

    public function testNewInstance()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application4.ini');
        $log = $application->bootstrap()
                           ->getBootstrap()
                           ->getResource('log');
                           
        $log->err('123');
        $log->info('123');

        $rediska = new Rediska(array('redisVersion' => '2.0', 'addToManager' => false));

        $count = $rediska->getListLength('log');
        $this->assertEquals(2, $count);
        
        $this->assertEquals(array(), Rediska_Manager::getAll());
    }
}