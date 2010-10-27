<?php

class Rediska_Profiler_Profile
{
    protected $_startTime;

    protected $_stopTime;

    protected $_context;

    public function __construct()
    {
        $this->start();
    }

    public function start()
    {
        $this->_startTime = microtime(true);
    }

    public function stop($context)
    {
        $this->_context = $context;
        $this->_stopTime = microtime(true);
    }

    public function hasStopped()
    {
        return $this->_stopTime !== null;
    }

    public function getContext()
    {
        return $this->_context;
    }

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

    public function  __toString()
    {
        return $this->getContext() . ' => ' . $this->getElapsedTime(4);
    }
}