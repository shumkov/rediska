<?php

class Rediska_Profiler_Profile extends Rediska_Profiler_Profile_Abstract
{
    protected $_startTime;

    protected $_stopTime;

    protected $_profiledObject;

    public function __construct()
    {
        $this->start();
    }

    public function start()
    {
        $this->_startTime = microtime(true);
    }

    public function stop($profiledObject)
    {
        $this->_profiledObject = $profiledObject;
        $this->_stopTime = microtime(true);
    }

    public function hasStopped()
    {
        return $this->_stopTime !== null;
    }

    public function getLabel()
    {
        return $this->_profiledObject->getName();
    }

    public function getElapsedTime()
    {
        if (!$this->hasStopped()) {
            return false;
        }

        return $this->_endTime - $this->_startTime;
    }
}