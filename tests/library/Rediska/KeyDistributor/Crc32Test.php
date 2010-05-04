<?php

class Rediska_KeyDistributor_Crc32Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rediska_KeyDistributor_ConsistentHashing
     */
    private $keyDistributor;

    protected function setUp()
    {
        $this->keyDistributor = new Rediska_KeyDistributor_Crc32();
    }

    protected function tearDown()
    {
        $this->keyDistributor = null;
    }

    public function testAddConnection()
    {
        $return = $this->keyDistributor->addConnection('127.0.0.1:6379');
        $this->assertEquals($this->keyDistributor, $return);

        $connection = $this->keyDistributor->getConnectionByKeyName('test');
        $this->assertEquals('127.0.0.1:6379', $connection);
    }

    /**
     * @expectedException Rediska_KeyDistributor_Exception
     */
    public function testAddDuplicateConnection()
    {
        $this->keyDistributor->addConnection('127.0.0.1:6379');
        $this->keyDistributor->addConnection('127.0.0.1:6379');
    }

    public function testRemoveConnection()
    {
        $this->keyDistributor->addConnection('127.0.0.1:6379');
        $return = $this->keyDistributor->removeConnection('127.0.0.1:6379');
        $this->assertEquals($this->keyDistributor, $return);
        $this->setExpectedException('Rediska_KeyDistributor_Exception');
        $this->keyDistributor->getConnectionByKeyName('test');
    }

    /**
     * @expectedException Rediska_KeyDistributor_Exception
     */
    public function testRemoveUnknownConnection()
    {
        $this->keyDistributor->removeConnection('127.0.0.1:6379');
    }

    public function testGetConnectionByKeyName()
    {
        $this->keyDistributor->addConnection('127.0.0.1:6379');
        $this->keyDistributor->addConnection('127.0.0.1:6380');
        $this->keyDistributor->addConnection('127.0.0.1:6381');
        $this->keyDistributor->addConnection('127.0.0.1:6382');

        $connections = array();
        for ($index = 0; $index < 5; $index++) {
            $connection = $this->keyDistributor->getConnectionByKeyName("key_$index");
            $connections[$index] = $connection;
        }

        for ($index = 0; $index < 5; $index++) {
            $connection = $this->keyDistributor->getConnectionByKeyName("key_$index");
            $this->assertEquals($connections[$index], $connection);
        }
    }
}