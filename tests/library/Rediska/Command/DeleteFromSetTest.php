<?php

class Rediska_Command_DeleteFromSetTest extends Rediska_TestCase
{
    public function testDeleteNotPresentMember()
    {
        $reply = $this->rediska->deleteFromSet('test', 'bbb');
        $this->assertFalse($reply);
    }

    public function testDeleteMemberAndReturnTrue()
    {
        $this->rediska->addToSet('test', 'bbb');

        $reply = $this->rediska->deleteFromSet('test', 'bbb');
        $this->assertTrue($reply);
    }
 
    public function testMemberIsDeletedFromSet()
    {
        $this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');

        $this->rediska->deleteFromSet('test', 'bbb');

        $values = $this->rediska->getSet('test');

        $this->assertContains('aaa', $values);
        $this->assertNotContains('bbb', $values);
        $this->assertEquals(1, count($values));
    }
}