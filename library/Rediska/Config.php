<?php

/**
 * Redis server config
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Config implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Rediska instance
     * 
     * @var Rediska
     */
    protected $_rediska;

    /**
     * Rediska specified connection instance
     * 
     * @var Rediska_Connection
     */
    protected $_connection;

    /**
     * Constructor
     * 
     * @param Rediska            $rediska    Rediska instance
     * @param Rediska_Connection $connection Config connection
     */
    public function __construct(Rediska $rediska, Rediska_Connection $connection)
    {
        $this->_rediska    = $rediska;
        $this->_connection = $connection;

        $this->_throwIfNotSupported();
    }

    /**
     * Get parameter or parameters by pattern
     *
     * @param string $nameOrPattern Name or pattern
     * @return string|array
     */
    public function get($nameOrPattern)
    {
        $configGet = new Rediska_Connection_Exec($this->_connection, array('CONFIG', 'GET', $nameOrPattern));
        $valuesAfterNames = $configGet->execute();

        if (preg_match('/\W/i', $nameOrPattern)) { // Pattern
            $isParameter = true;
            $namesAndValues = array();
            foreach($valuesAfterNames as $nameOrValue) {
                if ($isParameter) {
                    $name = $nameOrValue;
                } else {
                    $namesAndValues[$name] = $this->_sanitizeValue($nameOrValue);
                }

                $isParameter = !$isParameter;
            }

            return $namesAndValues;
        } else { // Parameter name
            if (empty($valuesAfterNames)) {
                return null;
            } else {
                return $this->_sanitizeValue($valuesAfterNames[1]);
            }
        }
    }

    /**
     * Magic for get a parameter
     * @param string $name Name
     * @return string
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Array magic for get a parameter
     * @param string $nameOrPattern Name or pattern
     * @return string
     */
    public function offsetGet($nameOrPattern)
    {
        return $this->get($nameOrPattern);
    }

    /**
     * Set value to parameter
     *
     * @param string $name  Name
     * @param string $value Value
     * @return Rediska_Config
     */
    public function set($name, $value)
    {
        $exec = new Rediska_Connection_Exec($this->_connection, array('CONFIG', 'SET', $name, $value));
        $exec->execute();

        return $this;
    }

    /**
     * Magic for set a value to parameter
     *
     * @param string $name  Name
     * @param mixed  $value Value
     * @return $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);

        return $value;
    }

    /**
     * Array magic for set a value to parameter
     *
     * @param string $field
     * @param mixed $value
     * @return boolean
     */
    public function offsetSet($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Get all parameters as array
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->get('*');
    }

    /* Countable implementation */

    public function count()
    {
        $exec = new Rediska_Connection_Exec($this->_connection, array('CONFIG', 'GET', '*'));
        $namesAndValues = $exec->execute();

        return count($namesAndValues) / 2;
    }

    /* IteratorAggregate implementation */

    public function getIterator()
    {
        return new ArrayObject($this->toArray());
    }
    
    /* ArrayAccess implementation */

    public function offsetExists($offset)
    {
        throw new Rediska_Exception('Not implemented!');
    }

    public function offsetUnset($offset)
    {
        throw new Rediska_Exception('Not implemented!');
    }

    /**
     * Sanitize value
     * 
     * @param string $value
     * @return string|integer|float
     */
    protected function _sanitizeValue($value)
    {
        $value = trim($value);

        // Try to check number
        if (strpos($value, '.') !== false) {
            $number = (integer)$value;
        } else {
            $number = (float)$value;
        }

        if ((string)$number == $value) {
            $value = $number;
        }

        return $value;
    }

    /**
     * Throw if config not supported by Redis
     */
    protected function _throwIfNotSupported()
    {
        $version = '2.0';
        $redisVersion = $this->_rediska->getOption('redisVersion');
        if (version_compare($version, $this->_rediska->getOption('redisVersion')) == 1) {
            throw new Rediska_Transaction_Exception("Transaction requires {$version}+ version of Redis server. Current version is {$redisVersion}. To change it specify 'redisVersion' option.");
        }
    }
}