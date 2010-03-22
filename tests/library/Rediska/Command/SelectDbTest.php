<?php

class Rediska_Command_SelectDbTest extends Rediska_TestCase
{
    public function testSelect()
    {
        $rediska = new Rediska(array('servers' => array(array('host' => REDISKA_HOST, 'port' => REDISKA_PORT, 'db' => 2))));
        $rediska->set('a', 123);
        
        $rediska->selectDb(1);
        
        $reply = $rediska->get('a');
        $this->assertNull($reply);
        
        $rediska->selectDb(2);
        
        $reply = $rediska->get('a');
        $this->assertEquals(123, $reply);
    }
}