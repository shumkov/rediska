<?php
/**
 * @group Ping
 */
class Rediska_Command_PingTest extends Rediska_TestCase
{
    public function testPing()
    {
        $ping = $this->rediska->ping();
        $this->assertEquals('PONG', $ping);
    }
    
    public function testInfoWithManyServers()
    {
        $this->_addSecondServerOrSkipTest();

        $ping = $this->rediska->ping();

        foreach ($ping as $pong) {
            $this->assertEquals('PONG', $pong);
        }
    }
}
