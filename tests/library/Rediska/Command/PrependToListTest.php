<?php

class Rediska_Command_PrependToListTest extends Rediska_TestCase
{
    public function testReturnTrue()
    {
        $this->rediska->prependToList('test', 'aaa');
        $reply = $this->rediska->prependToList('test', 'bbb');
        $this->assertEquals(2, $reply);
    }

    public function testPrepended()
    {
        $this->rediska->prependToList('test', 'aaa');
        $this->rediska->prependToList('test', 'bbb');

        $reply = $this->rediska->getFromList('test', 0);
        $this->assertEquals('bbb', $reply);

        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(2, $reply);
    }

    public function testCreateIfNotExists()
    {
        $retry = $this->rediska->prependToList('test', 'aaa', false);
        $this->assertEquals(0, $retry);

        $reply = $this->rediska->getListLength('test');
        $this->assertEquals(0, $reply);
    }
}