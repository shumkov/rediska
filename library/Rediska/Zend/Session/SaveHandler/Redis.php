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
        'keyprefix'      => 'PHPSESSIONS_',
        'lifetime'       => null,
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
        $this->_options['lifetime'] = (integer)ini_get('session.gc_maxlifetime');

        // Get Rediska instance
        if (isset($options['rediskaOptions'])) {
            throw new Zend_Session_SaveHandler_Exception("Option 'rediskaOptions' is deprecated. Use 'rediska' option. It may be Rediska object, instance name or options for new instance");
        }

        parent::__construct($options);

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
        $this->_set[] = $id;

        $reply = $this->getRediska()->set($this->_getKeyName($id), $data);

        if ($reply) {
            $this->getRediska()->expire($this->_getKeyName($id), $this->_options['lifetime']);
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

        $this->getRediska()->delete($this->_getKeyName($id));

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

        if (!empty($sessions)) {
            foreach($sessions as &$session) {
                $session = $this->_getKeyName($session);
            }
    
            // TODO: May by use TTL? Need benchmark.
            $lifeSession = $this->getRediska()->get($sessions);
            foreach($sessions as $session) {
                if (!isset($lifeSession[$session])) {
                    $sessionWithoutPrefix = substr($session, strlen($this->_options['keyprefix']));
                    $this->_set->remove($sessionWithoutPrefix);
                }
            }
        }

        return true;
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