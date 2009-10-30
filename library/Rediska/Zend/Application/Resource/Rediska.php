<?php

/**
 * @see Rediska
 */
require_once 'Rediska.php';

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Rediska Zend Application Resource for configure
 * and initialize Rediska from application.ini
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.2.1
 * @link http://code.google.com/p/rediska
 * @licence http://opensource.org/licenses/gpl-3.0.html
 */
class Rediska_Zend_Application_Resource_Rediska extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'rediska';

    public function init()
    {
        $options = $this->getOptions();

        if (isset($options['registry_key'])) {
        	$key = $options['registry_key'];
        	unset($options['registry_key']);
        } else {
        	$key = self::DEFAULT_REGISTRY_KEY;
        }

        $redis = new Rediska($options);

        Zend_Registry::set($key, $redis);

        return $redis;
    }
}