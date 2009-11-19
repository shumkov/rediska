<?php

require_once 'Zend/Log.php';

require_once 'Rediska/Zend/Log/Writer/Redis.php';

class Test_Zend_Log extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Log
     */
    protected $log;

    /** 
     * @var Rediska
     */
    protected $rediska;

    protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Test_'));
        $writer = new Rediska_Zend_Log_Writer_Redis('log');
        $this->log = new Zend_Log($writer);
    }

    protected function tearDown()
    {
        $this->rediska->flushDb(true);
        $this->rediska = null;
        $this->saveHandler = null;
    }

    public function testWrite()
    {
    	$this->log->err('123');
    	$this->log->info('123');

    	$count = $this->rediska->getListLength('log');
    	$this->assertEquals(2, $count);
    }
}