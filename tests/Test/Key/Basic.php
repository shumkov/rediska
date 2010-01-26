<?php

require_once 'Rediska/Key.php';

class Test_Key_Basic extends PHPUnit_Framework_TestCase
{
	/**
     * @var Rediska_Key
     */
    private $key;
    
    /**
     * @var Rediska
     */
    private $rediska;

    protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Tests_', 'servers' => array(array('host' => REDISKA_HOST, 'port' => REDISKA_PORT))));
        $this->key = new Rediska_Key('test');
    }

    protected function tearDown()
    {
        $this->rediska->flushDb();
        $this->rediska = null;
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
    
    public function testGetOrSetValue()
    {
    	require_once REDISKA_TESTS_PATH . '/classes/BasicKeyDataProvider.php';
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

    public function testSetExpireOnConstruct()
    {
    	$key = new Rediska_Key('test', 2);

    	$reply = $key->setValue(123);
    	$this->assertTrue($reply);

    	$value = $key->getValue();
    	$this->assertEquals(123, $value);

    	sleep(3);

    	$value = $key->getValue();
        $this->assertNull($value);
    }
}