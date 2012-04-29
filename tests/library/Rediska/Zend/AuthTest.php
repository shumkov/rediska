<?php

require_once 'Zend/Auth.php';

class Rediska_Zend_AuthTest extends Rediska_TestCase
{
    /**
     * @var Zend_Auth
     */
    protected $auth;

    /**
     * @var Rediska_Zend_Auth_Adapter_Redis
     */
    protected $adapter;

    protected function setUp()
    {
        parent::setUp();
        $this->rediska->set('user_ids:test', 1);

        $data = new stdClass;
        $data->login = 'test';
        $data->password = 'test';

        $this->rediska->set('users:1', $data);

        Zend_Session::$_unitTestEnabled = true;
        $this->auth = Zend_Auth::getInstance();

        $this->adapter = new Rediska_Zend_Auth_Adapter_Redis();
    }

    public function testIdentityNotFound()
    {
        $this->adapter->setIdentity('test2')->setCredential('aaa');

        $result = $this->auth->authenticate($this->adapter);

        $this->assertFalse($result->isValid());
        $this->assertEquals(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
    }

    public function testCredentialInvalid()
    {
        $this->adapter->setIdentity('test')->setCredential('aaa');

        $result = $this->auth->authenticate($this->adapter);

        $this->assertFalse($result->isValid());
        $this->assertEquals(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }

    public function testSuccess()
    {
        $this->adapter->setIdentity('test')->setCredential('test');

        $result = $this->auth->authenticate($this->adapter);

        $this->assertTrue($result->isValid());
        $this->assertEquals(Zend_Auth_Result::SUCCESS, $result->getCode());
        $this->assertTrue(is_object($this->adapter->getResultUserData()));
    }
}
