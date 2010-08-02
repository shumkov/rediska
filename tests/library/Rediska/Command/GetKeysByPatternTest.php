<?php

class Rediska_Command_GetKeysByPatternTest extends Rediska_TestCase
{
    public function testNotMatchedKeysReturnEmptyArray()
    {
        $reply = $this->rediska->getKeysByPattern('h*llo');
        $this->assertEquals(array(), $reply);
    }
    
    public function testNotMatchedKeysReturnEmptyArrayWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testNotMatchedKeysReturnEmptyArray();
    }

    public function testPatterns()
    {
        $keys = array('hello', 'hallo', 'hillo', 'hiiiiillo');

        foreach($keys as $index => $key) {
            $this->rediska->set($key, $index);
        }

        $reply = $this->rediska->getKeysByPattern('h*llo');
        foreach($reply as $key) {
            $this->assertContains($key, $keys);
        }

        $someKeys = $keys;
        unset($someKeys[3]);

        $reply = $this->rediska->getKeysByPattern('h?llo');
        foreach($reply as $key) {
            $this->assertContains($key, $someKeys);
        }

        unset($someKeys[2]);
        $reply = $this->rediska->getKeysByPattern('h[ea]llo');
        foreach($reply as $key) {
            $this->assertContains($key, $someKeys);
        }
    }

    public function testPatternsWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testPatterns();
    }
}