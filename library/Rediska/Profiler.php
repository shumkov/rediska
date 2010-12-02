<?php

/**
 * Rediska profiler
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Profiler
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Profiler implements Rediska_Profiler_Interface,
                                  IteratorAggregate,
                                  Countable
{
    /**
     * Profiles
     *
     * @var array
     */
    protected $_profiles = array();

    /**
     * Start profile
     *
     * @param mixed $context
     * @return Rediska_Profiler_Profile
     */
    public function start($context)
    {
        $profile = new Rediska_Profiler_Profile($this, $context);
        $profile->start();

        $this->_profiles[] = $profile;

        return $profile;
    }

    /**
     * Stop profile
     *
     * @return Rediska_Profiler_Profile
     */
    public function stop()
    {
        $hasUnstopped = false;
        foreach(array_reverse($this->_profiles) as $profile) {
            if (!$profile->hasStopped()) {
                $hasUnstopped = true;
                break;
            }
        }
        if ($hasUnstopped) {
            $profile->stop();
        } else {
            throw new Rediska_Profiler_Exception('You need start profiler before stop it');
        }

        return $profile;
    }

    /**
     * Reset profiler
     *
     * @return Rediska_Profiler
     */
    public function reset()
    {
        $this->_profiles = array();

        return $this;
    }

    /**
     * Get profiles
     *
     * @return array
     */
    public function getProfiles()
    {
        return $this->_profiles;
    }

    /**
     * Get total elapsed time
     *
     * @param integer[optional] $decimals
     * @return integer
     */
    public function getTotalElapsedTime($decimals = null)
    {
        $totalElapsedTime = 0;
        foreach ($this->getProfiles() as $profile) {
            if ($profile->hasStopped()) {
                $totalElapsedTime += $profile->getElapsedTime();
            }
        }

        if ($decimals) {
            return number_format($totalElapsedTime, $decimals);
        } else {
            return $totalElapsedTime;
        }
    }

    /**
     * Start callback. Called from profile
     *
     * @param Rediska_Profiler_Profile $profile
     */
    public function startCallBack(Rediska_Profiler_Profile $profile)
    {

    }

    /**
     * Stop callback. Called from profile
     *
     * @param Rediska_Profiler_Profile $profile
     */
    public function stopCallback(Rediska_Profiler_Profile $profile)
    {

    }

    /**
     * Get iterator. Implements IteratorAggregate interface
     *
     * @return ArrayObject
     */
    public function getIterator()
    {
        return new ArrayObject($this->_profiles);
    }

    /**
     * Get profiles count. Implements Countable interface
     *
     * @return integer
     */
    public function count()
    {
        return count($this->_profiles);
    }

    /**
     * Magic to string
     *
     * @return string
     */
    public function  __toString()
    {
        return count($this) . ' profiles => ' . $this->getTotalElapsedTime(4);
    }
}