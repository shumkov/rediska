<?php

/**
 * Rediska autoloader
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Autoloader
{
    /**
     * Is registered Rediska autoload
     *
     * @var boolean
     */
    protected static $_isRegistered;

    /**
     * Rediska path
     *
     * @var string
     */
    protected static $_rediskaPath;

    /**
     * Autoload callback
     *
     * @var array
     */
    protected static $_callback = array('Rediska_Autoloader', 'load');

    /**
     * Register Rediska autoload
     *
     * @return boolean
     */
    public static function register()
    {
        if (self::isRegistered()) {
            return false;
        }

        self::$_isRegistered = spl_autoload_register(self::$_callback);

        return self::$_isRegistered;
    }

    /**
     * Unregister Rediska autoload
     *
     * @return boolean
     */
    public static function unregister()
    {
        if (!self::isRegistered()) {
            return false;
        }

        self::$_isRegistered = !spl_autoload_unregister(self::$_callback);

        return self::$_isRegistered;
    }

    /**
     * Is Rediska autoload registered
     *
     * @return boolean
     */
    public static function isRegistered()
    {
        return self::$_isRegistered;
    }

    /**
     * Load class
     *
     * @param string $className
     */
    public static function load($className)
    {
        if (0 !== strpos($className, 'Rediska')) {
            return false;
        }

        $path = self::getRediskaPath() . '/' . str_replace('_', '/', $className) . '.php';

        return include $path;
    }

    /**
     * Get Rediska path
     *
     * @return string
     */
    public static function getRediskaPath()
    {
        if (!self::$_rediskaPath) {
            self::$_rediskaPath = realpath(dirname(__FILE__) . '/..');
        }

        return self::$_rediskaPath;
    }
}
