<?php

class Rediska_Command_SaveTest extends Rediska_TestCase
{
    public function testSave()
    {       
        $reply = $this->rediska->save();
        $this->assertTrue($reply);

        $timestamp = $this->rediska->getLastSaveTime();
        $this->assertTrue($timestamp > time() - 1);
    }
}