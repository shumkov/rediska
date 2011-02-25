<?php

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

    public function testSetAndExpire()
    {
        $this->key->setAndExpire(123, 1);
        $reply = $this->key->getRediska()->get($this->key->getName());
        $this->assertEquals(123, $reply);
        sleep(2);
        $reply = $this->key->getRediska()->get($this->key->getName());
        $this->assertNull($reply);
    }

    public function testAppend()
    {
        $this->key->setValue('abc');

        $reply = $this->key->append('abc');
        $this->assertEquals(6, $reply);

        $reply = $this->key->getRediska()->get($this->key->getName());
        $this->assertEquals('abcabc', $reply);
    }

    public function testSetBit()
    {
        $this->key->setBit(0, 1);
        $this->key->setBit(4, 1);

        $reply = $this->rediska->getBit($this->key->getName(), 0);
        $this->assertEquals(1, $reply);

        $reply = $this->rediska->getBit($this->key->getName(), 1);
        $this->assertEquals(0, $reply);

        $reply = $this->rediska->getBit($this->key->getName(), 4);
        $this->assertEquals(1, $reply);
    }

    public function testGetBit()
    {
        $this->rediska->setBit($this->key->getName(), 2, 1);

        $reply = $this->key->getBit(2);
        $this->assertEquals(1, $reply);
    }

    public function testSetRange()
    {
        $this->rediska->set($this->key->getName(), 'abc');

        $reply = $this->key->setRange(2, 'z');
        $this->assertEquals(3, $reply);

        $reply = $this->rediska->get($this->key->getName());
        $this->assertEquals('abz', $reply);
    }

    public function testGetRange()
    {
        $this->rediska->set($this->key->getName(), 'abc');

        $reply = $this->key->getRange(0);
        $this->assertEquals('abc', $reply);

        $reply = $this->key->getRange(0, 1);
        $this->assertEquals('ab', $reply);
    }

    public function testGetLength()
    {
        $this->rediska->set($this->key->getName(), 'abc');

        $reply = $this->key->getLength('test');
        $this->assertEquals(3, $reply);
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

    public function testCount()
    {
        $this->rediska->set($this->key->getName(), 'abc');

        $reply = count($this->key);
        $this->assertEquals(3, $reply);
    }

    public function testOffsetExists()
    {
        $this->rediska->setBit($this->key->getName(), 1, 1);

        $reply = isset($this->key[0]);
        $this->assertFalse($reply);

        $reply = isset($this->key[1]);
        $this->assertTrue($reply);
    }

    public function testOffsetGet()
    {
        $this->rediska->setBit($this->key->getName(), 1, 1);

        $reply = $this->key[0];
        $this->assertEquals(0, $reply);

        $reply = $this->key[1];
        $this->assertEquals(1, $reply);
    }

    public function testOffsetSet()
    {
        $this->key[1] = 1;

        $reply = $this->rediska->getBit($this->key->getName(), 0);
        $this->assertEquals(0, $reply);

        $reply = $this->rediska->getBit($this->key->getName(), 1);
        $this->assertEquals(1, $reply);
    }

    public function testOffsetUnset()
    {
        $this->rediska->setBit($this->key->getName(), 1, 1);

        $reply = $this->rediska->getBit($this->key->getName(), 1);
        $this->assertEquals(1, $reply);

        unset($this->key[1]);

        $reply = $this->rediska->getBit($this->key->getName(), 1);
        $this->assertEquals(0, $reply);
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