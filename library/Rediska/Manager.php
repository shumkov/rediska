<?php

// Require Rediska
require_once dirname(__FILE__) . '/../Rediska.php';

/**
 * Rediska instances manager
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Manager
{
    /**
     * Rediska instances
     *
     * @var array
     */
    protected static $_instances = array();

    /**
     * Add Rediska
     * Return true if new instance and false if instance overwrited
     *
     * @param Rediska $rediska Rediska instance or options
     * @return boolean
     */
    public static function add($rediska)
    {
        if ($rediska instanceof Rediska) {
            if (!self::has($rediska->getName())) {
                foreach(self::$_instances as $name => $instance) {
                    if ($instance === $rediska && $name != $rediska->getName()) {
                        unset(self::$_instances[$name]);
                        break;
                    }
                }
            }
            $name = $rediska->getName();
        } else if (is_array($rediska)) {
            if (isset($rediska['name'])) {
                $name = $rediska['name'];
            } else {
                $name = Rediska::DEFAULT_NAME;
            }
        } else {
            throw new Rediska_Exception('Rediska must be a instance or options');
        }

        if (self::has($name)) {
            $result = false;
        } else {
            $result = true;
        }

        self::$_instances[$name] = $rediska;

        return $result;
    }

    /**
     * Has Rediska instances
     *
     * @param string $name
     */
    public static function has($name)
    {
        return isset(self::$_instances[$name]);
    }

    /**
     * Get Rediska
     *
     * @param string[optional] $name Instance name
     * @return Rediska
     */
    public static function get($name = Rediska::DEFAULT_NAME)
    {
        if (!self::has($name)) {
            throw new Rediska_Exception("Rediska instance '$name' not present");
        }

        self::_instanceFromOptions($name);

        return self::$_instances[$name];
    }

    /**
     * Get all Rediska instances
     *
     * @return array
     */
    public static function getAll()
    {
        foreach(self::$_instances as $name => $instanceOrOptions) {
            self::_instanceFromOptions($name);
        }

        return self::$_instances;
    }

    /**
     * Remove Rediska
     *
     * @param Rediska $rediska Rediska instance or options
     * @return boolean
     */
    public static function remove($rediska)
    {
        if ($rediska instanceof Rediska) {
            $name = $rediska->getName();
        } else if (is_string($rediska)) {
            $name = $rediska;
        } else {
            throw new Rediska_Exception('Rediska must be a instance or name');
        }

        if (!isset(self::$_instances[$name])) {
            throw new Rediska_Exception("Rediska instance '$name' not present");
        }

        unset(self::$_instances[$name]);

        return true;
    }

    /**
     * Remove all rediska
     * Return count of removed instance
     *
     * @return integer
     */
    public static function removeAll()
    {
        $count = count(self::$_instances);

        self::$_instances = array();

        return $count;
    }

    /**
     * Instance from options if not yet
     *
     * @static
     * @param  $name
     * @return void
     */
    protected static function _instanceFromOptions($name)
    {
        if (!is_object(self::$_instances[$name])) {
            $options = self::$_instances[$name];
            self::$_instances[$name] = new Rediska($options);
        }
    }
}