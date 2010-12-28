<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../../../Rediska.php';

/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @see Zend_Auth_Result
 */
require_once 'Zend/Auth/Result.php';

/**
 * @see Zend_Auth_Adapter_Exception
 */
require_once 'Zend/Auth/Adapter/Exception.php';

/**
 * @see Zend_Config
 */
require_once 'Zend/Config.php';

/**
 * Redis adapter for Zend_Auth
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage ZendFrameworkIntegration
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Auth_Adapter_Redis extends Rediska_Options_RediskaInstance implements Zend_Auth_Adapter_Interface
{
    /**
     * User identity
     * 
     * @var string
     */
    protected $_identity;

    /**
     * User credential
     * 
     * @var string
     */
    protected $_credential;

    /**
     * User data
     * 
     * @var array|object
     */
    protected $_userData;
    
    /**
     * Exception class name for options
     * 
     * @var string
     */
    protected $_optionsException = 'Zend_Auth_Adapter_Exception';

    /**
     * Configuration
     * 
     * userIdKey               - Redis key where you store relation between login and id. '*' replaced to identity (login)
     * userDataKey             - Redis key where you store user data
     * credentialAttributeName - Name of credential (password) attribute in user data
     * userDataIsArray         - Set true if you store user data in associative array
     * identity                - User identity (login) for authorization
     * credential              - User credintial (password) for authorization
     * rediska                 - Rediska instance name, Rediska object or array of options
     * 
     * @var array
     */
    protected $_options = array(
        'userIdKey'               => 'user_ids:*',
        'userDataKey'             => 'users:*',
        'credentialAttributeName' => 'password',
        'userDataIsArray'         => false,
    );

    /**
     * Set identity (login)
     * 
     * @param string $credential Identity (login)
     * @return Rediska_Zend_Auth_Adapter_Redis
     */
    public function setIdentity($identity)
    {
        $this->_identity = $identity;
        
        return $this;
    }

    /**
     * Get identity (login)
     * 
     * @return string
     */
    public function getIdentity()
    {
        return $this->_identity;
    }
    
    /**
     * Set credential (password)
     * 
     * @param string $credential Credential (password)
     * @return Rediska_Zend_Auth_Adapter_Redis
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        
        return $this;
    }

    /**
     * Get credential (password)
     * 
     * @return string
     */
    public function getCredential()
    {
        return $this->_credential;
    }

    /**
     * Get result user data
     * 
     * @return object|array
     */
    public function getResultUserData()
    {
        return $this->_userData;
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Auth_Adapter_Interface#authenticate()
     */
    public function authenticate()
    {
        $identity = $this->getIdentity();

        $userIdKey = str_replace('*', $identity, $this->getOption('userIdKey'));

        $userId = $this->getRediska()->get($userIdKey);

        if (is_null($userId)) {
            $code    = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $message = 'User with the supplied identity could not be found.';
        } else {
            $userDataKey = str_replace('*', $userId, $this->getOption('userDataKey'));

            $userData = $this->getRediska()->get($userDataKey);

            if (is_null($userData)) {
                throw new Zend_Auth_Adapter_Exception("User data key '$userDataKey' not found");
            }

            $credentialAttributeName = $this->getOption('credentialAttributeName');
            if ($this->getOption('userDataIsArray')) {
                if (!array_key_exists($credentialAttributeName, $userData)) {
                    throw new Zend_Auth_Adapter_Exception("Credential key with name '$credentialAttributeName' not found in user data");
                }
                $credential = $userData[$credentialAttributeName];
            } else {
                if (!isset($userData->$credentialAttributeName)) {
                    throw new Zend_Auth_Adapter_Exception("Credential attribute with name '$credentialAttributeName' not found in user data");
                }
                $credential = $userData->$credentialAttributeName;
            }

            if ($this->getCredential() == $credential) {
                $code     = Zend_Auth_Result::SUCCESS;
                $message  = 'Authentication successful.';
                $this->_userData = $userData;
            } else {
                $code     = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
                $message  = 'Supplied credential is invalid.';
            }
        }

        return new Zend_Auth_Result($code, $identity, array($message));
    }
}
