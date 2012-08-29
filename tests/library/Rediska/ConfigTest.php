<?php

class Rediska_ConfigTest extends Rediska_TestCase
{
    private $config;

    public function setUp()
    {
        parent::setUp();

        list($connection) = $this->rediska->getConnections();

        $this->config = $this->rediska->config($connection);
    }

    public function testGet()
    {
        $reply = $this->config->get('maxm');
        $this->assertNull($reply);

        $reply = $this->config->get('maxmemory');
        $this->assertTrue(!is_array($reply));
        $this->assertNotNull($reply);

        $reply = $this->config->get('maxmemor*');
        $this->assertTrue(is_array($reply));
        $keys = array_keys($reply);
        $this->assertEquals('maxmemory', $keys[0]);
    }

    public function testMagicGet()
    {
        $reply = $this->config->maxmemory;
        $this->assertTrue(!is_array($reply));
        $this->assertNotNull($reply);
    }

    public function testArrayMagicGet()
    {
        $reply = $this->config['maxmemory'];
        $this->assertTrue(!is_array($reply));
        $this->assertNotNull($reply);

        $reply = $this->config['maxmemor*'];
        $this->assertTrue(is_array($reply));
        $keys = array_keys($reply);
        $this->assertEquals('maxmemory', $keys[0]);
    }

    public function testSet()
    {
        $maxMemory = $this->config->get('maxmemory');
        $this->assertNotNull($maxMemory);

        $reply = $this->config->set('maxmemory', 100000);
        $this->assertEquals($this->config, $reply);

        $reply = $this->config->get('maxmemory');
        $this->assertEquals(100000, $reply);

        $this->config->set('maxmemory', $maxMemory);
    }

    public function testMagicSet()
    {
        $maxMemory = $this->config->get('maxmemory');
        $this->assertNotNull($maxMemory);

        $this->config->maxmemory = 100000;

        $reply = $this->config->get('maxmemory');
        $this->assertEquals(100000, $reply);

        $this->config->set('maxmemory', $maxMemory);
    }

    public function testArrayMagicSet()
    {
        $maxMemory = $this->config->get('maxmemory');
        $this->assertNotNull($maxMemory);

        $this->config['maxmemory'] = 100000;

        $reply = $this->config->get('maxmemory');
        $this->assertEquals(100000, $reply);

        $this->config->set('maxmemory', $maxMemory);
    }

    public function testToArray()
    {
        $reply = $this->config->toArray();
        $this->assertTrue(is_array($reply));
        $this->assertGreaterThan(1, count($reply));
        $keys = array_keys($reply);
        
        $this->assertTrue(in_array('maxmemory', $keys));
    }

    public function testCount()
    {
        $reply = count($this->config);
        $this->assertGreaterThan(1, $reply);
    }

    public function testIterator()
    {
        $reply = array();
        foreach($this->config as $param => $value) {
            $reply[$param] = $value;
        }
        $this->assertEquals($this->config->toArray(), $reply);
    }
}