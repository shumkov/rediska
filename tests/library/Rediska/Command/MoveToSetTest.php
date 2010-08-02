<?php

class Rediska_Command_MoveToSetTest extends Rediska_TestCase
{
    public function testReturnTrue()
    {
        $this->rediska->addToSet('test', 'aaa');
        $reply = $this->rediska->moveToSet('test', 'test1', 'aaa');
        $this->assertTrue($reply);
    }
    
    public function testReturnTrueWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testReturnTrue();
    }

    public function testEmptySetReturnFalse()
    {
        $reply = $this->rediska->moveToSet('test', 'test1', 'aaa');
        $this->assertFalse($reply);
    }
    
    public function testEmptySetReturnFalseWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testEmptySetReturnFalse();
    }

    public function testSetIsEmpty()
    {
        $this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');

        $this->rediska->moveToSet('test', 'test1', 'aaa');
        $this->rediska->moveToSet('test', 'test1', 'bbb');

        $values = $this->rediska->getSet('test');
        $this->assertEquals(array(), $values);
    }
    
    public function testSetIsEmptyWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testSetIsEmpty();
    }

    public function testDestinationSetHasMembers()
    {
        $this->rediska->addToSet('test', 'aaa');
        $this->rediska->addToSet('test', 'bbb');
        
        $this->rediska->moveToSet('test', 'test1', 'aaa');
        $this->rediska->moveToSet('test', 'test1', 'bbb');
        
        $values = $this->rediska->getSet('test1');
        $this->assertContains('aaa', $values);
        $this->assertContains('bbb', $values);
    }
    
    public function testDestinationSetHasMembersWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testDestinationSetHasMembers();
    }
}