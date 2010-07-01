<?php

class Rediska_SerializerTest extends Rediska_TestCase

    public function testSetSerializerException()
    {
        $this->setExpectedException('Rediska_Exception');

        $this->rediska->setSerializer(array('wrong', 'serializer'));
    }

    public function testSetUnSerializerException()
    {
        $this->setExpectedException('Rediska_Exception');
        
        $this->rediska->setUnSerializer(array('wrong', 'serializer'));
    }

    public function testSetAndGetObjectWithDefaultSerializer()
    {
        $this->_setAndGetObject();
    }

    public function testSetAndGetObjectWithPersonalSerializer()
    {
        $this->rediska->setOptions(array(
            'serializer'   => array('RediskaTest', 'serializer'),
            'unserializer' => array('RediskaTest', 'unserializer')
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