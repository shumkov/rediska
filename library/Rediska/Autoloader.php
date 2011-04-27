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
     * @param boolean $prepend Prepend the autoloader in the chain, the default is
     *                         'false'. This parameter is available since PHP 5.3.0
     *                         and will be silently disregarded otherwise.
     * @return boolean
     */
    public static function register($prepend = false)
    {
        if (self::isRegistered()) {
            return false;
        }
        if (!is_bool($prepend)) {
            $prepend = (bool) $prepend;
        }

        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            self::$_isRegistered = spl_autoload_register(self::$_callback);
        } else {
            self::$_isRegistered = spl_autoload_register(
                self::$_callback,
                true,
                $prepend
            );
        }
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
     *
     * @return boolean
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
