<?php

class Rediska_Command_GetSetTest extends Rediska_TestCase
{
    public function testEmptySetReturnEmptySet()
    {
        $values = $this->rediska->getSet('test');
        $this->assertEquals(array(), $values);
    }

    public function testGetMembers()
    {
        $this->rediska->addToSet('test', 1);
        $this->rediska->addToSet('test', 2);
        $this->rediska->addToSet('test', 3);

        $values = $this->rediska->getSet('test');
        $this->assertTrue(in_array(1, $values));
        $this->assertTrue(in_array(2, $values));
        $this->assertTrue(in_array(3, $values));
        $this->assertFalse(in_array('xxxx', $values));
    }
}