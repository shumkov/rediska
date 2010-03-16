<?php

define('REDISKA_HOST', '127.0.0.1');
define('REDISKA_PORT', 6380);
define('REDISKA_SECOND_HOST', '127.0.0.1');
define('REDISKA_SECOND_PORT', 6381);

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../library'),
    get_include_path(),
)));

define('REDISKA_TESTS_PATH', realpath(dirname(__FILE__)));

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'classes/RediskaTestCase.php';

require_once 'Test/Strings.php';
require_once 'Test/KeySpace.php';
require_once 'Test/Lists.php';
require_once 'Test/Sets.php';
require_once 'Test/SortedSets.php';
require_once 'Test/Controls.php';
require_once 'Test/Serializer.php';
require_once 'Test/Connection.php';
require_once 'Test/Pipeline.php';
require_once 'Test/KeyDistributor/Crc32.php';
require_once 'Test/KeyDistributor/ConsistentHashing.php';
require_once 'Test/Key/Abstract.php';
require_once 'Test/Key/Basic.php';
require_once 'Test/Key/Set.php';
require_once 'Test/Key/SortedSet.php';
require_once 'Test/Key/List.php';
require_once 'Test/Zend/Application/Resource.php';
require_once 'Test/Zend/Auth.php';
require_once 'Test/Zend/Cache.php';
require_once 'Test/Zend/Session.php';
require_once 'Test/Zend/Queue.php';
require_once 'Test/Zend/Log.php';

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
		$this->addTestSuite('Test_SortedSets');
		$this->addTestSuite('Test_Controls');
		$this->addTestSuite('Test_Serializer');
		$this->addTestSuite('Test_Connection');
		$this->addTestSuite('Test_Pipeline');
		$this->addTestSuite('Test_KeyDistributor_Crc32');
		$this->addTestSuite('Test_KeyDistributor_ConsistentHashing');
		$this->addTestSuite('Test_Key_Abstract');
		$this->addTestSuite('Test_Key_Basic');
		$this->addTestSuite('Test_Key_Set');
		$this->addTestSuite('Test_Key_SortedSet');
		$this->addTestSuite('Test_Key_List');
		$this->addTestSuite('Test_Zend_Application_Resource');
		$this->addTestSuite('Test_Zend_Auth');
		$this->addTestSuite('Test_Zend_Cache');
		$this->addTestSuite('Test_Zend_Session');
		$this->addTestSuite('Test_Zend_Queue');
		$this->addTestSuite('Test_Zend_Log');
	}

	public static function suite() {
		return new self();
	}
}

