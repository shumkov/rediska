<?php

class Rediska_Options_RediskaInstanceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Rediska_Manager::removeAll();
    }

    public function testGetNotPresentDefaultRediska()
    {
        $this->setExpectedException('RediskaInstanceTestException');
        Rediska_Options_RediskaInstance::getRediskaInstance(Rediska::DEFAULT_NAME, 'RediskaInstanceTestException');
    }

    public function testGetDefaultRediska()
    {
        $r = new Rediska();

        $r2 = Rediska_Options_RediskaInstance::getRediskaInstance(Rediska::DEFAULT_NAME);

        $this->assertEquals($r, $r2);
    }

    public function testGetAnotherNotPresentRediska()
    {
        $this->setExpectedException('Rediska_Exception');
        Rediska_Options_RediskaInstance::getRediskaInstance('notPresent');
    }

    public function testGetAnotherRediska()
    {
        $r = new Rediska(array('name' => 'another'));

        $r2 = Rediska_Options_RediskaInstance::getRediskaInstance('another');

        $this->assertEquals($r, $r2);
    }

    public function testGetRediskaByOptions()
    {
        $r = Rediska_Options_RediskaInstance::getRediskaInstance(array('namespace' => 'byOptions'));

        $this->assertEquals('byOptions', $r->getOption('namespace'));
        $this->assertEquals(array(), Rediska_Manager::getAll());
    }
}

class RediskaInstanceTestException extends Exception {}