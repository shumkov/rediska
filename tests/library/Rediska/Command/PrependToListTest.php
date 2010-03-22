<?php

class Rediska_Command_PrependToListTest extends Rediska_TestCase
{
	public function testReturnTrue()
	{
		$reply = $this->rediska->prependToList('test', 'aaa');
        $this->assertTrue($reply);
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
}