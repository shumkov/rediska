<?php

class Rediska_Command_GetRandomFromSetTest extends Rediska_TestCase
{
    public function testEmptySetReturnNull()
    {
        $value = $this->rediska->getRandomFromSet('test');
        $this->assertNull($value);
    }

    public function testGetRandomValue()
    {
        $this->rediska->addToSet('test', 'aaa');
        $value = $this->rediska->getRandomFromSet('test');
        $this->assertEquals('aaa', $value);
    }

    public function testPopRandomValue()
    {
        $this->rediska->addToSet('test', 'aaa');
        $this->rediska->getRandomFromSet('test', true);

        $values = $this->rediska->getSet('test');
        $this->assertEquals(array(), $values);
    }  
}