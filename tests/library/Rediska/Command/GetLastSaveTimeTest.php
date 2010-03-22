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
        $this->assertArrayHasKey(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT, $timestamp);
        $this->assertTrue(is_numeric($timestamp[REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT]));
    }
}