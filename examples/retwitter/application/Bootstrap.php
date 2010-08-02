<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initAutoload()
    {
        $loader = $this->getApplication()->getAutoloader();

        $loader->setFallbackAutoloader(true);
        $loader->suppressNotFoundWarnings(false);
        
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath'  => APPLICATION_PATH,
            'namespace' => '',
            'resourceTypes' => array(
                'form' => array(
                    'path'      => 'forms',
                    'namespace' => 'Form',
                ),
            ),
        ));
        
    }
}