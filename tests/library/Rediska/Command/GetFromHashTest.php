<?php

class Rediska_Command_GetFromHashTest extends Rediska_TestCase
{ 
    public function testGetNotExists()
    {
        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals(null, $reply);
    }

    public function testGet()
    {
        $data = array('a' => array(1, 5), 'b' => 2, 3 => 'c');

        $this->rediska->setToHash('test', $data);

        $reply = $this->rediska->getFromHash('test', 'a');
        $this->assertEquals(array(1, 5), $reply);

        $reply = $this->rediska->getFromHash('test', 3);
        $this->assertEquals('c', $reply);

        $reply = $this->rediska->getFromHash('test', array('a', 'b', 3));
        $this->assertEquals($data, $reply);
    }
}