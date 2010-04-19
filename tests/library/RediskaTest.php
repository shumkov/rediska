<?php

class RediskaTest extends Rediska_TestCase
{
    public function testOn()
    {   
        list($firstServer) = $this->rediska->getConnections();

        $this->rediska->on($firstServer)->set('aaa', 'value');
        $this->assertEquals($this->rediska->on($firstServer->getAlias())->get('aaa'), 'value');
    }

    public function testOnTwoServers()
    {
        $this->_addSecondServerOrSkipTest();

        list($firstServer, $secondServer) = $this->rediska->getConnections();

        $this->rediska->on($firstServer)->set('aaa', 'value');
        $this->rediska->on($secondServer)->set('bbb', 'value');

        $this->assertEquals($this->rediska->on($firstServer->getAlias())->get('aaa'), 'value');
        $this->assertNull($this->rediska->on($secondServer->getAlias())->get('aaa'));

        $this->assertNull($this->rediska->on($firstServer->getAlias())->get('bbb'));
        $this->assertEquals($this->rediska->on($secondServer->getAlias())->get('bbb'), 'value');
    }

    public function testSetSerializerException()
    {
        $this->setExpectedException('Rediska_Exception');

        $this->rediska->setSerializer(array('wrong', 'serializer'));
    }

    public function testsetUnSerializerException()
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
            'serializer' => array('RediskaTest', 'serializer'),
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
}