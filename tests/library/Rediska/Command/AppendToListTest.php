<?php

class Rediska_Command_AppendToListTest extends Rediska_TestCase
{
	public function testReturnTrue()
	{
		$reply = $this->rediska->appendToList('test', 'aaa');
        $this->assertTrue($reply);
	}
	
	public function testAppended()
	{
		$this->rediska->appendToList('test', 'aaa');
		$this->rediska->appendToList('test', 'bbb');
        
		$reply = $this->rediska->getFromList('test', 1);
        $this->assertEquals('bbb', $reply);
        
         $reply = $this->rediska->getListLength('test');
        $this->assertEquals(2, $reply);
	}
}