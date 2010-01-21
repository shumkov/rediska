<?php

class Test_Pipeline extends RediskaTestCase
{
	public function testPiplineInstance()
	{
		$instance = $this->rediska->pipeline();
		$this->assertType('Rediska_Pipeline', $instance);

		$instance = $this->rediska->pipeline()->set('a', 'b');
        $this->assertType('Rediska_Pipeline', $instance);
	}

    public function testPipelineIncapsulation()
    {
    	$this->rediska->pipeline()->set('a', 'b');
    	$reply = $this->rediska->get('a');
    	$this->assertNull($reply);
    }

    /**
     * @expectedException Rediska_Exception
     */
    public function testPipelineNothingToExecute()
    {
    	$this->rediska->pipeline()->execute();
    }

    public function testPipeline()
    {
    	$reply = $this->rediska->pipeline()
		    	               ->set('a', 123)
		    	               ->get('a')
		    	               ->addToSet('b', 123)
		    	               ->getSet('b')
		    	               ->execute();

		$this->assertEquals(array(true, 123, true, array(123)), $reply);
    }

    public function testPipelineWithMultipleConnections()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);
    	$this->testPipeline();
    }

    public function testPipelineWithSpecifiedConnection()
    {
    	$this->_addServerOrSkipTest('127.0.0.1', 6380);

    	$this->rediska->on('127.0.0.1:6380')->pipeline()
    	                                    ->set(1, 1)
    	                                    ->set(2, 2)
    	                                    ->on('127.0.0.1:6379')->set(3, 3)
    	                                    ->set(4, 4)
    	                                    ->execute();

        $this->assertEquals(1, $this->rediska->on('127.0.0.1:6380')->get(1));
        $this->assertEquals(2, $this->rediska->on('127.0.0.1:6380')->get(2));
        $this->assertEquals(3, $this->rediska->on('127.0.0.1:6379')->get(3));
        $this->assertEquals(4, $this->rediska->on('127.0.0.1:6380')->get(4));
    }
}