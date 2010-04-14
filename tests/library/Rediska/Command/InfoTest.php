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

        foreach($this->rediska->getConnections() as $connection) {
            $this->assertArrayHasKey($connection->getAlias(), $info);
            $this->assertArrayHasKey('redis_version', $info[$connection->getAlias()]);
        }
    }
}