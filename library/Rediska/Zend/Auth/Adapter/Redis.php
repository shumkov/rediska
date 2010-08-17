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
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Auth_Adapter_Redis extends Rediska_Options implements Zend_Auth_Adapter_Interface
{
    /**
     * Rediska instance
     * 
     * @var Rediska
     */
    protected $_rediska = Rediska::DEFAULT_NAME;

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
     * Configuration
     * 
     * userIdKey               - Redis key where you store relation between login and id. * replaced to identity (login)
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
        'useridkey'               => 'user_ids:*',
        'userdatakey'             => 'users:*',
        'credentialattributename' => 'password',
        'userdataisarray'         => false,
    );

    /**
     * Set Rediska instance
     *
     * @param Rediska $rediska Rediska instance or name
     * @return Rediska_Key_Abstract
     */
    public function setRediska($rediska)
    {
        if (is_object($rediska) && !$rediska instanceof Rediska) {
            throw new Rediska_Key_Exception('$rediska must be Rediska instance name, Rediska object or array of options');
        }

        $this->_rediska = $rediska;

        return $this;
    }

    /**
     * Get Rediska instance
     *
     * @throws Rediska_Exception
     * @return Rediska
     */
    public function getRediska()
    {
        if (!is_object($this->_rediska)) {
            if (is_array($this->_rediska)) {
                $this->_rediska = new Rediska($this->_rediska);
            } else {
                $this->_rediska = Rediska_Manager::getOrInstanceDefault($this->_rediska);
            }
        }

        return $this->_rediska;
    }

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

            $credentialAttributeName = $this->getOption('credentialattributename');
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
