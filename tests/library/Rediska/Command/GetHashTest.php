<?php

class Rediska_Command_GetHashTest extends Rediska_TestCase
{
    public function testGetHashNotExists()
    {
        $hash = $this->rediska->getHash('test');
        $this->assertEquals(array(), $hash);
    }

    public function testGetHash()
    {
        $data = array('a' => array(1, 5), 'b' => 2, 3 => 'c');

        $this->rediska->setToHash('test', $data);
        $dataFromHash = $this->rediska->getHash('test');
        $this->assertEquals($data, $dataFromHash);
    }
}