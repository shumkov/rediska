<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../../../Rediska.php';

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
 * @subpackage ZendFrameworkIntegration
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Application_Resource_Rediska extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'rediska';

    public function init()
    {
        $options = $this->getOptions();

        if (isset($options['instances'])) {
            foreach($options['instances'] as $name => $instanceOptions) {
                if ($name == Rediska::DEFAULT_NAME) {
                    $options = $instanceOptions;
                } else {
                    $instanceOptions['name'] = $name;
                    Rediska_Manager::add($instanceOptions);
                }
            }
            unset($options['instances']);
        }

        if (!empty($options)) {
            $options['name'] = Rediska::DEFAULT_NAME;   
            $rediska = new Rediska($options);

            Zend_Registry::set(self::DEFAULT_REGISTRY_KEY, $rediska);

            return $rediska;
        }
    }
}