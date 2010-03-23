<?php

/**
 * @see Rediska
 */
require_once 'Rediska.php';

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
class Rediska_Zend_Auth_Adapter_Redis implements Zend_Auth_Adapter_Interface
{
	/**
	 * Rediska instance
	 * 
	 * @var Rediska
	 */
	protected $_rediska;

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
	 * rediska                 - Rediska instance
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
	 * Construct Redis Zend_Auth adapter
	 * 
	 * @param array|Zend_Config $options Options
	 * 
	 * userIdKey               - Redis key where you store relation between login and id. * replaced to identity (login)
     * userDataKey             - Redis key where you store user data
     * credentialAttributeName - Name of credential (password) attribute in user data
     * userDataIsArray         - Set true if you store user data in associative array
     * identity                - User identity (login) for authorization
     * credential              - User credintial (password) for authorization
     * rediska                 - Rediska instance
     * 
	 */
    public function __construct($options = array())
    {
    	if ($options instanceof Zend_Config) {
    		$options = $options->toArray();
    	}

        $options = array_change_key_case($options, CASE_LOWER);
        $options = array_merge($this->_options, $options);

        $this->setOptions($options);

        $this->_setupRediskaDefaultInstance();
    }
    
    /**
     * Set options array
     * 
     * @param array $options Options (see $_options description)
     * @return Rediska_Zend_Auth_Adapter_Redis
     */
    public function setOptions(array $options)
    {
        foreach($options as $name => $value) {
            if (method_exists($this, "set$name")) {
                call_user_func(array($this, "set$name"), $value);
            } else {
                $this->setOption($name, $value);
            }
        }

        return $this;
    }

    /**
     * Set option
     * 
     * @throws Zend_Auth_Adapter_Exception
     * @param string $name Name of option
     * @param mixed $value Value of option
     * @return Rediska_Zend_Auth_Adapter_Redis
     */
    public function setOption($name, $value)
    {
        $lowerName = strtolower($name);

        if (!array_key_exists($lowerName, $this->_options)) {
            throw new Zend_Auth_Adapter_Exception("Unknown option '$name'");
        }

        $this->_options[$lowerName] = $value;

        return $this;
    }

    /**
     * Get option
     * 
     * @throws Zend_Auth_Adapter_Exception 
     * @param string $name Name of option
     * @return mixed
     */
    public function getOption($name)
    {
        $lowerName = strtolower($name);

        if (!array_key_exists($lowerName, $this->_options)) {
            throw new Zend_Auth_Adapter_Exception("Unknown option '$name'");
        }

        return $this->_options[$lowerName];
    }
    
    /**
     * Set Rediska instance
     * 
     * @param Rediska $rediska
     * @return Rediska_Zend_Auth_Adapter_Redis
     */
    public function setRediska(Rediska $rediska)
    {
        $this->_rediska = $rediska;
        
        return $this;
    }

    /**
     * Get Rediska instance
     * 
     * @return Rediska
     */
    public function getRediska()
    {
        if (!$this->_rediska instanceof Rediska) {
            throw new Zend_Auth_Adapter_Exception('Rediska instance not found for ' . get_class($this));
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

    /**
     * Setup Rediska instance
     */
    protected function _setupRediskaDefaultInstance()
    {
    	if (is_null($this->_rediska)) {
            $this->_rediska = Rediska::getDefaultInstance();
            if (is_null($this->_rediska)) {
                $this->_rediska = new Rediska();
            }
    	}
    }
}
