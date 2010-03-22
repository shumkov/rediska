<?php

class Rediska_Command_GetTest extends Rediska_TestCase
{
    public function testGet()
    {
        $this->rediska->set('a', 'b');
        $reply = $this->rediska->get('a');
        $this->assertEquals('b', $reply);

        $reply = $this->rediska->set('a', 0);
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertEquals(0, $reply);

        $reply = $this->rediska->set('a', 1);
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertEquals(1, $reply);

        $reply = $this->rediska->set('a', '');
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertEquals('', $reply);

        $reply = $this->rediska->set('a', null);
        $this->assertTrue($reply);
        $reply = $this->rediska->get('a');
        $this->assertNull($reply);  
    }

    public function testGetWithNotPresentKey()
    {
        $reply = $this->rediska->get('a');
        $this->assertNull($reply);
    }

    public function testGetWithArrayOfKeyNames()
    {
        $keyNames = array('a', 'b', 'c', 'd');
        $notExistsKeyName = array('i', 'j');

        foreach($keyNames as $keyName) {
            $this->rediska->set($keyName, "value of $keyName");
        }       

        $values = $this->rediska->get(array_merge($keyNames, $notExistsKeyName));

        foreach($keyNames as $keyName) {
            $this->assertArrayHasKey($keyName, $values);
            $this->assertEquals("value of $keyName", $values[$keyName]);
        }

        foreach($notExistsKeyName as $keyName) {
            $this->assertArrayNotHasKey($keyName, $values);
        }

        // Test order
        $index = 0;
        foreach($values as $key => $value) {
            $this->assertEquals($key, $keyNames[$index]);
            $index++;
        }
    }
    
    public function testGetWithArrayOfKeyNamesWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testGetWithArrayOfKeyNames();
    }
}