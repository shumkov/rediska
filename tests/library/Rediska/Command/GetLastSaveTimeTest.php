<?php

class Rediska_Command_GetLastSaveTimeTest extends Rediska_TestCase
{
    public function testGetLastSaveTime()
    {
        $timestamp = $this->rediska->getLastSaveTime();
        $this->assertTrue(is_numeric($timestamp));
    }

    public function testGetLastSaveTimeWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $timestamp = $this->rediska->getLastSaveTime();
        $this->assertTrue(is_array($timestamp));

        foreach($this->rediska->getConnections() as $connection) {
            $this->assertArrayHasKey($connection->getAlias(), $timestamp);
            $this->assertTrue(is_numeric($timestamp[$connection->getAlias()]));
        }
    }
}