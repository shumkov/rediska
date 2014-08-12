<?php

class Rediska_Connection_ExecTest extends Rediska_TestCase
{
    public function testWrite()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testRead()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testExecute()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testTransformMultiBulkCommand()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testReadResponseFromConnection()
    {
        $this->markTestIncomplete('Write me!');
    }

    public function testReadResponseFromConnectionReplyError()
    {
        try {
            $this->rediska->expire('key', '10 seconds');
        } catch(Rediska_Connection_Exec_Exception $e) {
            // ERR value is not an integer or out of range
            $this->assertStringStartsWith('ERR', $e->getMessage());
            return;
        }

        $this->fail('Rediska_Connection_Exec_Exception exception has not been raised');
    }

    public function testReadResponseFromConnectionReplyWrongTypeError()
    {
        $redisVersion = $this->rediska->getOption('redisVersion');

        if (version_compare($redisVersion, '2.8.0') === -1) {
            $this->markTestSkipped('Many errors are prefixed by a more specific error code from version 2.8');
        }

        try {
            $this->rediska->set('key', 'value1');
            $this->rediska->appendToList('key', 'value2');
        } catch (Rediska_Connection_Exec_Exception $e) {
            // WRONGTYPE Operation against a key holding the wrong kind of value
            $this->assertStringStartsWith('WRONGTYPE', $e->getMessage());
            return;
        }

        $this->fail('Rediska_Connection_Exec_Exception exception has not been raised');
    }
}