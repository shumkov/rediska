<?php
/**
 * @group Info
 */
class Rediska_Command_InfoTest extends Rediska_TestCase
{
    public function testInfo()
    {
        $info = $this->rediska->info();
        $this->assertInstanceOf('Rediska_Info', $info);
        $this->assertNotNull($info->redis_version);
    }
    
    public function testInfoWithManyServers()
    {
        $this->_addSecondServerOrSkipTest();

        $info = $this->rediska->info();
        $this->assertInstanceOf('Rediska_Info', $info);

        foreach($this->rediska->getConnections() as $connection) {
            $this->assertArrayHasKey($connection->getAlias(), $info);
            $this->assertNotNull($info[$connection->getAlias()]->redis_version);
        }
    }
}
