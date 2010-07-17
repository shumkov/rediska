<?php

/**
 * Unsubscribe from the channel or multiple channels
 * This object is a proxy for Rediska_PubSub_Context
 *
 * @param string|array  $channel or array of channels
 * @return array - the list of successfully unsubscribed channels
 *
 * @author Yuriy Bogdanov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Unsubscribe extends Rediska_Command_Abstract
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
     * @var array
     */
    protected $_result;

    protected function _create($channels, $timeout = null)
    {
        $this->_context = Rediska_PubSub_Context::getInstance();
        $this->_channels = $channels;
    }

    public function write()
    {
        $this->_result = $this->_context->unsubscribe($this->_channels);
    }

    /**
     * Overloaded read()
     * Here we just ensure that all subscriptions succeed
     *
     * @return Rediska_PubSub_Context
     */
    public function read()
    {
        return $this->_result;
    }
}