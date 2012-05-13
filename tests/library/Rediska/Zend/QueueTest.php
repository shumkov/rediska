<?php

require_once 'Zend/Queue.php';

class Rediska_Zend_QueueTest extends Rediska_TestCase
{
    /**
     * @var Zend_Queue
     */
    protected $queue;

    protected function setUp()
    {
        parent::setUp();
        $this->queue = new Zend_Queue('Redis', array('adapterNamespace' => 'Rediska_Zend_Queue_Adapter'));
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
        $this->assertInstanceOf($queue->getMessageClass(), $reply);

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
        $this->assertInstanceOf($queue->getMessageSetClass(), $messages);

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
