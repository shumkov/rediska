<?php

class Test_Serializer extends PHPUnit_Framework_TestCase
{
	/**
     * @var Rediska
     */
    private $rediska;
	
	protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Tests_'));
    }

    protected function tearDown()
    {
        $this->rediska->flushDb();
        $this->rediska = null;
    }

    /**
     * @expectedException Rediska_Exception
     */
    public function testSetSerializerException()
    {
        $this->rediska->setSerializer(array('wrong', 'serializer'));
    }

    /**
     * @expectedException Rediska_Exception
     */
    public function testsetUnSerializerException()
    {
        $this->rediska->setUnSerializer(array('wrong', 'serializer'));
    }

    public function testSetAndGetObjectWithDefaultSerializer()
    {
        $this->_setAndGetObject();
    }

    public function testSetAndGetObjectWithPersonalSerializer()
    {
    	$this->rediska->setOptions(array(
            'serializer' => array('Test_Serializer', 'serializer'),
            'unserializer' => array('Test_Serializer', 'unserializer')
    	));

    	$this->_setAndGetObject();
    }

    public static function serializer($value)
    {
    	return serialize($value);
    }

    public static function unserializer($value)
    {
    	return unserialize($value);
    }

    protected function _setAndGetObject()
    {
        $object = new stdClass;
        $object->a = 'b';

        $this->rediska->set('a', $object);

        $replyObject = $this->rediska->get('a');
        $this->assertObjectHasAttribute('a', $replyObject);
        $this->assertEquals($object->a, $replyObject->a);
    }
}