<?php

class Rediska_Command_DeleteFromListTest extends Rediska_TestCase
{
    public function testDeleteNotPresentMember()
    {
        $reply = $this->rediska->deleteFromList('test', 'bbb');
        $this->assertEquals(0, $reply);
    }

    public function testDeleteMemberAndReturnNumberOfDeleted()
    {
        $this->rediska->appendToList('test', 'bbb');

        $reply = $this->rediska->deleteFromList('test', 'bbb');
        $this->assertEquals(1, $reply);
    }
 
    public function testMemberIsDeletedFromSet()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');

        $this->rediska->deleteFromList('test', 'bbb');

        $values = $this->rediska->getList('test');

        $this->assertContains('aaa', $values);
        $this->assertNotContains('bbb', $values);
        $this->assertEquals(1, count($values));
    }
}