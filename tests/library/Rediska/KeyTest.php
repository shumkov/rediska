<?php

require_once 'Rediska/Key.php';

class Rediska_KeyTest extends Rediska_TestCase
{
	/**
     * @var Rediska_Key
     */
    private $key;

    protected function setUp()
    {
        parent::setUp();
        $this->key = new Rediska_Key('test');
    }

    public function testSetValue()
    {
    	$reply = $this->key->setValue(123);
    	$this->assertTrue($reply);
    	
    	$value = $this->key->getRediska()->get($this->key->getName());
    	$this->assertEquals(123, $value);
    }

    public function testGetValue()
    {
    	$this->key->getRediska()->set($this->key->getName(), 123);

        $value = $this->key->getValue();
        $this->assertEquals(123, $value);

        $this->assertEquals('123', "{$this->key}");
    }

    public function testIncrement()
    {
    	$this->key->getRediska()->set($this->key->getName(), 123);

    	$reply = $this->key->increment(2);
    	$this->assertEquals(125, $reply);

    	$reply = $this->key->getRediska()->get($this->key->getName());
    	$this->assertEquals(125, $reply);
    }

    public function testDecrement()
    {
    	$this->key->getRediska()->set($this->key->getName(), 123);

        $reply = $this->key->decrement(2);
        $this->assertEquals(121, $reply);

        $reply = $this->key->getRediska()->get($this->key->getName());
        $this->assertEquals(121, $reply);
    }

    public function testGetOrSetValue()
    {
    	$provider = new BasicKeyDataProvider();

    	$value = $this->key->getOrSetValue($provider)->data;
    	$this->assertEquals(123, $value);
    	
    	$reply = $this->key->isExists();
        $this->assertTrue($reply);
        
        $this->assertEquals(123, $this->key->getValue());
    	
    	$value = $this->key->getOrSetValue($provider)->getOtherDataForTest();
        $this->assertEquals(123, $value);

    	$this->key->delete();

    	$value = $this->key->getOrSetValue($provider)->getData();
        $this->assertEquals(123, $value);
        
        $reply = $this->key->isExists();
        $this->assertTrue($reply);
        
        $this->assertEquals(123, $this->key->getValue());

    	$getOrSetValueObject = $this->key->getOrSetValue($provider);
    	$this->assertEquals(123, "{$getOrSetValueObject}");
    }
}

class BasicKeyDataProvider
{
    public $data = 123;

    public function getData()
    {
        return $this->data;
    }
    
    public function getOtherDataForTest()
    {
        return 456;
    }

    public function __toString()
    {
        return (string)$this->getData();
    }
}