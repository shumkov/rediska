<?php

class Rediska_Zend_Session_ResourceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $rediska = new Rediska();
        $rediska->flushDb();

        Rediska_Manager::removeAll();
    }

    public function testDefaultWithoutInstance()
    {
        $this->setExpectedException('Zend_Session_SaveHandler_Exception', "You must instance '" . Rediska::DEFAULT_NAME . "' Rediska before or use 'rediska' option for specify another");

        $application = new Zend_Application('tests', dirname(__FILE__) . '/application.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('session');

        Zend_Session::getSaveHandler()->getRediska();
    }

    public function testDefault()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application2.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('session');

        $rediska = Zend_Session::getSaveHandler()->getRediska();

        $this->assertEquals('default', $rediska->getOption('name'));
    }

    public function testNamedInstance()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application3.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('session');

        $rediska = Zend_Session::getSaveHandler()->getRediska();

        $this->assertEquals('test', $rediska->getOption('name'));

        $this->assertFalse(Rediska_Manager::has('default'));
    }

    public function testNewInstance()
    {
        $application = new Zend_Application('tests', dirname(__FILE__) . '/application4.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('session');

        $rediska = Zend_Session::getSaveHandler()->getRediska();

        $this->assertEquals('default', $rediska->getOption('name'));

        $this->assertEquals(array(), Rediska_Manager::getAll());
    }
}