<?php

/**
 * Rediska connection timeout exception
 * (throws only on read timeout, not when connect)
 * 
 * @author Yuriy Bogdanov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Connection_TimeoutException extends Rediska_Connection_Exception
{
	
}