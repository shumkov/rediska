<?php

/**
 * Abstract class for provide options to Rediska components
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_Options
{
    protected $_options = array();

    public function __construct(array $options = array()) 
    {
        $options = $this->applyDefaultOptions($options);

        $this->setOptions($options);
    }

    public function applyDefaultOptions($options)
    {
        $options = array_change_key_case($options, CASE_LOWER);
        $options = array_merge($this->_options, $options);

        return $options;
    }

    /**
     * Set options array
     * 
     * @param array $options Options (see $_options description)
     * @return Rediska_Options
     */
    public function setOptions(array $options)
    {
        foreach($options as $name => $value) {
            if (method_exists($this, "set$name")) {
                call_user_func(array($this, "set$name"), $value);
            } else {
                $this->setOption($name, $value);
            }
        }

        return $this;
    }

    /**
     * Get associative array of options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set option
     * 
     * @throws Rediska_Exception
     * @param string $name Name of option
     * @param mixed $value Value of option
     * @return Rediska_Options
     */
    public function setOption($name, $value)
    {
        $lowerName = strtolower($name);

        if (!array_key_exists($lowerName, $this->_options)) {
            throw new Rediska_Exception("Unknown option '$name'");
        }

        $this->_options[$lowerName] = $value;

        return $this;
    }

    /**
     * Get option
     * 
     * @throws Rediska_Exception 
     * @param string $name Name of option
     * @return mixin
     */
    public function getOption($name)
    {
        $lowerName = strtolower($name);

        if (!array_key_exists($lowerName, $this->_options)) {
            throw new Rediska_Exception("Unknown option '$name'");
        }

        return $this->_options[$lowerName];
    }
    
}