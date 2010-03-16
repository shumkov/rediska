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
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
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

        $rediska = new Rediska($options);

        Zend_Registry::set($key, $rediska);

        return $rediska;
    }
}