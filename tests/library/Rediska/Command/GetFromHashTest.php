<?php

class Rediska_Command_GetFromHashTest extends Rediska_TestCase
{ 
    public function testExistsNotExists()
    {
        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals(null, $reply);
    }

    public function testExists()
    {
        $data = array('a' => array(1, 5), 'b' => 2, 3 => 'c');

        $this->rediska->setToHash('test', $data);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals(array(1, 5), $reply);

        $reply = $this->rediska->getFromHash('test', 3);
        $this->assertEquals('c', $reply);
    }
}