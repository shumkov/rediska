<?php

class RediskaTest extends Rediska_TestCase
{
    public function testOn()
    {
        $this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->set('aaa', 'value');
        $this->assertEquals($this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->get('aaa'), 'value');
    }

    public function testOnTwoServers()
    {
        $this->_addSecondServerOrSkipTest();

        $this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->set('aaa', 'value');
        $this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->set('bbb', 'value');

        $this->assertEquals($this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->get('aaa'), 'value');
        $this->assertNull($this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->get('aaa'));

        $this->assertNull($this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->get('bbb'));
        $this->assertEquals($this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->get('bbb'), 'value');
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