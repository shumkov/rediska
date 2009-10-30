<?php

/**
 * @see Rediska_Key_Set
 */
require_once 'Rediska/Key/Set.php';

/**
 * Sessions set
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.2.1
 * @link http://code.google.com/p/rediska
 * @licence http://opensource.org/licenses/gpl-3.0.html
 */
class Rediska_Zend_Session_Set extends Rediska_Key_Set
{
	/**
	 * Save handler instance
	 * 
	 * @var Rediska_Zend_Session_SaveHandler_Redis
	 */
	protected static $_saveHandler;

	public function __construct(Rediska_Zend_Session_SaveHandler_Redis $saveHandler = null)
	{
		if (!is_null($saveHandler)) {
			self::$_saveHandler = $saveHandler;
		} else if (self::$_saveHandler) {
			$saveHandler = self::$_saveHandler;
		} else {
			throw new Rediska_Key_Exception('You must initialize Rediska_Zend_Session_SaveHandler_Redis before');
		}

		$this->setRediska($saveHandler->getRediska());

		parent::__construct($saveHandler->getOption('keyPrefix') . 'sessions');
	}
}