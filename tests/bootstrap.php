<?php

/**
 * @todo Currently we assume the test suite is run from source.
 */
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
    get_include_path(),
)));

// Configuration
if (file_exists(dirname(__FILE__) . '/config.ini')) {
    $config = parse_ini_file(dirname(__FILE__) . '/config.ini');

    define('REDISKA_HOST', $config['rediska_host'][0]);
    define('REDISKA_PORT', $config['rediska_port'][0]);
    define('REDISKA_SECOND_HOST', $config['rediska_host'][1]);
    define('REDISKA_SECOND_PORT', $config['rediska_port'][1]);
} else {
	define('REDISKA_HOST', '127.0.0.1');
    define('REDISKA_PORT', 6380);
    define('REDISKA_SECOND_HOST', '127.0.0.1');
    define('REDISKA_SECOND_PORT', 6381);
}

require_once dirname(__FILE__) . '/library/Rediska/TestCase.php';

// Run As... PHPUnit Test on bootstrap.php
class ZendStudioLauncher extends PHPUnit_Framework_TestSuite
{
    public function __construct($theClass = '', $name = '')
    {
        $this->setName(get_class($this));
        $zendPath = getCwd();
        chdir(realpath(dirname(__FILE__)));
        require_once 'PHPUnit/Util/Configuration.php';
        // test phpUnit version 
        if (version_compare(PHPUnit_Runner_Version::id(), '3.4', '>=')) {
            $configuration = PHPUnit_Util_Configuration::getInstance('phpunit.xml');
        } else {
            $configuration = new PHPUnit_Util_Configuration('phpunit.xml');
        }
        $testSuite = $configuration->getTestSuiteConfiguration(false);
        chdir($zendPath);
        foreach ($testSuite->tests() as $test) {
            if (!$test instanceof PHPUnit_Framework_Warning) {
                $this->addTestSuite($test);
            }
        }
    }
    
    public static function suite()
    {
        return new self();
    }
}