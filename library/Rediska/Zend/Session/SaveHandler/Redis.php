<?php

/**
 * @see Rediska
 */
require_once 'Rediska.php';

/** 
 * @see Rediska_Zend_Session_Set
 */
require_once 'Rediska/Zend/Session/Set.php';

/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';

/**
 * @see Zend_Config
 */
require_once 'Zend/Config.php';

/**
 * @see Zend_Session_SaveHandler_Interface
 */
require_once 'Zend/Session/SaveHandler/Interface.php';

/**
 * @see Zend_Session_SaveHandler_Exception
 */
require_once 'Zend/Session/SaveHandler/Exception.php';

/**
 * Redis save handler for Zend_Session
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Session_SaveHandler_Redis implements Zend_Session_SaveHandler_Interface
{
    /**
     * Rediska instance
     *
     * @var Rediska
     */
    protected $_rediska;
    
    /**
     * Sessions set
     * 
     * @var Rediska_Zend_Session_Set
     */
    protected $_set;
    
    /**
     * Configuration
     * 
     * @var array
     */
    protected $_options = array(
        'keyprefix'      => 'PHPSESSIONS_',
        'lifetime'       => null,
    );

    /**
     * Construct save handler
     *
     * @param Zend_Config|array $options
     */
    public function __construct($options = array())
    {
    	if ($options instanceof Zend_Config) {
    		$options = $options->toArray();
    	}

    	// Set default lifetime
    	$this->_options['lifetime'] = (integer)ini_get('session.gc_maxlifetime');

    	// Get Rediska instance
        $defaultInstance = Rediska::getDefaultInstance();
        if ($defaultInstance && !isset($options['rediskaOptions'])) {
            $this->_rediska = $defaultInstance;
        } else {
            $this->_rediska = new Rediska($options['rediskaOptions']);
            unset($options['rediskaOptions']);
        }

    	$this->setOptions($options);

        Rediska_Zend_Session_Set::setSaveHandler($this);

        $this->_set = new Rediska_Zend_Session_Set();
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Zend_Session::writeClose();
    }

    /**
     * Open Session
     *
     * @param string $save_path
     * @param string $name
     * @return boolean
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * Close session
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        return $this->_rediska->get($this->_getKeyName($id));
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
    	$this->_set[] = $id;

        $reply = $this->_rediska->set($this->_getKeyName($id), $data);

        if ($reply) {
            $this->_rediska->expire($this->_getKeyName($id), $this->_options['lifetime']);
        }

        return $reply;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        $this->_set->remove($id);

        $this->_rediska->delete($this->_getKeyName($id));

        return true;
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
    	$sessions = $this->_set->toArray();
    	foreach($sessions as &$session) {
    		$session = $this->_getKeyName($session);
    	}

    	// TODO: May by use TTL? Need benchmark.
    	$lifeSession = $this->_rediska->get($sessions);
    	foreach($sessions as $session) {
    		if (!isset($lifeSession[$session])) {
    			$sessionWithoutPrefix = substr($session, strlen($this->_options['keyprefix']));
    			$this->_set->remove($sessionWithoutPrefix);
    		}
    	}

    	return true;
    }

    /**
     * Set options array
     * 
     * @param array $options Options (see $_options description)
     * @return Rediska_Zend_Session_SaveHandler_Redis
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
     * @throws Zend_Session_SaveHandler_Exception
     * @param string $name Name of option
     * @param mixed $value Value of option
     * @return Rediska_Zend_Session_SaveHandler_Redis
     */
    public function setOption($name, $value)
    {
    	$lowerName = strtolower($name);

        if (!array_key_exists($lowerName, $this->_options)) {
            throw new Zend_Session_SaveHandler_Exception("Unknown option '$name'");
        }

        $this->_options[$lowerName] = $value;

        return $this;
    }

    /**
     * Get option
     * 
     * @param string $name Name of option
     * @return mixed
     */
    public function getOption($name)
    {
    	$lowerName = strtolower($name);
    	
        if (!array_key_exists($lowerName, $this->_options)) {
            throw new Zend_Session_SaveHandler_Exception("Unknown option '$name'");
        }

        return $this->_options[$lowerName];
    }
    
    /**
     * Set Rediska instance
     * 
     * @param Rediska $rediska
     * @return Rediska_Zend_Session_SaveHandler_Redis
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
        return $this->_rediska;
    }

    /**
     * Add prefix to session name
     * @param string $id
     * @return string
     */
    protected function _getKeyName($id)
    {
        return $this->_options['keyprefix'] . $id;
    }
}