<?php

class Rediska_Command_PopFromListTest extends Rediska_TestCase
{
    public function testPopFromList()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');

        $reply = $this->rediska->popFromList('test');
        $this->assertEquals('ccc', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);

        $reply = $this->rediska->popFromList('test');
        $this->assertEquals('bbb', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa'), $reply);
    }
    
    public function testPopFromListAndPushToAnother()
    {
        $this->rediska->appendToList('test', 'aaa');
        $this->rediska->appendToList('test', 'bbb');
        $this->rediska->appendToList('test', 'ccc');

        $reply = $this->rediska->popFromList('test', 'test2');
        $this->assertEquals('ccc', $reply);

        $reply = $this->rediska->getList('test');
        $this->assertEquals(array('aaa', 'bbb'), $reply);

        $reply = $this->rediska->getList('test2');
        $this->assertEquals(array('ccc'), $reply);
    }

    public function testPopFromListAndPushToAnotherWithManyConnection()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testPopFromListAndPushToAnother();
    }
}