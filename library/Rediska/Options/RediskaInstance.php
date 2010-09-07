<?php

/**
 * Abstract class for provide options and Rediska instanse setter and getter to Rediska components
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Options_RediskaInstance extends Rediska_Options
{
    /**
     * Rediska instance
     *
     * @var string|array|Rediska
     */
    protected $_rediska = Rediska::DEFAULT_NAME;
    
    /**
     * Rediska instance option name
     * 
     * @var string
     */
    protected $_rediskaOptionName = 'rediska';

    /**
     * Set Rediska instance
     * 
     * @param array|string|Rediska $rediska Rediska instance name, Rediska object or Rediska options for new instance
     * @return $this
     */
    public function setRediska($rediska)
    {
        $this->_rediska = $rediska;

        return $this;
    }

    /**
     * Get Rediska instance
     *
     * @return Rediska
     */
    public function getRediska()
    {
        if (!is_object($this->_rediska)) {
            $this->_rediska = self::getRediskaInstance($this->_rediska, $this->_optionsException, $this->_rediskaOptionName);
        }

        return $this->_rediska;
    }

    public static function getRediskaInstance($rediska, $exceptionClassName = 'Rediska_Exception', $optionName = 'rediska')
    {
        if (is_string($rediska)) { // Rediska instance name
            if ($rediska == Rediska::DEFAULT_NAME) { // Default name
                if (Rediska_Manager::has($rediska)) {
                    $rediska = Rediska_Manager::get($rediska);
                } else {
                    throw new $exceptionClassName("You must instance '" . Rediska::DEFAULT_NAME . "' Rediska before or use '$optionName' option for specify another");
                }
            } else { // Another name
                $rediska = Rediska_Manager::get($rediska);
            }
        } else if (is_array($rediska)) { // Rediska options
            $options = $rediska;
            $options['addToManager'] = false;
            $rediska = new Rediska($options);
        } else if (!$rediska instanceof Rediska) {
            throw new $exceptionClassName("'$optionName' option must be instance name, Rediska object or rediska options for new instance");
        }

        return $rediska;
    }
}