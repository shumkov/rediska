<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath('../library'),
    get_include_path(),
)));

define('REDISKA_TESTS_PATH', realpath(dirname(__FILE__)));

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Test/Strings.php';
require_once 'Test/KeySpace.php';
require_once 'Test/Lists.php';
require_once 'Test/Sets.php';
require_once 'Test/Controls.php';
require_once 'Test/Serializer.php';
require_once 'Test/KeyDistributor/Crc32.php';
require_once 'Test/KeyDistributor/ConsistentHashing.php';
require_once 'Test/Key/Abstract.php';
require_once 'Test/Key/Basic.php';
require_once 'Test/Key/Set.php';
require_once 'Test/Key/List.php';
require_once 'Test/Zend/Application/Resource.php';

require_once 'Rediska.php';

class Suite extends PHPUnit_Framework_TestSuite
{
	public function __construct()
	{
		$this->setName('Suite');

		$this->addTestSuite('Test_Strings');
		$this->addTestSuite('Test_KeySpace');
		$this->addTestSuite('Test_Lists');
		$this->addTestSuite('Test_Sets');
		$this->addTestSuite('Test_Controls');
		$this->addTestSuite('Test_Serializer');
		$this->addTestSuite('Test_KeyDistributor_Crc32');
		$this->addTestSuite('Test_KeyDistributor_ConsistentHashing');
		$this->addTestSuite('Test_Key_Abstract');
		$this->addTestSuite('Test_Key_Basic');
		$this->addTestSuite('Test_Key_Set');
		$this->addTestSuite('Test_Key_List');
		$this->addTestSuite('Test_Zend_Application_Resource');
	}

	public static function suite() {
		return new self();
	}
}

