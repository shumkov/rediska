<?php

class Rediska_Command_InfoTest extends Rediska_TestCase
{
    public function testInfo()
    {
        $info = $this->rediska->info();
        $this->assertTrue(is_array($info));
        $this->assertArrayHasKey('redis_version', $info);
    }
    
    public function testInfoWithManyServers()
    {
        $this->_addSecondServerOrSkipTest();

        $info = $this->rediska->info();
        $this->assertTrue(is_array($info));
        $this->assertArrayHasKey(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT, $info);
        $this->assertArrayHasKey('redis_version', $info[REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT]);
    }
}