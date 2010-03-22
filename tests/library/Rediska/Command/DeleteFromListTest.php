<?php

class Rediska_Command_DeleteFromListTest extends Rediska_TestCase
{
    public function testDeleteNotPresentMember()
    {
        $reply = $this->rediska->deleteFromList('test', 'bbb');
        $this->assertFalse($reply);
    }

    public function testDeleteMemberAndReturnTrue()
    {
        $this->rediska->addToSet('test', 'bbb');

        $reply = $this->rediska->deleteFromList('test', 'bbb');
        $this->assertTrue($reply);
    }
 
    public function testMemberIsDeletedFromSet()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $this->rediska->deleteFromList('test', 'ccc');

        $values = $this->rediska->getList('test');

        $this->assertContains('aaa', $values);
        $this->assertNotContains('bbb', $values);
        $this->assertEquals(1, count($values));
    }
}