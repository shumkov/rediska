<?php

class Rediska_Command_DeleteFromHashTest extends Rediska_TestCase
{
    public function testDeleteFromHashNotExists()
    {
        $reply = $this->rediska->deleteFromHash('test', 'a');
        $this->assertEquals(false, $reply);
    }

    public function testDelete()
    {
        $data = array('a' => array(1, 5), 'b' => 2, 3 => 'c');

        $this->rediska->setToHash('test', $data);
        
        $reply = $this->rediska->deleteFromHash('test', 'a');
        $this->assertEquals(true, $reply);

        unset($data['a']);

        $dataFromHash = $this->rediska->getHash('test');
        $this->assertEquals($data, $dataFromHash);
    }
}