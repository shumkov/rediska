<?php

class Rediska_Command_SelectDbTest extends Rediska_TestCase
{
    public function testSelect()
    {
        $config = $GLOBALS['rediskaConfigs'][0];
        $config['servers'][0]['db'] = 2;
        $rediska = new Rediska($config);

        $rediska->set('a', 123);
        
        $rediska->selectDb(1);
        
        $reply = $rediska->get('a');
        $this->assertNull($reply);
        
        $rediska->selectDb(2);
        
        $reply = $rediska->get('a');
        $this->assertEquals(123, $reply);
    }
}