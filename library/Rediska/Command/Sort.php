<?php

/**
 * Get sorted elements contained in the List, Set, or Sorted Set value at key.
 * 
 * @param string        $name  Key name
 * @param string|array  $value Options or SORT query string (http://code.google.com/p/redis/wiki/SortCommand).
 *                             Important notes for SORT query string:
 *                                 1. If you set Rediska namespace option don't forget add it to key names.
 *                                 2. If you use more then one connection to Redis servers, it will choose by key name,
 *                                    and key by you pattern's may not present on it.
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Sort extends Rediska_Command_Abstract
{
    const ASC  = 'asc';
    const DESC = 'desc';
    
    protected $_options = array(
        'order'  => self::ASC,
        'limit'  => null,
        'offset' => null,
        'alpha'  => false,
        'by'     => null,
        'get'    => null,   
        'store'  => null, 
    );

    protected function _create($name, $options = array())
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "SORT {$this->_rediska->getOption('namespace')}$name";

        if (!is_string($options)) {
            foreach($options as $name => $value) {
                if (!array_key_exists($name, $this->_options)) {
                    throw new Rediska_Command_Exception("Unknown option '$name'");
                }
                $this->_options[$name] = $value;
            }

            // Limit
            if (isset($this->_options['limit'])) {
                $offset = isset($this->_options['offset']) ? $this->_options['offset'] : 0;
                $command .= " LIMIT $offset {$this->_options['limit']}";
            }

            // Alpha
            if ($this->_options['alpha']) {
                $command .= " ALPHA";
            }

            // Order
            if (isset($this->_options['order'])) {
                $command .= ' ' . strtoupper($this->_options['order']);
            }

            // By
            if (isset($this->_options['by'])) {
                $this->_throwExceptionIfManyConnections('by');
                $command .= " BY {$this->_rediska->getOption('namespace')}{$this->_options['by']}";
            }

            // Get
            if (isset($this->_options['get'])) {
                $this->_throwExceptionIfManyConnections('get');
                $command .= " GET";
                if (!is_string($this->_options['get'])) {
                    foreach($this->_options['get'] as $pattern) {
                        $command .= ' ' . $this->_addNamespaceToGetIfNeeded($pattern);
                    }
                } else {
                    $command .= ' ' . $this->_addNamespaceToGetIfNeeded($this->_options['get']);
                }
            }

            // Store
            if (isset($this->_options['store'])) {
                $this->_throwExceptionIfManyConnections('store');
                $command .= " STORE {$this->_rediska->getOption('namespace')}{$this->_options['store']}";
            }
        } else {
            $command .= ' ' . $options;
        }

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _addNamespaceToGetIfNeeded($pattern)
    {
        if ($pattern != '#') {
            $pattern = $this->_rediska->getOption('namespace') . $pattern;
        } else {
            $this->_checkVersion('1.1');
        }

        return $pattern;
    }

    protected function _throwExceptionIfManyConnections($argument)
    {
        $connections = $this->_rediska->getConnections();
        if (count($connections) > 1) {
            throw new Rediska_Command_Exception("You can use '$argument' with only one connection. Use 'on' method to specify it.");
        }
    }

    protected function _parseResponses($responses)
    {
        $values = $responses[0];

        foreach($values as &$value) {
            $value = $this->_rediska->unserialize($value);
        }

        return $values;
    }
}