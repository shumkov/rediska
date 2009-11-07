<?php

require_once 'PHPUnit/Framework/TestCase.php';

class RediskaTestCase extends PHPUnit_Framework_TestCase
{
	/**
     * @var Rediska
     */
    protected $rediska;
	
	protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Tests_'));
    }
    
    protected function tearDown()
    {
        $this->rediska->flushDb(true);
        $this->rediska = null;
    }
	
    protected function _addServerOrSkipTest($host, $port)
    {
        $socket = @fsockopen($host, $port);

        if (is_resource($socket)) {
            @fclose($socket);
            $this->rediska->addServer($host, $port);
        } else {
            $this->markTestSkipped("You must start server $host:$port before run test");
        }
    }
}