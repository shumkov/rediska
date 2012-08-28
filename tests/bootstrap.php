<?php

ini_set('display_errors', true);

$composerAutoload = realpath(dirname(__FILE__) . '/../vendor/autoload.php');
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library/'),
    realpath(dirname(__FILE__) . '/../vendor/zendframework/zendframework/library/'),
    get_include_path(),
)));

// Require Rediska
require_once 'Rediska.php';

// Configuration
$status = @include_once 'Zend/Config/Ini.php';
if (false === $status) {
    echo "The Zend Framework is needed to run this test suite. ";
    echo "Please add it to your include_path.";
    echo "\n";
    exit(1);
}

$configPath = dirname(__FILE__) . '/config.ini';
if (!file_exists($configPath)) {
    $configPath = dirname(__FILE__) . '/config.ini-dist';
}
$config = new Zend_Config_Ini($configPath);
$config = $config->toArray();
$GLOBALS['rediskaConfigs'] = $config['rediska'];

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
