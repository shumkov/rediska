<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../../../Rediska.php';

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
 * @subpackage ZendFrameworkIntegration
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Session_SaveHandler_Redis extends Rediska_Options_RediskaInstance implements Zend_Session_SaveHandler_Interface
{
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
        'keyPrefix' => 'PHPSESSIONS_',
        'lifetime'  => null,
    );

    /**
     * Exception class name for options
     * 
     * @var string
     */
    protected $_optionsException = 'Zend_Session_SaveHandler_Exception';

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
        if (!isset($options['lifetime'])) {
            $lifetime = (int)ini_get('session.gc_maxlifetime');

            if ($lifetime != 0) {
                $options['lifetime'] = $lifetime;
            } else {
                trigger_error(
                    "Please set session.gc_maxlifetime to enable garbage collection.",
                    E_USER_WARNING
                );
            }
        }

        parent::__construct($options);

        Rediska_Zend_Session_Set::setSaveHandler($this);
        $this->_set = new Rediska_Zend_Session_Set();
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
        return $this->getRediska()->get($this->_getKeyName($id));
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
        try {
            $timestamp = time();
            $this->_set[$timestamp] = $id;
        } catch (Rediska_Connection_Exec_Exception $e) {
            $this->_deleteSetOrThrowException($e);
        }

        return $this->getRediska()->setAndExpire(
            $this->_getKeyName($id),
            $data,
            $this->getOption('lifetime')
        );
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        try {
            $this->_set->remove($id);
        } catch(Rediska_Connection_Exec_Exception $e) {
            $this->_deleteSetOrThrowException($e);
        }

        return $this->getRediska()->delete($this->_getKeyName($id));
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
        try {
            return $this->_set->removeByScore(0, time() - $this->getOption('lifetime'));
        } catch(Rediska_Connection_Exec_Exception $e) {
            $this->_deleteSetOrThrowException($e);
        }
    }

    /**
     * Add prefix to session name
     * @param string $id
     * @return string
     */
    protected function _getKeyName($id)
    {
        return $this->getOption('keyPrefix') . $id;
    }

    /**
     * Delete old set or throw exception
     *
     * @throws Rediska_Connection_Exec_Exception
     * @param Rediska_Connection_Exec_Exception $e
     * @return void
     */
    protected function _deleteSetOrThrowException(Rediska_Connection_Exec_Exception $e)
    {
        if ($e->getMessage() == 'Operation against a key holding the wrong kind of value') {
            $this->_set->delete();
        } else {
            throw $e;
        }
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
}
