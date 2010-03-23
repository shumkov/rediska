<?php

/**
 * @see Rediska_Key_List
 */
require_once 'Rediska/Key/List.php';

/**
 * @see Zend_Log_Writer_Abstract
 */
require_once 'Zend/Log/Writer/Abstract.php';

/**
 * Redis writer for Zend_Log
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
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
     * @param string            $keyName Log key name
     * @param Zend_Config|array $options Rediska options
     */
    public function __construct($keyName, $options = array())
    {
    	if ($options instanceof Zend_Config) {
    		$options = $options->toArray();
    	}

        $defaultInstance = Rediska::getDefaultInstance();
        if (empty($options) && $defaultInstance) {
            $rediska = $defaultInstance;
        } else {
            $rediska = new Rediska($options);
        }

        $this->_list = new Rediska_Key_List($keyName);
        $this->_list->setRediska($rediska);
    }

    /**
     * Formatting is not possible on this writer
     */
    public function setFormatter($formatter)
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

        if (!isset($config['options'])) {
            $config['options'] = array();
        }

        return new self($config['keyName'], $config['options']);
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
