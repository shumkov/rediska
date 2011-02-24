<?php

/**
 * Get sorted elements contained in the List, Set, or Sorted Set value at key.
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
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

    /**
     * Create command
     *
     * @param string        $key   Key name
     * @param array         $value Options:
     *                               * order
     *                               * limit
     *                               * offset
     *                               * alpha
     *                               * get
     *                               * by
     *                               * store
     *
     *                              See more: http://code.google.com/p/redis/wiki/SortCommand

     *                              If you use more then one connection to Redis servers,
     *                              it will choose by key name, and key by you pattern's may not present on it.
     *
     * @return Rediska_Connection_Exec
     */
    public function create($key, array $options = array())
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        $command = array('SORT',
                         $this->_rediska->getOption('namespace') . $key);

        foreach($options as $name => $value) {
            if (!array_key_exists($name, $this->_options)) {
                throw new Rediska_Command_Exception("Unknown option '$name'");
            }
            $this->_options[$name] = $value;
        }

        // Limit
        if (isset($this->_options['limit'])) {
            $offset = isset($this->_options['offset']) ? $this->_options['offset'] : 0;
            $command[] = 'LIMIT';
            $command[] = $offset;
            $command[] = $this->_options['limit'];
        }

        // Alpha
        if ($this->_options['alpha']) {
            $command[] = 'ALPHA';
        }

        // Order
        if (isset($this->_options['order'])) {
            $command[] = strtoupper($this->_options['order']);
        }

        // By
        if (isset($this->_options['by'])) {
            $this->_throwExceptionIfManyConnections('by');
            $command[] = 'BY';
            $command[] = $this->_rediska->getOption('namespace') . $this->_options['by'];
        }

        // Get
        if (isset($this->_options['get'])) {
            $this->_throwExceptionIfManyConnections('get');
            if (!is_string($this->_options['get'])) {
                foreach($this->_options['get'] as $pattern) {
                    $command[] = 'GET';
                    $command[] = $this->_addNamespaceToGetIfNeeded($pattern);
                }
            } else {
                $command[] = 'GET';
                $command[] = $this->_addNamespaceToGetIfNeeded($this->_options['get']);
            }
        }

        // Store
        if (isset($this->_options['store'])) {
            $this->_throwExceptionIfManyConnections('store');
            $command[] = 'STORE';
            $command[] = $this->_rediska->getOption('namespace') . $this->_options['store'];
        }

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param array $response
     * @return array
     */
    public function parseResponse($response)
    {
        return array_map(array($this->_rediska->getSerializer(), 'unserialize'), $response);
    }

    protected function _addNamespaceToGetIfNeeded($pattern)
    {
        if ($pattern != '#') {
            $pattern = $this->_rediska->getOption('namespace') . $pattern;
        } else {
            $this->_throwExceptionIfNotSupported('1.1');
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
}