<?php

require_once 'Zend/Application.php';

class Rediska_Zend_Application_ResourceTest extends Rediska_TestCase
{
    public function testBootstrap()
    {
        set_include_path(implode(PATH_SEPARATOR, array(
            realpath(dirname(__FILE__) . '/../../../../../library/'),
            get_include_path(),
        )));

        $application = new Zend_Application('tests', dirname(__FILE__) . '/application.ini');
        $application->bootstrap()
                    ->getBootstrap()
                    ->getResource('rediska');

        $defaultNamespace = Rediska_Manager::get('default')->getOption('namespace');
        $this->assertEquals('defaultInstance', $defaultNamespace);

        $anotherNamespace = Rediska_Manager::get('another')->getOption('namespace');
        $this->assertEquals('anotherInstance', $anotherNamespace);
    }
}