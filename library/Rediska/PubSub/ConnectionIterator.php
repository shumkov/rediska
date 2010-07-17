<?php

/**
 * This iterator is used by Rediska_PubSub_Context
 * to repeatedly iterate through available connections
 *
 * @author Yuriy Bogdanov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_PubSub_ConnectionIterator implements Iterator, Countable
{
    /**
     *
     * @var Rediska_PubSub_Context
     */
    protected $_context;

    /**
     * Index
     *
     * @var int
     */
    protected $_i = 0;

    /**
     * Constructor.
     *
     * @param Rediska_PubSub_Context $command
     */
    public function  __construct(Rediska_PubSub_Context $context)
    {
        $this->_context = $context;
    }

    /**
     *
     * @return Rediska_PubSub_Context
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     *
     * @return Rediska_PubSub_Connection
     */
    public function current()
    {
        $data = $this->_context->getActiveConnections();
        return $data[$this->_i];
    }

    /**
     *
     * @return string
     */
    public function key()
    {
        return $this->_i;
    }

    public function next()
    {
        // Run around
        $this->_i = ++$this->_i % $this->count();
    }

    public function rewind()
    {
        $this->_i = 0;
    }

    public function valid()
    {
        // Valid until there are some active connections
        return $this->count() > 0;
    }

    public function count()
    {
        return count($this->_context->getActiveConnections());
    }
}