<?php

// Register autoloader
require_once dirname(__FILE__) . '/Rediska/Autoloader.php';
Rediska_Autoloader::register();

/**
 * Rediska (radish on russian) - PHP client 
 * for key-value database Redis (http://code.google.com/p/redis)
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska extends Rediska_Options
{
    /**
     * End of line
     * 
     * @var string
     */
    const EOL = "\r\n";

    /**
     * Current stable Redis version
     * 
     * @var string
     */
    const STABLE_REDIS_VERSION = '2.2.1';

    /**
     * Default client name
     *
     * @var string
     */
    const DEFAULT_NAME = 'default';

    /**
     * Connections
     * 
     * @var array
     */
    protected $_connections = array();

    /**
     * Proxy object for specified connection
     * 
     * @var Rediska_Connection_Specified
     */
    protected $_specifiedConnection;

    /**
     * Object for distribution keys by servers 
     * 
     * @var Rediska_KeyDistributor_Interface
     */
    protected $_keyDistributor;

    /**
     * Serializer object
     * 
     * @var Rediska_Serializer
     */
    protected $_serializer;

    /**
     * Profiler object
     *
     * @var Rediska_Profiler
     */
    protected $_profiler;

    /**
     * Configuration
     *
     * name              - Rediska instance name. See Rediska_Manager
     * addToManager      - Add instance to Rediska_Manager
     * namespace         - Key names prefix
     * servers           - Array of servers: array(
     *                                        array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 1, 'password' => '123', 'alias' => 'example'),
     *                                        'alias' => array('host' => '127.0.0.1', 'port' => 6380, 'weight' => 2)
     *                                       )
     * serializerAdapter - Value's serialize method. For default 'phpSerialize' (PHP serialize functions).
     *                     You may use 'json' or you personal serializer class
     *                     which implements Rediska_Serializer_Adapter_Interface
     * keyDistributor    - Algorithm of keys distribution on redis servers.
     *                     For default 'consistentHashing' which implement
     *                     consistent hashing algorithm (http://weblogs.java.net/blog/tomwhite/archive/2007/11/consistent_hash.html)
     *                     You may use basic 'crc32' (crc32(key) % servers_count) algorithm
     *                     or you personal implementation (option value - name of class
     *                     which implements Rediska_KeyDistributor_Interface).
     * redisVersion      - Redis server version for command specification.
     * profiler          - Rediska profiler. Disable for default. Don't use it on production!
     *                     Value of this option may be:
     *                         * True or false
     *                         * Object wich implements Rediska_Profiler_Interface
     *                         * Array with key 'name' wich value is name of profiler ('stream' for example)
     *                           or class name wich implements Rediska_Profiler_Interface. Other keys passed
     *                           as options to profiler
     *
     * @var array
     */
    protected $_options = array(
        'addToManager' => true,
        'name'         => self::DEFAULT_NAME,
        'namespace'    => '',
        'servers'      => array(
            array(
                'host'   => Rediska_Connection::DEFAULT_HOST,
                'port'   => Rediska_Connection::DEFAULT_PORT,
                'weight' => Rediska_Connection::DEFAULT_WEIGHT,
            )
        ),
        'serializerAdapter' => 'phpSerialize',
        'keyDistributor'    => 'consistentHashing',
        'redisVersion'      => self::STABLE_REDIS_VERSION,
        'profiler'          => false,
    );

    /**
     * Contruct Rediska
     * 
     * @param array $options Options
     * 
     * name              - Rediska instance name. See Rediska_Manager
     * addToManager      - Add instance to Rediska_Manager
     * namespace         - Key names prefix
     * servers           - Array of servers: array(
     *                                        array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 1, 'password' => '123', 'alias' => 'example'),
     *                                        'alias' => array('host' => '127.0.0.1', 'port' => 6380, 'weight' => 2)
     *                                       )
     * serializerAdapter - Value's serialize method. For default 'phpSerialize' (PHP serialize functions).
     *                     You may use 'json' or you personal serializer class
     *                     which implements Rediska_Serializer_Interface
     * keyDistributor    - Algorithm of keys distribution on redis servers.
     *                     For default 'consistentHashing' which implement
     *                     consistent hashing algorithm (http://weblogs.java.net/blog/tomwhite/archive/2007/11/consistent_hash.html)
     *                     You may use basic 'crc32' (crc32(key) % servers_count) algorithm
     *                     or you personal implementation (option value - name of class
     *                     which implements Rediska_KeyDistributor_Interface).
     * redisVersion      - Redis server version for command specification.
     * profiler          - Rediska profiler. Disable for default. Don't use it on production!
     *                     Value of this option may be:
     *                         * True or false
     *                         * Object wich implements Rediska_Profiler_Interface
     *                         * Array with key 'name' wich value is name of profiler ('stream' for example)
     *                           or class name wich implements Rediska_Profiler_Interface. Other keys passed
     *                           as options to profiler
     * 
     */
    public function __construct(array $options = array()) 
    {
        parent::__construct($options);

        $this->_specifiedConnection = new Rediska_Connection_Specified($this);
    }

    /**
     * Set Rediska client name
     *
     * @param string $name
     * @return Rediska
     */
    public function setName($name)
    {
        $this->_options['name'] = $name;

        if ($this->_options['addToManager']) {
            Rediska_Manager::add($this);
        }

        return $this;
    }

    /**
     * Get Rediska client name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_options['name'];
    }

    /**
     * Set servers array:
     * 
     * array(
     *     array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 1),
     *     array('host' => '127.0.0.1', 'port' => 6380, 'weight' => 2)
     * )
     * 
     * @param array $servers
     * @return Rediska
     */
    public function setServers(array $servers)
    {
        $this->_connections = array();
        foreach($servers as $alias => $serverOptions) {
            if (!isset($serverOptions['alias']) && is_string($alias)) {
                $serverOptions['alias'] = $alias;
            } 

            $this->addServer(
                isset($serverOptions['host']) ? $serverOptions['host'] : Rediska_Connection::DEFAULT_HOST,
                isset($serverOptions['port']) ? $serverOptions['port'] : Rediska_Connection::DEFAULT_PORT,
                $serverOptions);
        }

        return $this;
    }

    /**
     * Add server
     * 
     * @throws Rediska_Exception
     * @param string $host Hostname or IP
     * @param integer $port Port
     * @param array $options Options see: Rediska_Connection
     * @return Rediska
     */
    public function addServer($host, $port = Rediska_Connection::DEFAULT_PORT, array $options = array())
    {
        if (!isset($options['alias'])) {
            $connectionString = "$host:$port";
        } else {
            $connectionString = $options['alias'];
        }

        if (array_key_exists($connectionString, $this->_connections)) {
            throw new Rediska_Exception("Server '$connectionString' already added.");
        }

        $options['host'] = $host;
        $options['port'] = $port;

        $this->_connections[$connectionString] = new Rediska_Connection($options);

        if ($this->_keyDistributor) {
            $this->_keyDistributor->addConnection(
                $connectionString,
                isset($options['weight']) ? $options['weight'] : Rediska_Connection::DEFAULT_WEIGHT
            );
        }

        return $this;
    }

    /**
     * Remove server
     *
     * @param  $alias
     * @return void
     */
    public function removeServer($aliasOrConnection)
    {
        if ($aliasOrConnection instanceof Rediska_Connection) {
            $alias = $aliasOrConnection->getAlias();
        }

        if (!isset($this->_connections[$alias])) {
            throw new Rediska_Exception("Can't find connection '$alias'");
        }

        unset($this->_connections[$alias]);

        if ($this->_keyDistributor) {
            $this->_keyDistributor->removeConnection($alias);
        }

        return $this;
    }

    /**
     * Get Rediska connection instance by key name
     * 
     * @throws Rediska_Connection_Exception
     * @param string $name Key name
     * @return Rediska_Connection
     */
    public function getConnectionByKeyName($name)
    {
        if ($this->_specifiedConnection->getConnection()) {
            $connection = $this->_specifiedConnection->getConnection();
        } elseif (count($this->_connections) == 1) {
            $connections = array_values($this->_connections);
            $connection = $connections[0];
        } else {
            $alias = $this->_keyDistributor->getConnectionByKeyName($name);
            $connection = $this->_connections[$alias];
        }

        return $connection;
    }

    /**
     * Get connection by alias
     * 
     * @param string $alias
     * @return Rediska_Connection
     */
    public function getConnectionByAlias($alias)
    {
        if (!isset($this->_connections[$alias])) {
            throw new Rediska_Exception("Can't find connection '$alias'");
        }

        return $this->_connections[$alias];
    }

    /**
     * Get all Rediska connection instances
     * 
     * @throws Rediska_Connection_Exception
     * @return array
     */
    public function getConnections()
    {
        if ($this->_specifiedConnection->getConnection()) {
            return array($this->_specifiedConnection->getConnection());
        } else {
            return array_values($this->_connections);
        }
    }

    /**
     * Chain method to work with keys on specified by alias server
     * 
     * @param $aliasOrConnection Alias or Rediska_Connection object
     * @return Rediska_Connection_Specified
     */
    public function on($aliasOrConnection)
    {
        if ($aliasOrConnection instanceof Rediska_Connection) {
            $connection = $aliasOrConnection;
        } else {
            $alias = $aliasOrConnection;
            $connection = $this->getConnectionByAlias($alias);
        }

        $this->_specifiedConnection->setConnection($connection);

        return $this->_specifiedConnection;
    }

    /**
     * Create pipeline
     * 
     * @return Rediska_Pipeline
     */
    public function pipeline()
    {
        return new Rediska_Pipeline($this, $this->_specifiedConnection);
    }

    /**
     * Create transaction
     * 
     * @param $aliasOrConnection Server alias or Rediska_Connection object
     * @return Rediska_Transaction
     */
    public function transaction($aliasOrConnection = null)
    {
        if ($aliasOrConnection instanceof Rediska_Connection) {
            $connection = $aliasOrConnection;
        } elseif ($aliasOrConnection !== null) {
            $connection = $this->getConnectionByAlias($aliasOrConnection);
        } elseif ($this->_specifiedConnection->getConnection()) {
            $connection = $this->_specifiedConnection->getConnection();
        } else {
            $connections = $this->getConnections();
            if (count($connections) == 1) {
                $connection = $connections[0];
            } else {
                throw new Rediska_Transaction_Exception('You must specify connection by $aliasOrConnection argument!');
            }
        }

        return new Rediska_Transaction($this, $this->_specifiedConnection, $connection);
    }

    /**
     * Subscribe to PubSub channel or channels
     * 
     * @param string|array      $channelOrChannels
     * @param integer[optional] $timeout
     * @return Rediska_PubSub_Channel
     */
    public function subscribe($channelOrChannels, $timeout = null)
    {
        return new Rediska_PubSub_Channel($channelOrChannels, array(
            'rediska'       => $this,
            'timeout'       => $timeout,
            'serverAlias'   => $this->_specifiedConnection->getConnection()
        ));
    }

    /**
     * Monitor commands
     *
     * @param integer[optional] $timeout Timeout
     * @return Rediska_Monitor
     */
    public function monitor($timeout = null)
    {
        return new Rediska_Monitor(array(
            'rediska'       => $this,
            'timeout'       => $timeout,
            'serverAlias'   => $this->_specifiedConnection->getConnection()
        ));
    }

    /**
     * Get Redis server configuration
     *
     * @param $aliasOrConnection Server alias or Rediska_Connection object
     * @return Rediska_Config
     */
    public function config($aliasOrConnection = null)
    {
        if ($aliasOrConnection instanceof Rediska_Connection) {
            $connection = $aliasOrConnection;
        } elseif ($aliasOrConnection !== null) {
            $connection = $this->getConnectionByAlias($aliasOrConnection);
        } elseif ($this->_specifiedConnection->getConnection()) {
            $connection = $this->_specifiedConnection->getConnection();
        } else {
            $connections = $this->getConnections();
            if (count($connections) == 1) {
                $connection = $connections[0];
            } else {
                throw new Rediska_Transaction_Exception('You must specify connection by $aliasOrConnection argument!');
            }
        }

        return new Rediska_Config($this, $connection);
    }

    /**
     * Set key distributor.
     * See options description for more information.
     * 
     * @throws Rediska_Exception
     * @param string $name Object or name of key distributor (crc32, consistentHashing or you personal class)
     * @return rediska
     */
    public function setKeyDistributor($name)
    {
        $this->_options['keyDistributor'] = $name;

        if (is_object($name)) {
            $this->_keyDistributor = $name;
        } else if (in_array($name, array('crc32', 'consistentHashing'))) {
            $name = ucfirst($name);
            $className = "Rediska_KeyDistributor_$name";
            $this->_keyDistributor = new $className;
        } else {
            if (!@class_exists($name)) {
                throw new Rediska_Exception("Key distributor '$name' not found. You need include it before or setup autoload.");
            }
            $this->_keyDistributor = new $name;
        }

        if (!$this->_keyDistributor instanceof Rediska_KeyDistributor_Interface) {
            throw new Rediska_Exception("'$name' must implement Rediska_KeyDistributor_Interface");
        }

        // Add available connections
        foreach($this->_connections as $connectionString => $connection) {
            $this->_keyDistributor->addConnection($connectionString);
        }

        return $this;
    }

    /**
     * Set Rediska serializer adapter
     * 
     * @param mixed $serializer
     * @return Rediska
     */
    public function setSerializerAdapter($adapter)
    {
        $this->_options['serializerAdapter'] = $adapter;
        $this->_serializer = null;

        return $this;
    }

    /**
     * Get Rediska serializer
     * 
     * @return Rediska_Serializer
     */
    public function getSerializer()
    {
        if ($this->_serializer === null) {
            $this->_serializer = new Rediska_Serializer($this->_options['serializerAdapter']);
        }

        return $this->_serializer;
    }

    /**
     * Set profiler
     *
     * @param Rediska_Profiler|array $profilerOrOptions Profiler object or array of options
     * @return Rediska
     */
    public function setProfiler($profilerOrOptions)
    {
        $this->_options['profiler'] = $profilerOrOptions;
        $this->_profiler = null;
        
        return $this;
    }

    /**
     * Get profiler
     *
     * @return Rediska_Profiler
     */
    public function getProfiler()
    {
        if (!$this->_profiler) {
            if (is_string($this->_options['profiler'])) {
                $this->_options['profiler'] = array(
                    'name' => $this->_options['profiler']
                );
            }

            if ($this->_options['profiler'] === false) {
                $this->_profiler = new Rediska_Profiler_Null();
            } else if ($this->_options['profiler'] === true) {
                $this->_profiler = new Rediska_Profiler();
            } else if (is_array($this->_options['profiler'])) {
                if (!isset($this->_options['profiler']['name'])) {
                    throw new Rediska_Exception("You must specify profiler 'name'.");
                } else if (in_array($this->_options['profiler']['name'], array('stream'))) {
                    $name = ucfirst($this->_options['profiler']['name']);
                    $className = "Rediska_Profiler_$name";
                    $options = $this->_options['profiler'];
                    unset($options['name']);
                    $this->_profiler = new $className($options);
                } else if (@class_exists($this->_options['profiler']['name'])) {
                    $className = $this->_options['profiler']['name'];
                    $options = $this->_options['profiler'];
                    unset($options['name']);
                    $this->_profiler = new $className($options);
                } else {
                    throw new Rediska_Exception("Profiler '{$this->_options['profiler']['name']}' not found. You need include it before or setup autoload.");
                }
            } elseif (is_object($this->_options['profiler'])) {
                $this->_profiler = $this->_options['profiler'];
            } else {
                throw new Rediska_Exception("Profiler option must be a boolean, object or array of options");
            }

            if (!$this->_profiler instanceof Rediska_Profiler_Interface) {
                $profilerClass = get_class($this->_profiler);
                throw new Rediska_Serializer_Exception("Profiler '$profilerClass' must implement Rediska_Profiler_Interface");
            }
        }

        return $this->_profiler;
    }

    /**
     * Magic method for execute command
     *
     * @param string $name Command name
     * @param array  $args  Command arguments
     * @return mixed
     */
    public function __call($name, $args)
    {
        return $this->_executeCommand($name, $args);
    }

    /**
     * Execute command
     *
     * @param string $name Command name
     * @param array  $args  Command arguments
     * @return mixed
     */
    protected function _executeCommand($name, $args = array())
    {        
        $this->_specifiedConnection->resetConnection();

        $command = Rediska_Commands::get($this, $name, $args);

        $this->getProfiler()->start($command);

        $response = $command->execute();

        $this->getProfiler()->stop();

        unset($command);

        return $response;
    }

    /**
     *  Deprecated
     */

    /**
    * Get Rediska default instance
    *
    * @deprecated
    */
    public static function getDefaultInstance()
    {
        throw new Rediska_Exception("Rediska::getDefaultInstance() is deprecated. Use Rediska_Manager::get()");
    }

   /**
    * Set Rediska default instance
    *
    * @deprecated
    */
    public static function setDefaultInstace(Rediska $instance)
    {
        throw new Rediska_Exception("Rediska::setDefaultInstance() is deprecated. Use Rediska_Manager::set()");
    }

   /**
    * Serialize value
    *
    * @deprecated
    */
    public function serialize($value)
    {
        throw new Rediska_Exception("Rediska#serialize(\$value) is deprecated. Use Rediska#getSerializer()->serialize(\$value)");
    }

   /**
    * Unserailize value
    *
    * @deprecated
    */
    public function unserialize($value)
    {
        throw new Rediska_Exception("Rediska#unserialize(\$value) is deprecated. Use Rediska#getSerializer()->unserialize(\$value)");
    }

       /**
    * Set serializer callback
    * For example: "unserializer" or array($object, "unserializer")
    *
    * @deprecated
    */
    public function setSerializer($serializer)
    {
        throw new Rediska_Exception("Serializer is deprecated. Use 'serializerAdapter' option to set phpSerializer, json or you personal class which implements Rediska_Serialize_Adapter_Interface");
    }

   /**
    * Set unserializer callback
    * For example: "unserializer" or array($object, "unserializer")
    *
    * @deprecated
    */
    public function setUnserializer($serializer)
    {
        throw new Rediska_Exception("Unserializer is deprecated. Use 'serializerAdapter' option to set phpSerializer, json or you personal class which implements Rediska_Serialize_Adapter_Interface");
    }

    /**
     * Generated command methods by 'scripts/add_command_methods.php'
     */

    /**
     * Ask the server to silently close the connection.
     *
     * @return mixed
     */
    public function quit() { $args = func_get_args(); return $this->_executeCommand('quit', $args); }

    /**
     * Test if a key exists
     *
     * @param string $key Key name
     * @return boolean
     */
    public function exists($key) { $args = func_get_args(); return $this->_executeCommand('exists', $args); }

    /**
     * Delete a key or keys
     *
     * @param string|array $keyOrKeys Key name or array of key names
     * @return mixed
     */
    public function delete($keyOrKeys) { $args = func_get_args(); return $this->_executeCommand('delete', $args); }

    /**
     * Get key type
     *
     * @param string $key Key name
     * @return mixed
     */
    public function getType($key) { $args = func_get_args(); return $this->_executeCommand('getType', $args); }

    /**
     * Returns all the keys matching the glob-style pattern
     * Glob style patterns examples:
     *   h?llo will match hello hallo hhllo
     *   h*llo will match hllo heeeello
     *   h[ae]llo will match hello and hallo, but not hillo
     *
     * @param string $pattern Pattern
     * @return mixed
     */
    public function getKeysByPattern($pattern) { $args = func_get_args(); return $this->_executeCommand('getKeysByPattern', $args); }

    /**
     * Return a random key from the key space
     *
     * @return string|null
     */
    public function getRandomKey() { $args = func_get_args(); return $this->_executeCommand('getRandomKey', $args); }

    /**
     * Rename the old key in the new one
     *
     * @param string            $oldKey    Old key name
     * @param string            $newKey    New key name
     * @param boolean[optional] $overwrite Overwrite the new name key if it already exists. For default is false.
     * @return mixed
     */
    public function rename($oldKey, $newKey, $overwrite = true) { $args = func_get_args(); return $this->_executeCommand('rename', $args); }

    /**
     * Get the number of keys
     *
     * @return mixed
     */
    public function getKeysCount() { $args = func_get_args(); return $this->_executeCommand('getKeysCount', $args); }

    /**
     * Set a time to live in seconds or timestamp on a key
     *
     * @param string  $key                   Key name
     * @param integer $secondsOrTimestamp    Time in seconds or timestamp
     * @param boolean $isTimestamp[optional] Time is timestamp. For default is false.
     * @return boolean
     */
    public function expire($key, $secondsOrTimestamp, $isTimestamp = false) { $args = func_get_args(); return $this->_executeCommand('expire', $args); }

    /**
     * Get key lifetime
     *
     * @param string $key Key name
     * @return string|null
     */
    public function getLifetime($key) { $args = func_get_args(); return $this->_executeCommand('getLifetime', $args); }

    /**
     * Select the DB having the specified index
     *
     * @param integer $index Db index
     * @return mixed
     */
    public function selectDb($index) { $args = func_get_args(); return $this->_executeCommand('selectDb', $args); }

    /**
     * Move the key from the currently selected DB to the DB having as index dbindex
     *
     * @param string  $key     Key name
     * @param integer $dbIndex Redis DB index
     * @return boolean
     */
    public function moveToDb($key, $dbIndex) { $args = func_get_args(); return $this->_executeCommand('moveToDb', $args); }

    /**
     * Remove all the keys of the currently selected DB
     *
     * @param boolean[optional] $all Remove from all Db. For default is false.
     * @return boolean
     */
    public function flushDb($all = false) { $args = func_get_args(); return $this->_executeCommand('flushDb', $args); }

    /**
     * Set value to a key or muliple values to multiple keys
     *
     * @param string|array $keyOrData                  Key name or array with key => value.
     * @param mixed        $valueOrOverwrite[optional] Value or overwrite property for array of values. For default true.
     * @param boolean      $overwrite[optional]        Overwrite for single value (if false don't set and return false if key already exist). For default true.
     * @return mixed
     */
    public function set($keyOrData, $valueOrOverwrite = null, $overwrite = true) { $args = func_get_args(); return $this->_executeCommand('set', $args); }

    /**
     * Set + Expire atomic command
     *
     * @param string  $key      Key name
     * @param mixed   $value    Value
     * @param integer $seconds  Expire time in seconds
     * @return mixed
     */
    public function setAndExpire($key, $value, $seconds) { $args = func_get_args(); return $this->_executeCommand('setAndExpire', $args); }

    /**
     * Atomic set value and return old 
     *
     * @param string $key   Key name
     * @param mixed  $value Value
     * @return mixed
     */
    public function setAndGet($key, $value) { $args = func_get_args(); return $this->_executeCommand('setAndGet', $args); }

    /**
     * Get value of key or array of values by array of keys
     *
     * @param string|array $keyOrKeys Key name or array of names
     * @return mixed
     */
    public function get($keyOrKeys) { $args = func_get_args(); return $this->_executeCommand('get', $args); }

    /**
     * Append value to a end of string key
     *
     * @param string $key    Key name
     * @param mixed  $value  Value
     * @return mixed
     */
    public function append($key, $value) { $args = func_get_args(); return $this->_executeCommand('append', $args); }

    /**
     * Increment the number value of key by integer
     *
     * @param string            $key    Key name
     * @param integer[optional] $amount Amount to increment. One for default
     * @return mixed
     */
    public function increment($key, $amount = 1) { $args = func_get_args(); return $this->_executeCommand('increment', $args); }

    /**
     * Decrement the number value of key by integer
     *
     * @param string            $key    Key name
     * @param integer[optional] $amount Amount to decrement. One for default
     * @return mixed
     */
    public function decrement($key, $amount = 1) { $args = func_get_args(); return $this->_executeCommand('decrement', $args); }

    /**
     * Overwrite part of a string at key starting at the specified offset
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @param integer $value  Value
     * @return mixed
     */
    public function setRange($key, $offset, $value) { $args = func_get_args(); return $this->_executeCommand('setRange', $args); }

    /**
     * Return a subset of the string from offset start to offset end (both offsets are inclusive)
     *
     * @param string            $key   Key name
     * @param integer           $start Start
     * @param integer[optional] $end   End. If end is omitted, the substring starting from $start until the end of the string will be returned. For default end of string
     * @return mixin
     */
    public function getRange($key, $start, $end = -1) { $args = func_get_args(); return $this->_executeCommand('getRange', $args); }

    /**
     * Return a subset of the string from offset start to offset end (both offsets are inclusive)
     *
     * @param string            $key   Key name
     * @param integer           $start Start
     * @param integer[optional] $end   End. If end is omitted, the substring starting from $start until the end of the string will be returned. For default end of string
     * @return mixin
     */
    public function substring($key, $start, $end = -1) { $args = func_get_args(); return $this->_executeCommand('substring', $args); }

    /**
     * Returns the bit value at offset in the string value stored at key
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @param integer $bit    Bit (0 or 1)
     * @return mixed
     */
    public function setBit($key, $offset, $bit) { $args = func_get_args(); return $this->_executeCommand('setBit', $args); }

    /**
     * Returns the bit value at offset in the string value stored at key
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @return mixed
     */
    public function getBit($key, $offset) { $args = func_get_args(); return $this->_executeCommand('getBit', $args); }

    /**
     * Returns the length of the string value stored at key
     *
     * @param string  $key Key name
     * @return mixed
     */
    public function getLength($key) { $args = func_get_args(); return $this->_executeCommand('getLength', $args); }

    /**
     * Append value to the end of List
     *
     * @param string            $key                Key name
     * @param mixed             $value              Element value
     * @param boolean[optional] $createIfNotExists  Create list if not exists
     * @return mixed
     */
    public function appendToList($key, $value, $createIfNotExists = true) { $args = func_get_args(); return $this->_executeCommand('appendToList', $args); }

    /**
     * Append value to the head of List
     *
     * @param string            $key                Key name
     * @param mixed             $value              Element value
     * @param boolean[optional] $createIfNotExists  Create list if not exists
     * @return mixed
     */
    public function prependToList($key, $value, $createIfNotExists = true) { $args = func_get_args(); return $this->_executeCommand('prependToList', $args); }

    /**
     * Return the length of the List value at key
     *
     * @param string $key Key name
     * @return mixed
     */
    public function getListLength($key) { $args = func_get_args(); return $this->_executeCommand('getListLength', $args); }

    /**
     * Get List by key
     *
     * @param string  $key                         Key name
     * @param integer $start[optional]             Start index. For default is begin of list
     * @param integer $end[optional]               End index. For default is end of list
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created 
     * @return array
     */
    public function getList($key, $start = 0, $end = -1, $responseIterator = false) { $args = func_get_args(); return $this->_executeCommand('getList', $args); }

    /**
     * Trim the list at key to the specified range of elements
     *
     * @param string  $key   Key name
     * @param integer $start Start index
     * @param integer $end   End index
     * @return boolean
     */
    public function truncateList($key, $start, $end) { $args = func_get_args(); return $this->_executeCommand('truncateList', $args); }

    /**
     * Return element of List by index at key
     *
     * @param string  $key   Key name
     * @param integer $index Index
     * @return mixed
     */
    public function getFromList($key, $index) { $args = func_get_args(); return $this->_executeCommand('getFromList', $args); }

    /**
     * Set a new value as the element at index position of the List at key
     *
     * @param string  $key   Key name
     * @param mixed   $value Value
     * @param integer $index Index
     * @return boolean
     */
    public function setToList($key, $index, $member) { $args = func_get_args(); return $this->_executeCommand('setToList', $args); }

    /**
     * Delete element from list by member at key
     *
     * @param $key             Key name
     * @param $value           Element value
     * @param $count[optional] Limit of deleted items. For default no limit.
     * @return mixed
     */
    public function deleteFromList($key, $value, $count = 0) { $args = func_get_args(); return $this->_executeCommand('deleteFromList', $args); }

    /**
     * Return and remove the first element of the List at key
     *
     * @param string $key Key name
     * @return mixed
     */
    public function shiftFromList($key) { $args = func_get_args(); return $this->_executeCommand('shiftFromList', $args); }

    /**
     * Return and remove the first element of the List at key and block if list is empty or not exists
     *
     * @param string $keyOrKeys   Key name or array of names
     * @param string $timeout     Blocking timeout in seconds. Timeout disabled for default.
     * @return mixed
     */
    public function shiftFromListBlocking($keyOrKeys, $timeout = 0) { $args = func_get_args(); return $this->_executeCommand('shiftFromListBlocking', $args); }

    /**
     * Return and remove the last element of the List at key 
     *
     * @param string           $name       Key name
     * @param string[optional] $pushToName If not null - push value to another key.
     * @return mixed
     */
    public function popFromList($key, $pushToKey = null) { $args = func_get_args(); return $this->_executeCommand('popFromList', $args); }

    /**
     * Return and remove the last element of the List at key and block if list is empty or not exists
     *
     * @param string|array $keyOrKeys           Key name or array of names
     * @param integer      $timeout[optional]   Timeout. 0 for default - timeout is disabled.
     * @param string       $pushToKey[optional] If not null - push value to another list.
     * @return mixed
     */
    public function popFromListBlocking($keyOrKeys, $timeout = 0, $pushToKey = null) { $args = func_get_args(); return $this->_executeCommand('popFromListBlocking', $args); }

    /**
     * Insert a new value as the element before or after the reference value
     *
     * @param string  $key            Key name
     * @param string  $position       BEFORE or AFTER
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return integer|boolean
     */
    public function insertToList($key, $position, $referenceValue, $value) { $args = func_get_args(); return $this->_executeCommand('insertToList', $args); }

    /**
     * Insert a new value as the element after the reference value
     *
     * @param string  $key            Key name
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return integer|boolean
     */
    public function insertToListAfter($key, $referenceValue, $value) { $args = func_get_args(); return $this->_executeCommand('insertToListAfter', $args); }

    /**
     * Insert a new value as the element before the reference value
     *
     * @param string  $key            Key name
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return integer|boolean
     */
    public function insertToListBefore($key, $referenceValue, $value) { $args = func_get_args(); return $this->_executeCommand('insertToListBefore', $args); }

    /**
     * Add the specified member to the Set value at key
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @return boolean
     */
    public function addToSet($key, $member) { $args = func_get_args(); return $this->_executeCommand('addToSet', $args); }

    /**
     * Remove the specified member from the Set value at key
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @return boolean
     */
    public function deleteFromSet($key, $member) { $args = func_get_args(); return $this->_executeCommand('deleteFromSet', $args); }

    /**
     * Get random element from the Set value at key
     *
     * @param string            $key  Key name
     * @param boolean[optional] $pop  If true - pop value from the set. For default is false
     * @return mixed
     */
    public function getRandomFromSet($key, $pop = false) { $args = func_get_args(); return $this->_executeCommand('getRandomFromSet', $args); }

    /**
     * Return the number of elements (the cardinality) of the Set at key
     *
     * @param string $key Key name
     * @return mixed
     */
    public function getSetLength($key) { $args = func_get_args(); return $this->_executeCommand('getSetLength', $args); }

    /**
     * Test if the specified value is a member of the Set at key
     *
     * @param string $key    Key value
     * @param mixed  $member Member
     * @return boolean
     */
    public function existsInSet($key, $member) { $args = func_get_args(); return $this->_executeCommand('existsInSet', $args); }

    /**
     * Return the intersection between the Sets stored at key1, key2, ..., keyN
     *
     * @param array            $keys     Array of key names
     * @param string[optional] $storeKey Store to set with key name
     * @return mixed
     */
    public function intersectSets(array $keys, $storeKey = null) { $args = func_get_args(); return $this->_executeCommand('intersectSets', $args); }

    /**
     * Return the union between the Sets stored at key1, key2, ..., keyN
     *
     * @param array            $keys     Array of key names
     * @param string[optional] $storeKey Store to set with key name
     * @return mixed
     */
    public function unionSets(array $keys, $storeKey = null) { $args = func_get_args(); return $this->_executeCommand('unionSets', $args); }

    /**
     * Return the difference between the Set stored at key1 and all the Sets key2, ..., keyN
     *
     * @param array            $keys     Array of key names
     * @param string[optional] $storeKey Store to set with key name
     * @return mixed
     */
    public function diffSets(array $keys, $storeKey = null) { $args = func_get_args(); return $this->_executeCommand('diffSets', $args); }

    /**
     * Return all the members of the Set value at key
     *
     * @param string  $key Key name
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created 
     * @return array
     */
    public function getSet($key, $responseIterator = false) { $args = func_get_args(); return $this->_executeCommand('getSet', $args); }

    /**
     * Move the specified member from one Set to another atomically
     *
     * @param string $fromKey From key name
     * @param string $toKey   To key name
     * @param mixed  $member  Member
     * @return mixed
     */
    public function moveToSet($fromKey, $toKey, $member) { $args = func_get_args(); return $this->_executeCommand('moveToSet', $args); }

    /**
     * Add member to sorted set
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @param number $score  Score of member
     * @return boolean
     */
    public function addToSortedSet($key, $member, $score) { $args = func_get_args(); return $this->_executeCommand('addToSortedSet', $args); }

    /**
     * Delete the specified member from the sorted set by value
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @return boolean
     */
    public function deleteFromSortedSet($key, $member) { $args = func_get_args(); return $this->_executeCommand('deleteFromSortedSet', $args); }

    /**
     * Get all the members of the Sorted Set value at key
     *
     * @param string  $key                         Key name
     * @param integer $withScores[optional]        Return values with scores. For default is false.
     * @param integer $start[optional]             Start index. For default is begin of set.
     * @param integer $end[optional]               End index. For default is end of set.
     * @param boolean $revert[optional]            Revert elements (not used in sorting). For default is false
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created 
     * @return array
     */
    public function getSortedSet($key, $withScores = false, $start = 0, $end = -1, $revert = false, $responseIterator = false) { $args = func_get_args(); return $this->_executeCommand('getSortedSet', $args); }

    /**
     * Get members from sorted set by min and max score
     *
     * @param string            $key        Key name
     * @param number            $min        Min score
     * @param number            $max        Max score
     * @param boolean[optional] $withScores Get with scores. For default is false
     * @param integer[optional] $limit      Limit. For default is no limit
     * @param integer[optional] $offset     Offset. For default is no offset
     * @param boolean[optional] $revert     Revert. For default false
     * @return array
     */
    public function getFromSortedSetByScore($key, $min, $max, $withScores = false, $limit = null, $offset = null, $revert = false) { $args = func_get_args(); return $this->_executeCommand('getFromSortedSetByScore', $args); }

    /**
     * Get length of Sorted Set
     *
     * @param string $key Key name
     * @return mixed
     */
    public function getSortedSetLength($key) { $args = func_get_args(); return $this->_executeCommand('getSortedSetLength', $args); }

    /**
     * Get count of members from sorted set by min and max score
     *
     * @param string $key Key name
     * @param number $min Min score
     * @param number $max Max score
     * @return mixed
     */
    public function getSortedSetLengthByScore($key, $min, $max) { $args = func_get_args(); return $this->_executeCommand('getSortedSetLengthByScore', $args); }

    /**
     * Increment score of sorted set element
     *
     * @param string        $key   Key name
     * @param mixed         $value Member
     * @param integer|float $score Score to increment
     * @return mixed
     */
    public function incrementScoreInSortedSet($key, $value, $score) { $args = func_get_args(); return $this->_executeCommand('incrementScoreInSortedSet', $args); }

    /**
     * Remove all the elements in the sorted set at key with a score between min and max (including elements with score equal to min or max).
     *
     * @param string  $key   Key name
     * @param numeric $min   Min value
     * @param numeric $max   Max value
     * @return mixed
     */
    public function deleteFromSortedSetByScore($key, $min, $max) { $args = func_get_args(); return $this->_executeCommand('deleteFromSortedSetByScore', $args); }

    /**
     * Remove all elements in the sorted set at key with rank between start  and end
     *
     * @param string  $key   Key name
     * @param numeric $start Start position
     * @param numeric $end   End position
     * @return mixed
     */
    public function deleteFromSortedSetByRank($key, $start, $end) { $args = func_get_args(); return $this->_executeCommand('deleteFromSortedSetByRank', $args); }

    /**
     * Get member score from Sorted Set
     *
     * @param string $key    Key name
     * @param mixed  $member Member value
     * @return mixed
     */
    public function getScoreFromSortedSet($key, $member) { $args = func_get_args(); return $this->_executeCommand('getScoreFromSortedSet', $args); }

    /**
     * Get rank of member from sorted set
     *
     * @param string  $key              Key name
     * @param integer $member           Member value
     * @param boolean $revert[optional] Revert elements (not used in sorting). For default is false
     * @return mixed
     */
    public function getRankFromSortedSet($key, $member, $revert = false) { $args = func_get_args(); return $this->_executeCommand('getRankFromSortedSet', $args); }

    /**
     * Store to key union between the sorted sets
     *
     * @param array  $keys       Array of key names or associative array with weights
     * @param string $storeKey   Result sorted set key name
     * @param string $aggregation Aggregation method: SUM (for default), MIN, MAX.
     * @return mixed
     */
    public function unionSortedSets(array $keys, $storeKey, $aggregation = Rediska_Command_UnionSortedSets::SUM) { $args = func_get_args(); return $this->_executeCommand('unionSortedSets', $args); }

    /**
     * Store to key intersection between sorted sets
     *
     * @param array  $keys       Array of key names or associative array with weights
     * @param string $storeKey   Result sorted set key name
     * @param string $aggregation Aggregation method: SUM (for default), MIN, MAX.
     * @return mixed
     */
    public function intersectSortedSets(array $keys, $storeKey, $aggregation = Rediska_Command_IntersectSortedSets::SUM) { $args = func_get_args(); return $this->_executeCommand('intersectSortedSets', $args); }

    /**
     * Set value to a hash field or fields
     *
     * @param string        $key          Key name
     * @param array|string  $fieldOrData  Field or array of many fields and values: field => value
     * @param mixed         $value        Value for single field
     * @param boolean       $overwrite    Overwrite for single field (if false don't set and return false if key already exist). For default true.
     * @return boolean
     */
    public function setToHash($key, $fieldOrData, $value = null, $overwrite = true) { $args = func_get_args(); return $this->_executeCommand('setToHash', $args); }

    /**
     * Get value from hash field or fields
     *
     * @param string       $key           Key name
     * @param string|array $fieldOrFields Field or fields
     * @return mixed
     */
    public function getFromHash($key, $fieldOrFields) { $args = func_get_args(); return $this->_executeCommand('getFromHash', $args); }

    /**
     * Increment field value in hash
     *
     * @param string $key              Key name
     * @param mixed  $field            Field
     * @param number $amount[optional] Increment amount. One for default
     * @return mixed
     */
    public function incrementInHash($key, $field, $amount = 1) { $args = func_get_args(); return $this->_executeCommand('incrementInHash', $args); }

    /**
     * Test if field is present in hash
     *
     * @param string $key   Key name
     * @param mixed  $field Field
     * @return boolean
     */
    public function existsInHash($key, $field) { $args = func_get_args(); return $this->_executeCommand('existsInHash', $args); }

    /**
     * Delete field from hash
     *
     * @param string $key   Key name
     * @param mixed  $field Field
     * @return boolean
     */
    public function deleteFromHash($key, $field) { $args = func_get_args(); return $this->_executeCommand('deleteFromHash', $args); }

    /**
     * Return the number of fields in hash
     *
     * @param string $key Key name
     * @return mixed
     */
    public function getHashLength($key) { $args = func_get_args(); return $this->_executeCommand('getHashLength', $args); }

    /**
     * Get hash fields and values
     *
     * @param string $key Key name
     * @return array
     */
    public function getHash($key) { $args = func_get_args(); return $this->_executeCommand('getHash', $args); }

    /**
     * Get hash fields
     *
     * @param string $key Key name
     * @return mixed
     */
    public function getHashFields($key) { $args = func_get_args(); return $this->_executeCommand('getHashFields', $args); }

    /**
     * Get hash values
     *
     * @param string $key Key name
     * @return array
     */
    public function getHashValues($key) { $args = func_get_args(); return $this->_executeCommand('getHashValues', $args); }

    /**
     * Get sorted elements contained in the List, Set, or Sorted Set value at key.
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
     * @return array
     */
    public function sort($key, array $options = array()) { $args = func_get_args(); return $this->_executeCommand('sort', $args); }

    /**
     * Publish message to pubsub channel
     *
     * @param array|string $channelOrChannels Channel or array of channels
     * @param mixed        $message           Message
     * @return mixed
     */
    public function publish($channelOrChannels, $message) { $args = func_get_args(); return $this->_executeCommand('publish', $args); }

    /**
     * Save the DB on disk
     *
     * @param boolean[optional] $background Save asynchronously. For default is false
     * @return mixed
     */
    public function save($background = false) { $args = func_get_args(); return $this->_executeCommand('save', $args); }

    /**
     * Return the UNIX time stamp of the last successfully saving of the dataset on disk
     *
     * @return mixed
     */
    public function getLastSaveTime() { $args = func_get_args(); return $this->_executeCommand('getLastSaveTime', $args); }

    /**
     * Stop all the clients, save the DB, then quit the server
     *
     * @return mixed
     */
    public function shutdown() { $args = func_get_args(); return $this->_executeCommand('shutdown', $args); }

    /**
     * Rewrite the Append Only File in background when it gets too big
     *
     * @return mixed
     */
    public function rewriteAppendOnlyFile() { $args = func_get_args(); return $this->_executeCommand('rewriteAppendOnlyFile', $args); }

    /**
     * Provide information and statistics about the server
     *
     * @return mixed
     */
    public function info() { $args = func_get_args(); return $this->_executeCommand('info', $args); }

    /**
     * This command is often used to test if a connection is still alive, or to
     * measure latency.
     *
     * @return mixed
     */
    public function ping() { $args = func_get_args(); return $this->_executeCommand('ping', $args); }

    /**
     * Change the replication settings of a slave on the fly
     *
     * @param string|Rediska_Connection|false $aliasOrConnection Server alias, Rediska_Connection object or false if not slave
     * @return mixed
     */
    public function slaveOf($aliasOrConnection) { $args = func_get_args(); return $this->_executeCommand('slaveOf', $args); }

}
