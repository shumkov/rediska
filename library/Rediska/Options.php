<?php

/**
 * Abstract class for provide options to Rediska components
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_Options
{
    protected $_options = array();

    /**
     * Exception class name for Rediska setter and getter
     * 
     * @var string
     */
    protected $_optionsException = 'Rediska_Exception';

    public function __construct(array $options = array()) 
    {
        $options = array_merge($this->_options, $options);

        $this->setOptions($options);
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
            $this->setOption($name, $value);
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
     * @param string $name Name of option
     * @param mixed $value Value of option
     * @return Rediska_Options
     */
    public function setOption($name, $value)
    {
        if (method_exists($this, "set$name")) {
            return call_user_func(array($this, "set$name"), $value);
        } else if (array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
            return $this;
        } else {
            throw new $this->_optionsException("Unknown option '$name'");
        }
    }

    /**
     * Get option
     *  
     * @param string $name Name of option
     * @return mixed
     */
    public function getOption($name)
    {
        if (method_exists($this, "get$name")) {
            return call_user_func(array($this, "get$name"));
        } else if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        } else {
            throw new $this->_optionsException("Unknown option '$name'");
        }
    }
}