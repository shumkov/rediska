<?php

class Rediska_Command_AddToSetTest extends Rediska_TestCase
{
    public function testReturnTrue()
    {
        $value = $this->rediska->addToSet('test', 'aaa');
        $this->assertTrue($value);
    }

    public function testAddMembers()
    {
        $this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');

        $values = $this->rediska->getSet('test');
        $this->assertContains('aaa', $values);
        $this->assertContains('bbb', $values);

        $this->assertEquals(2, count($values));
    }
}