<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../../../Rediska.php';

/**
 * @see Zend_Log_Writer_Abstract
 */
require_once 'Zend/Log/Writer/Abstract.php';

/**
 * Redis writer for Zend_Log
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage ZendFrameworkIntegration
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Log_Writer_Redis extends Zend_Log_Writer_Abstract
{
    /**
     * Log list
     * 
     * @var Rediska_Key_List
     */
    protected $_list;

    /**
     * Writer constructor
     * 
     * @param string $keyName Log key name
     * @param mixed  $rediska Rediska instance name, Rediska object or array of options
     */
    public function __construct($keyName, $rediska = Rediska::DEFAULT_NAME)
    {
        if ($rediska instanceof Zend_Config) {
            $rediska = $rediska->toArray();
        }

        $this->_list = new Rediska_Key_List($keyName, array('rediska' => $rediska));
    }

    /**
     * Formatting is not possible on this writer
     */
    public function setFormatter(Zend_Log_Formatter_Interface $formatter)
    {
        require_once 'Zend/Log/Exception.php';
        throw new Zend_Log_Exception(get_class() . ' does not support formatting');
    }

    /**
     * Remove reference to list
     *
     * @return void
     */
    public function shutdown()
    {
        $this->_list = null;
    }
    
    /**
     * Create a new instance of Rediska_Zend_Log_Writer_Redis
     * 
     * @param  array|Zend_Config $config
     * @return Zend_Log_Writer_Mock
     * @throws Zend_Log_Exception
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        
        if (!isset($config['keyName'])) {
            throw new Zend_Log_Exception('keyName not present');
        }

        if (!isset($config['rediska'])) {
            $config['rediska'] = Rediska::DEFAULT_NAME;
        }

        return new self($config['keyName'], $config['rediska']);
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     */
    protected function _write($event)
    {
        $this->_list[] = $event;
    }
}
