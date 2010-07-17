<?php

/**
 * Subscribe to the channel or multiple channels
 * This object is a proxy for Rediska_PubSub_Context
 *
 * @param string|array  $channel or array of channels
 * @return Rediska_PubSub_Context
 *
 * @author Yuriy Bogdanov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Subscribe extends Rediska_Command_Abstract
{
    /**
     *
     * @var Rediska_PubSub_Context
     */
    protected $_context;

    /**
     *
     * @var array|string
     */
    protected $_channels;

    /**
     *
     * @var int
     */
    protected $_timeout;

    protected function _create($channels, $timeout = null)
    {
        $this->_context = Rediska_PubSub_Context::getInstance();
        $this->_channels = $channels;
        $this->_timeout = $timeout;
    }

    public function write()
    {
        if ($this->_timeout) {
            $this->_context->setTimeout($this->_timeout);
        }
        $this->_context->subscribe($this->_channels);
    }

    /**
     * Overloaded read()
     * Here we just ensure that all subscriptions succeed
     *
     * @return Rediska_PubSub_Context
     */
    public function read()
    {
        return $this->_context;
    }
}