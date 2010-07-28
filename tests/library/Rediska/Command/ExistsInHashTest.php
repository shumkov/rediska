<?php

class Rediska_Command_ExistsInHashTest extends Rediska_TestCase
{
    public function testExistsNotExists()
    {
        $reply = $this->rediska->existsInHash('test', 'a');
        $this->assertEquals(false, $reply);
    }

    public function testExists()
    {
        $data = array('a' => array(1, 5), 'b' => 2, 3 => 'c');

        $this->rediska->setToHash('test', $data);

        $reply = $this->rediska->existsInHash('test', 'a');
        $this->assertEquals(true, $reply);

        $reply = $this->rediska->existsInHash('test', 'c');
        $this->assertEquals(false, $reply);
    }
}