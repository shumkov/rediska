<?php

class Rediska_Command_GetHashValuesTest extends Rediska_TestCase
{ 
    public function testGetHashValuesNotExists()
    {
        $hash = $this->rediska->getHashValues('test');
        $this->assertEquals(array(), $hash);
    }

    public function testGetHashValues()
    {
        $data = array('a' => array(1, 5), 'b' => 2, 3 => 'c');

        $this->rediska->setToHash('test', $data);
        $dataFromHash = $this->rediska->getHashValues('test');
        $this->assertEquals(array_values($data), $dataFromHash);
    }
}