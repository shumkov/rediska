<?php

require_once 'Zend/Queue.php';

class Test_Zend_Queue extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Queue
     */
    protected $queue;

    /** 
     * @var Rediska
     */
    protected $rediska;

    protected function setUp()
    {
        $this->rediska = new Rediska(array('namespace' => 'Rediska_Test_', 'servers' => array(array('host' => REDISKA_HOST, 'port' => REDISKA_PORT))));
        $this->queue = new Zend_Queue('Redis', array('adapterNamespace' => 'Rediska_Zend_Queue_Adapter'));
    }

    protected function tearDown()
    {
        $this->rediska->flushDb(true);
        $this->rediska = null;
        $this->saveHandler = null;
    }

    public function testCreateQueue()
    {
    	$queue = $this->queue->createQueue('test');
    	$values = $this->rediska->getSet('Zend_Queue_queues');
    	$this->assertEquals(array('test'), $values);
    }

    public function testDeleteQueue()
    {
    	$queue = $this->queue->createQueue('test');
    	$this->queue->createQueue('test2');

    	$queue->deleteQueue();

    	$values = $this->rediska->getSet('Zend_Queue_queues');
        $this->assertEquals(array('test2'), $values);
    }

    public function testSend()
    {
    	$queue = $this->queue->createQueue('test');
    	$reply = $queue->send(array(1, 2, 3));
    	$this->assertType($queue->getMessageClass(), $reply);

    	$values = $this->rediska->getList('Zend_Queue_queue_test');
    	$this->assertEquals(array(array(1, 2, 3)), $values);
    }

    public function testCount()
    {
    	$queue = $this->queue->createQueue('test');
        $queue->send(array(1, 2, 3));
        $this->assertEquals(1, $queue->count());
    }

    public function testReceive()
    {
    	$queue = $this->queue->createQueue('test');
        $queue->send(array(1, 2, 3));

        $messages = $queue->receive();
        $this->assertType($queue->getMessageSetClass(), $messages);

        $values = $messages->toArray();
        $this->assertEquals(array(1, 2, 3), $values[0]['body']);
    }

    public function testDeleteMessage()
    {
        $queue = $this->queue->createQueue('test');
        $queue->send(array(1, 2, 3));

        foreach($queue->receive() as $message) {
            $queue->deleteMessage($message);
        }

        $values = $this->rediska->getList('Zend_Queue_queue_test');
        $this->assertEquals(array(), $values);
    }
}