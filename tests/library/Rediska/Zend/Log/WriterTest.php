<?php

require_once 'Zend/Log.php';

class Rediska_Zend_Log_WriterTest extends Rediska_TestCase
{
    /**
     * @var Zend_Log
     */
    protected $log;

    protected function setUp()
    {
        parent::setUp();
        $writer = new Rediska_Zend_Log_Writer_Redis('log');
        $this->log = new Zend_Log($writer);
    }

    public function testWrite()
    {
        $this->log->err('123');
        $this->log->info('123');

        $count = $this->rediska->getListLength('log');
        $this->assertEquals(2, $count);
    }
}