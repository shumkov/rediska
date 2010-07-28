<?php

class Rediska_Command_GetHashLengthTest extends Rediska_TestCase
{
    public function testGetLengthFromNotExists()
    {
        $fields = $this->rediska->getHashLength('test');
        $this->assertEquals(0, $fields);
    }

    public function testGetLength()
    {
        $this->rediska->setToHash('test', array('a' => 1, 'b' => 2, 3 => 'c'));
        $length = $this->rediska->getHashLength('test');
        $this->assertEquals(3, $length);
    }
}