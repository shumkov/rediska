<?php

/**
 * Rediska profile
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Profiler
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Profiler_Profile
{
    /**
     * Profiler object
     *
     * @var Rediska_Profiler_Interface
     */
    protected $_profiler;

    /**
     * Context
     *
     * @var mixed
     */
    protected $_context;

    /**
     * Start time
     *
     * @var integer|null
     */
    protected $_startTime;

    /**
     * Stop time
     *
     * @var integer|null
     */
    protected $_stopTime;

    /**
     * Constructor
     *
     * @param Rediska_Profiler_Interface $profiler
     * @param mixed $context
     */
    public function __construct(Rediska_Profiler_Interface $profiler, $context)
    {
        $this->_profiler = $profiler;
        $this->_context  = $context;
    }

    /**
     * Start profile
     *
     * @return integer
     */
    public function start()
    {
        $this->_startTime = microtime(true);

        $this->_profiler->startCallback($this);

        return $this->_startTime;
    }

    /**
     * Stop profile
     *
     * @return integer
     */
    public function stop()
    {
        $this->_stopTime = microtime(true);

        $this->_profiler->stopCallBack($this);

        return $this->getElapsedTime();
    }

    /**
     * Reset profile
     *
     * @return boolean
     */
    public function reset()
    {
        $this->_startTime = null;
        $this->_stopTime  = null;

        return true;
    }

    /**
     * Has stopped?
     *
     * @return boolean
     */
    public function hasStopped()
    {
        return $this->_stopTime !== null;
    }

    /**
     * Get context
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * Get elapsed time
     *
     * @param ineger[optioanl] $decimals
     * @return integer|false
     */
    public function getElapsedTime($decimals = null)
    {
        if (!$this->hasStopped()) {
            return false;
        }

        $elapsedTime = $this->_stopTime - $this->_startTime;

        if ($decimals) {
            return number_format($elapsedTime, $decimals);
        } else {
            return $elapsedTime;
        }
    }

    /**
     * Magic to string
     *
     * @return string
     */
    public function  __toString()
    {
        return $this->getContext() . ' => ' . $this->getElapsedTime(4);
    }
}