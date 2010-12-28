<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../../Rediska.php';

/**
 * Sessions set
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage ZendFrameworkIntegration
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Session_Set extends Rediska_Key_SortedSet
{
    /**
     * Save handler instance
     * 
     * @var Rediska_Zend_Session_SaveHandler_Redis
     */
    protected static $_saveHandler;

    /**
     * Session set constructor
     */
    public function __construct()
    {
        if (!self::$_saveHandler) {
            throw new Rediska_Key_Exception('You must initialize Rediska_Zend_Session_SaveHandler_Redis before');
        }

        $keyName = self::getSaveHandler()->getOption('keyPrefix') . 'sessions';
        $options = array('rediska' => self::getSaveHandler()->getRediska());

        parent::__construct($keyName, $options);
    }

    /**
     * Get Redis session save handler
     * 
     * @return Rediska_Zend_Session_SaveHandler_Redis
     */
    public static function getSaveHandler()
    {
        return self::$_saveHandler;
    }

    /**
     * Set Redis session save handler
     * 
     * @param Rediska_Zend_Session_SaveHandler_Redis $saveHandler
     * @return boolean
     */
    public static function setSaveHandler(Rediska_Zend_Session_SaveHandler_Redis $saveHandler)
    {
        self::$_saveHandler = $saveHandler;

        return true;
    }
}