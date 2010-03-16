<?php

/**
 * @see Rediska
 */
require_once 'Rediska.php';

/**
 * @see Rediska_Key_List
 */
require_once 'Rediska/Key/List.php';

/**
 * @see Rediska_Key_Set
 */
require_once 'Rediska/Key/Set.php';

/**
 * @see Zend_Queue_Adapter_AdapterAbstract
 */
require_once 'Zend/Queue/Adapter/AdapterAbstract.php';

/**
 * Redis adapter for Zend_Queue
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Queue_Adapter_Redis extends Zend_Queue_Adapter_AdapterAbstract
{
	const KEY_PREFIX = 'Zend_Queue_';

	/**
	 * Rediska instance
	 *
	 * @var Rediska
	 */
	protected $_rediska;

	/**
	 * Queues set
	 *
	 * @var Rediska_Key_Set
	 */
	protected $_queues;

	/**
	 * Queue Lists array
	 *
	 * @var array
	 */
	protected $_queueObjects;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @param  null|Zend_Queue $queue
     * @return void
     */
    public function __construct($options, Zend_Queue $queue = null)
    {
        parent::__construct($options, $queue);

        $defaultInstance = Rediska::getDefaultInstance();
        if (empty($this->_options['driverOptions']) && $defaultInstance) {
        	$this->_rediska = $defaultInstance;
        } else {
        	$this->_rediska = new Rediska($this->_options['driverOptions']);
        }

        $this->_queues = new Rediska_Key_Set($this->_getKeyName('queues'));
        $this->_queues->setRediska($this->_rediska);
    }

    /**
     * Does a queue already exist?
     *
     * Throws an exception if the adapter cannot determine if a queue exists.
     * use isSupported('isExists') to determine if an adapter can test for
     * queue existance.
     *
     * @param  string $name
     * @return boolean
     */
    public function isExists($name)
    {
    	if (isset($this->_queueObjects[$name])) {
    		return true;
    	} else {
    		return $this->_queues->exists($name);
    	}
    }

    /**
     * Create a new queue
     *
     * Visibility timeout is how long a message is left in the queue "invisible"
     * to other readers.  If the message is acknowleged (deleted) before the
     * timeout, then the message is deleted.  However, if the timeout expires
     * then the message will be made available to other queue readers.
     *
     * @param  string  $name    queue name
     * @param  integer $timeout default visibility timeout
     * @return boolean
     */
    public function create($name, $timeout = null)
    {
        $this->_queues->add($name);
        $this->_queueObjects[$name] = new Rediska_Key_List($this->_getKeyName("queue_$name"));

        return true;
    }

    /**
     * Delete a queue and all of it's messages
     *
     * Returns false if the queue is not found, true if the queue exists
     *
     * @param  string  $name queue name
     * @return boolean
     */
    public function delete($name)
    {
        if ($this->_queues->remove($name)) {
        	if (isset($this->_queueObjects[$name])) {
        		unset($this->_queueObjects[$name]);
        	}

        	return $this->_rediska->delete($this->_getKeyName("queue_$name"));
        }
    }

    /**
     * Get an array of all available queues
     *
     * Not all adapters support getQueues(), use isSupported('getQueues')
     * to determine if the adapter supports this feature.
     *
     * @return array
     */
    public function getQueues()
    {
        return $this->_queues->toArray();
    }

    protected function _getKeyName($name)
    {
    	return self::KEY_PREFIX . $name;
    }

    /**
     * Return the approximate number of messages in the queue
     *
     * @param  Zend_Queue $queue
     * @return integer
     */
    public function count(Zend_Queue $queue=null)
    {
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $queueName = $queue->getName();

        if (!$this->isExists($queueName)) {
            require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue does not exist:' . $queueName);
        }

        if (!isset($this->_queueObjects[$queueName])) {
            $this->_queueObjects[$queueName] = new Rediska_Key_List($this->_getKeyName("queue_$queueName"));
        }

        return count($this->_queueObjects[$queueName]);
    }

    /********************************************************************
     * Messsage management functions
     *********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param  string     $message Message to send to the active queue
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Exception
     */
    public function send($message, Zend_Queue $queue=null)
    {
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $queueName = $queue->getName();

        if (!$this->isExists($queueName)) {
        	require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue does not exist:' . $queueName);
        }

        if (!isset($this->_queueObjects[$queueName])) {
        	$this->_queueObjects[$queueName] = new Rediska_Key_List($this->_getKeyName("queue_$queueName"));
        }

        $result = $this->_queueObjects[$queueName]->prepend($message);

        if ($result === false) {
        	require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Failed to insert message into queue:' . $queueName);
        }

        $options = array(
            'queue' => $queue,
            'data'  => array('body' => $message),
        );

        $classname = $queue->getMessageClass();
        if (!class_exists($classname)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    /**
     * Get messages in the queue
     *
     * @param  integer    $maxMessages  Maximum number of messages to return
     * @param  integer    $timeout      Visibility timeout for these messages
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message_Iterator
     */
    public function receive($maxMessages=null, $timeout=null, Zend_Queue $queue=null)
    {
        if ($maxMessages === null) {
            $maxMessages = 1;
        }
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $queueName = $queue->getName();

        if (!$this->isExists($queueName)) {
            require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue does not exist:' . $queueName);
        }

        if (!isset($this->_queueObjects[$queueName])) {
            $this->_queueObjects[$queueName] = new Rediska_Key_List($this->_getKeyName("queue_$queueName"));
        }

        $messages = array();
        for ($i = 0; $i < $maxMessages; $i++) {
        	$message = $this->_queueObjects[$queueName]->pop();
        	if (!is_null($message)) {
                $messages[] = array('body' => $message);
        	}
        }

        $options = array(
            'queue'        => $queue,
            'data'         => $messages,
            'messageClass' => $queue->getMessageClass(),
        );

        $classname = $queue->getMessageSetClass();
        if (!class_exists($classname)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    /**
     * Delete a message from the queue
     *
     * Returns true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * @param  Zend_Queue_Message $message
     * @return boolean
     */
    public function deleteMessage(Zend_Queue_Message $message)
    {
    	$queueName = $this->_queue->getName();

        if (!isset($this->_queueObjects[$queueName])) {
            $this->_queueObjects[$queueName] = new Rediska_Key_List($this->_getKeyName("queue_$queueName"));
        }

        return (boolean)$this->_queueObjects[$queueName]->remove($message->body);
    }

    /********************************************************************
     * Supporting functions
     *********************************************************************/

    /**
     * Return a list of queue capabilities functions
     *
     * $array['function name'] = true or false
     * true is supported, false is not supported.
     *
     * @param  string $name
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'create'        => true,
            'delete'        => true,
            'send'          => true,
            'receive'       => true,
            'deleteMessage' => true,
            'getQueues'     => true,
            'count'         => true,
            'isExists'      => true,
        );
    }
}