<?php

class Rediska_Command_GetSetLengthTest extends Rediska_TestCase
{
	public function testEmptySetReturnFalse()
	{
		$reply = $this->rediska->getSetLength('test');
        $this->assertFalse($reply);
	}

    public function testReturnLength()
    {
        $this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');
        $this->rediska->addToSet('test', 'ccc');

        $reply = $this->rediska->getSetLength('test');
        $this->assertEquals(3, $reply);
    }
}