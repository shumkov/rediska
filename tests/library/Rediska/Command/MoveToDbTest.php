<?php

class Rediska_Command_MoveToDbTest extends Rediska_TestCase
{
    public function testMoveToDb()
    {
        $value = $this->rediska->get('a');
        $this->assertNull($value);
        
        $this->rediska->selectDb(1);

        $this->rediska->set('a', 1);

        $reply = $this->rediska->moveToDb('a', 0);
        $this->assertTrue($reply);

        $this->rediska->selectDb(0);

        $value = $this->rediska->get('a');
        $this->assertEquals(1, $value);
    }
}