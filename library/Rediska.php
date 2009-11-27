<?php

/**
 ** @see Rediska_Connection
 */
require_once 'Rediska/Connection.php';

/**
 * @see Rediska_Exception
 */
require_once 'Rediska/Exception.php';

/**
 * @see Rediska_KeyDistributor_Interface
 */
require_once 'Rediska/KeyDistributor/Interface.php';

/**
 * Rediska (radish on russian) - PHP client 
 * for key-value database Redis (http://code.google.com/p/redis)
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.2.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska
{
    const EOL = "\r\n";

    const REPLY_STATUS     = '+';
    const REPLY_ERROR      = '-';
    const REPLY_INTEGER    = ':';
    const REPLY_BULK       = '$';
    const REPLY_MULTY_BULK = '*';

    const KEY_TYPE_STRING = 'string';
    const KEY_TYPE_LIST   = 'list';
    const KEY_TYPE_SET    = 'set';

    /**
     * Object for distribution keys by servers 
     * 
     * @var Rediska_KeyDistributor_Abstract
     */
    protected $_keyDistributor;

    /**
     * Connections
     * 
     * @var array
     */
    protected $_connections = array();
    
    /**
     * Default rediska instance
     * 
     * @var Rediska
     */
    protected static $_defaultInstance;

    /**
     * Configuration
     * 
     * namespace      - Key names prefix
     * servers        - Array of servers: array(
     *                                        array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 1, 'password' => '123'),
     *                                        array('host' => '127.0.0.1', 'port' => 6380, 'weight' => 2)
     *                                    )
     * serializer     - Callback function for serialization.
     *                  You may use PHP extensions like igbinary (http://opensource.dynamoid.com/)
     *                  or you personal function.
     *                  For default php function serialize.             
     * unserializer   - Unserialize callback.
     * keyDistributor - Algorithm of keys distribution on redis servers.
     *                  For default 'consistentHashing' which implement
     *                  consistent hashing algorithm (http://weblogs.java.net/blog/tomwhite/archive/2007/11/consistent_hash.html)
     *                  You may use basic 'crc32' (crc32(key) % servers_count) algorithm
     *                  or you personal implementation (option value - name of class
     *                  which implements Rediska_KeyDistributor_Interface).
     * @var array
     */
    protected $_options = array(
        'namespace'           => '',
        'servers'             => array(
            array(
                'host'   => Rediska_Connection::DEFAULT_HOST,
                'port'   => Rediska_Connection::DEFAULT_PORT,
                'weight' => Rediska_Connection::DEFAULT_WEIGHT,
            )
        ),
        'serializer'          => 'serialize',
        'unserializer'        => 'unserialize',
        'keydistributor'      => 'consistentHashing'
    );

    /**
     * Contruct Rediska
     * 
     * @param array $options Options
     * 
     * namespace      - Key names prefix
     * servers        - Array of servers: array(
     *                                        array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 1, 'password' => '123'),
     *                                        array('host' => '127.0.0.1', 'port' => 6380, 'weight' => 2)
     *                                    )
     * serializer     - Callback function for serialization.
     *                  You may use PHP extensions like igbinary (http://opensource.dynamoid.com/)
     *                  or you personal function.
     *                  For default php function serialize.             
     * unserializer   - Unserialize callback.
     * keyDistributor - Algorithm of keys distribution on redis servers.
     *                  For default 'consistentHashing' which implement
     *                  consistent hashing algorithm (http://weblogs.java.net/blog/tomwhite/archive/2007/11/consistent_hash.html)
     *                  You may use basic 'crc32' (crc32(key) % servers_count) algorithm
     *                  or you personal implementation (option value - name of class
     *                  which implements Rediska_KeyDistributor_Interface).
     * 
     */
    public function __construct(array $options = array()) 
    {
    	$options = array_change_key_case($options, CASE_LOWER);
        $options = array_merge($this->_options, $options);

        // Set key distributer before setting servers
        $this->setKeyDistributor($options['keydistributor']);
        unset($options['keydistributor']);

        $this->setOptions($options);

        self::setDefaultInstace($this);
    }

    /**
     * Get Rediska default instance
     * 
     * @return Rediska
     */
    public static function getDefaultInstance()
    {
    	return self::$_defaultInstance;
    }

    /**
     * Set Rediska default instance
     * 
     * @param Rediska $instance
     */
    public static function setDefaultInstace(Rediska $instance)
    {
    	self::$_defaultInstance = $instance;
    }

    /**
     * Set options array
     * 
     * @param array $options Options (see $_options description)
     * @return Rediska
     */
    public function setOptions(array $options)
    {
        foreach($options as $name => $value) {
            if (method_exists($this, "set$name")) {
                call_user_func(array($this, "set$name"), $value);
            } else {
                $this->setOption($name, $value);
            }
        }

        return $this;
    }

    /**
     * Set option
     * 
     * @throws Rediska_Exception
     * @param string $name Name of option
     * @param mixed $value Value of option
     * @return Rediska
     */
    public function setOption($name, $value)
    {
    	$lowerName = strtolower($name);

        if (!array_key_exists($lowerName, $this->_options)) {
            throw new Rediska_Exception("Unknown option '$name'");
        }

        $this->_options[$lowerName] = $value;

        return $this;
    }

    /**
     * Get option
     * 
     * @throws Rediska_Exception 
     * @param string $name Name of option
     * @return mixed
     */
    public function getOption($name)
    {
    	$lowerName = strtolower($name);

        if (!array_key_exists($lowerName, $this->_options)) {
            throw new Rediska_Exception("Unknown option '$name'");
        }

        return $this->_options[$lowerName];
    }
    
    /**
     * Set serializer callback
     * For example: "serializer" or array($object, "serializer")
     * 
     * @throws Rediska_Exception
     * @param $callback Callback
     * @return Rediska
     */
    public function setSerializer($callback)
    {
        if (!is_callable($callback)) {
            throw new Rediska_Exception("Wrong serialize callback");
        }

        $this->_options['serializer'] = $callback;

        return $this;
    }

    /**
     * Set unserializer callback
     * For example: "unserializer" or array($object, "unserializer")
     * 
     * @throws Rediska_Exception
     * @param $callback Callback
     * @return Rediska
     */
    public function setUnserializer($callback)
    {
        if (!is_callable($callback)) {
            throw new Rediska_Exception("Wrong unserialize callback");
        }

        $this->_options['unserializer'] = $callback;

        return $this;
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
        foreach($servers as $serverOptions) {
            $this->addServer(
                isset($serverOptions['host']) ? $serverOptions['host'] : null,
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
    	$connectionString = "$host:$port";

    	if (array_key_exists($connectionString, $this->_connections)) {
    		throw new Rediska_Exception("Server '$host:$port' already added");
    	}

    	$options['host'] = $host;
    	$options['port'] = $port;

    	$connection = new Rediska_Connection($options);

    	$this->_connections[$connectionString] = $connection;

        $this->_keyDistributor->addConnection(
            $connectionString,
            isset($options['weight']) ? $options['weight'] : Rediska_Connection::DEFAULT_WEIGHT
        );

        return $this;
    }

    /**
     * Set key distributor.
     * See options description for more information.
     * 
     * @throws Rediska_Exception
     * @param string $name Name of key distributor (crc32, consistentHashing or you personal class) or object
     * @return rediska
     */
    public function setKeyDistributor($name)
    {
        if (is_object($name)) {
            $this->_keyDistributor = $name;
        } else if (in_array($name, array('crc32', 'consistentHashing'))) {
            $name = ucfirst($name);
            require_once "Rediska/KeyDistributor/$name.php";
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

        // Return prev key distributor connections
        foreach($this->_connections as $connectionString => $connection) {
            $this->_keyDistributor->addConnection($connectionString);
        }

        return $this;
    }

    /**
     * Commands operating on single-value keys
     */

    /**
     * Set value to a key
     * 
     * @throws Rediska_Exception
     * @param string $name Key name
     * @param mixed $value Value
     * @param integer $expire Number of seconds to expire key
     * @param boolean $overwrite If false don't set and return false if key already exist
     * @param boolean $getOldValue If true return old value
     * @return boolean
     */
    public function set($name, $value, $expire = null, $overwrite = true) 
    {
        if ((!is_null($expire) && !is_integer($expire)) || (is_integer($expire) && $expire <= 0)) {
            throw new Rediska_Exception("Expire must be positive integer");
        }

        $connection = $this->_getConnectionByKeyName($name);

        $reply = $this->_set($connection, $name, $value, $overwrite);

        if ($reply && !is_null($expire)) {
            $expireReplay = $this->_expire($connection, $name, $expire);
            if (!$expireReplay) {
            	$this->_delete($connection, $name);
                throw new Rediska_Exception("Can't set expire for key: '$name'");
            }
        }

        return $reply;
    }
    
    protected function _set(Rediska_Connection $connection, $name, $value, $overwrite)
    {
    	$value = $this->_serialize($value);

        if ($overwrite) {
            $command = 'SET';
        } else {
            $command = 'SETNX';
        }
        $command .= " {$this->_options['namespace']}$name " . strlen($value) . self::EOL . $value;

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Atomic set and get old value
     * 
     * @param string  $name   Key name
     * @param mixin   $value  Value
     * @param integer $expire Number of seconds to expire key
     * @return mixin
     */
    public function setAndGet($name, $value, $expire = null)
    {
        if ((!is_null($expire) && !is_integer($expire)) || (is_integer($expire) && $expire <= 0)) {
            throw new Rediska_Exception("Expire must be positive integer");
        }

    	$connection = $this->_getConnectionByKeyName($name);

        $value = $this->_serialize($value);

        $command = "GETSET {$this->_options['namespace']}$name " . strlen($value) . self::EOL . $value;

        $reply = $this->_sendCommandAndGetReply($connection, $command);

        if ($expire) {
            $expireReplay = $this->_expire($connection, $name, $expire);
            if (!$expireReplay) {
                $this->_delete($connection, $name, $expire);
                throw new Rediska_Exception("Can't set expire for key: '$name'");
            }
        }

        return $this->_unserialize($reply);
    }


    /**
     * Get value of key or array of values by array of keys
     * 
     * @param string|array $nameOrNames Key name or array of names
     * @return mixed
     */
    public function get($nameOrNames) 
    {
        if (is_array($nameOrNames)) {
            $names = $nameOrNames;
            $sortedResult = array();
            if (!empty($names)) {
                $connections = array();
                foreach ($names as $name) {
                    $connection = $this->_getConnectionByKeyName($name);
                    $connectionString = "$connection";
                    if (!array_key_exists($connectionString, $connections)) {
                        $connections[$connectionString] = array();
                    }
                    $connections[$connectionString][] = $name;
                }

                $result = array();
                foreach($connections as $connectionString => $keys) {
                    $command = "MGET ";
                    $resultKeyNames = array();
                    foreach($keys as $key) {
                        $command .= " {$this->_options['namespace']}$key";
                        $resultKeyNames[] = $key;
                    }

                    $reply = $this->_sendCommandAndGetReply($this->_connections[$connectionString], $command);

                    $index = 0;
                    foreach($reply as $value) {
                        if (!is_null($value)) {
                           $result[$resultKeyNames[$index]] = $this->_unserialize($value);
                        }
                        $index++;
                    }
                }

                foreach($names as $name) {
                    if (isset($result[$name])) {
                        $sortedResult[$name] = $result[$name];
                    }
                }
            }

            return $sortedResult;
        } else {
            $name = $nameOrNames;

            $connection = $this->_getConnectionByKeyName($name);

            return $this->_get($connection, $name);
        }
    }
    
    protected function _get(Rediska_Connection $connection, $name)
    {
    	$command = "GET {$this->_options['namespace']}$name";

        $reply = $this->_sendCommandAndGetReply($connection, $command);

        return $this->_unserialize($reply);
    }

    /**
     * Increment the number value of key by integer
     * 
     * @param string $name Key name
     * @param integer $amount Amount to increment
     * @return integer New value
     */
    public function increment($name, $amount = 1) 
    {
        if (!is_integer($amount) || $amount <= 0) {
            throw new Rediska_Exception("Amount must be positive integer");
        }

        $connection = $this->_getConnectionByKeyName($name);

        if ($amount == 1) {
            $command = "INCR {$this->_options['namespace']}$name";
        } else {
            $command = "INCRBY {$this->_options['namespace']}$name $amount";
        }

        return $this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Decrement the number value of key by integer
     * 
     * @param string $name Key name
     * @param integer $amount Amount to decrement
     * @return integer New value
     */
    public function decrement($name, $amount = 1) 
    {
        if (!is_integer($amount) || $amount <= 0) {
            throw new Rediska_Exception("Amount must be positive integer");
        }

        $connection = $this->_getConnectionByKeyName($name);

        if ($amount == 1) {
            $command = "DECR {$this->_options['namespace']}$name";
        } else {
            $command = "DECRBY {$this->_options['namespace']}$name $amount";
        }

        return $this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Test if a key exists
     * 
     * @param string $name Key name
     * @return boolean
     */
    public function exists($name) 
    {
        $connection = $this->_getConnectionByKeyName($name);
        
        $command = "EXISTS {$this->_options['namespace']}$name";

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }
    
    /**
     * Delete a key
     * 
     * @param string|array Key name or array of key names
     * @return boolean|integer
     */
    public function delete($nameOrNames) 
    {
        if (is_array($nameOrNames)) {
            $names = $nameOrNames;
            $result = 0;
            if (!empty($names)) {
                $connections = array();
                foreach ($names as $name) {
                    $connection = $this->_getConnectionByKeyName($name);
                    $connectionString = "$connection";
                    if (!array_key_exists($connectionString, $connections)) {
                        $connections[$connectionString] = array();
                    }
                    $connections[$connectionString][] = $name;
                }

                foreach($connections as $connectionString => $keys) {
                    $command = "DEL ";
                    foreach($keys as $key) {
                        $command .= " {$this->_options['namespace']}$key";
                    }

                    $result += $this->_sendCommandAndGetReply(
                        $this->_connections[$connectionString],
                        $command
                    );
                }
            }

            return $result;
        } else {
            $name = $nameOrNames;

            $connection = $this->_getConnectionByKeyName($name);

            return $this->_delete($connection, $name);
        }
    }
    
    protected function _delete(Rediska_Connection $connection, $name)
    {
    	$command = "DEL {$this->_options['namespace']}$name";

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Commands operating on the key space
     */

    /**
     * Get key type
     * 
     * @param string $name Key name
     * @return string
     */
    
    public function getType($name)
    {
    	$connection = $this->_getConnectionByKeyName($name);

    	$command = "TYPE {$this->_options['namespace']}$name";

    	$reply = $this->_sendCommandAndGetReply($connection, $command);

    	if ($reply == 'none') {
    		$reply = null;
    	}

    	return $reply;
    }

    /**
     * Returns all the keys matching the glob-style pattern
     * Glob style patterns examples:
     *   h?llo will match hello hallo hhllo
     *   h*llo will match hllo heeeello
     *   h[ae]llo will match hello and hallo, but not hillo
     * 
     * @param string $pattern
     * @return array
     */
    public function getKeysByPattern($pattern) 
    {
        $keys = array();
        if ($pattern != '') {
            $command = "KEYS {$this->_options['namespace']}$pattern";
            foreach($this->_getConnections() as $connection) {
                $reply = $this->_sendCommandAndGetReply($connection, $command);
                if ($reply != '') {
                    $keys = array_merge($keys, explode(' ', $reply));
                }
            }

            if (!empty($keys)) {
                $keys = array_unique($keys);
                foreach($keys as &$key) {
                    $key = substr($key, strlen($this->_options['namespace']));
                }
            }
        }

        return $keys;
    }

    /**
     * Return a random key from the key space
     * 
     * @return null|string
     */
    public function getRandomKey() 
    {
        $connections = $this->_getConnections();
        $index = rand(0, count($connections) - 1);
        $connection = $connections[$index];

        $command = "RANDOMKEY";

        $reply = $this->_sendCommandAndGetReply($connection, $command);

        if ($reply == '') {
            return null;
        } else {
        	if (strpos($reply, $this->_options['namespace']) === 0) {
                $reply = substr($reply, strlen($this->_options['namespace']));
        	}

        	return $reply;
        }
    }

    /**
     * Rename the old key in the new one
     * 
     * @param string $oldName Old key name
     * @param string $newName New key name
     * @param boolean $overwrite Overwrite the new name key if it already exists 
     * @return boolean
     */
    public function rename($oldName, $newName, $overwrite = true) 
    {
        $connection = false;

        if (count($this->_connections) == 1) {
        	$connections = $this->_getConnections();
        	$connection = $connections[0];
        } else {
        	$oldNameConnection = $this->_getConnectionByKeyName($oldName);
            $newNameConnection = $this->_getConnectionByKeyName($newName);
        	if ("$oldNameConnection" == "$newNameConnection") {
        	   $connection = $oldNameConnection;
        	}
        }

        if ($connection) {
        	if ($overwrite) {
        		$command = "RENAME";
        	} else {
        		$command = "RENAMENX";
        	}
        	$command .= " {$this->_options['namespace']}$oldName {$this->_options['namespace']}$newName";

            return (boolean)$this->_sendCommandAndGetReply($connection, $command);
        } else {
        	// TODO: Need lock?
        	$oldValue = $this->_get($oldNameConnection, $oldName);
        	if (!is_null($oldValue)) {
        		$reply = $this->_set($newNameConnection, $newName, $oldValue, $overwrite);
        		if ($reply) {
        			$this->_delete($oldNameConnection, $oldName);
        		}
        		return $reply;
        	} else {
                throw new Rediska_Exception('No such key');
        	}
        }
    }

    /**
     * Get the number of keys
     * 
     * @return integer
     */
    public function getKeysCount()
    {
    	$command = 'DBSIZE';
    	$count = 0;
    	foreach($this->_getConnections() as $connection) {
    		$count += $this->_sendCommandAndGetReply($connection, $command);
    	}

    	return $count;
    }

    /**
     * Set a time to live in seconds on a key
     * 
     * @param string  $name    Key name
     * @param integer $seconds Seconds from now to expire
     * @return boolean
     */
    public function expire($name, $seconds)
    {
        if (!is_integer($seconds) || $seconds <= 0) {
            throw new Rediska_Exception("Seconds must be positive integer");
        }

        $connection = $this->_getConnectionByKeyName($name);

        return $this->_expire($connection, $name, $seconds);
    }
    
    protected function _expire(Rediska_Connection $connection, $name, $seconds)
    {
    	$command = "EXPIRE {$this->_options['namespace']}$name $seconds";

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Get key lifetime
     * 
     * @param string $name
     */
    public function getLifetime($name)
    {
    	$connection = $this->_getConnectionByKeyName($name);

    	$command = "TTL {$this->_options['namespace']}$name";

    	$reply = $this->_sendCommandAndGetReply($connection, $command);

    	if ($reply == -1) {
    		$reply = null;
    	}

    	return $reply;
    }

    /**
     * Commands operating on lists
     */

    /**
     * Append value to the end of List
     * 
     * @param string $name  Key name
     * @param mixin  $value Value
     * @return boolean
     */
    public function appendToList($name, $value) 
    {
    	$connection = $this->_getConnectionByKeyName($name);

    	$value = $this->_serialize($value);

        $command = "RPUSH {$this->_options['namespace']}$name " . strlen($value) . self::EOL . $value;

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Append value to the head of List
     * 
     * @param string $name Key name
     * @param mixin  $value Value
     * @return boolean
     */
    public function prependToList($name, $value) 
    {
        $connection = $this->_getConnectionByKeyName($name);

        $value = $this->_serialize($value);

        $command = "LPUSH {$this->_options['namespace']}$name " . strlen($value) . self::EOL . $value;

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }
    
    /**
     * Return the length of the List value at key
     * 
     * @param string $name
     * @return integer
     */
    public function getListLength($name) 
    {
        $connection = $this->_getConnectionByKeyName($name);

        $command = "LLEN {$this->_options['namespace']}$name";

        return $this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Get List by key
     * 
     * @throws Rediska_Exception
     * @param string         $name        Key name
     * @param integer|string $limitOrSort Limit of elements or sorting query
     *                                    ALPHA work incorrect becouse values in List serailized
     *                                    Read more: http://code.google.com/p/redis/wiki/SortCommand
     * @param integer        $offset      Offset
     * @return array
     */
    public function getList($name, $limitOrSort = null, $offset = null)
    {
    	$connection = $this->_getConnectionByKeyName($name);
    	
    	if (is_null($limitOrSort) || is_numeric($limitOrSort)) {
    		$limit = $limitOrSort;

    		if (!is_null($limit) && !is_integer($limit)) {
                throw new Rediska_Exception("Limit must be integer");
            }

            if (is_null($offset)) {
                $offset = 0;
            } else if (!is_integer($offset)) {
                throw new Rediska_Exception("Offset must be integer");
            }

            $start = $offset;

            if (is_null($limit)) {
                $end = -1;
            } else {
                $end = $offset + $limit - 1;
            }
    
            $command = "LRANGE {$this->_options['namespace']}$name $start $end";
        } else {
            $sort = $limitOrSort;

            if (!is_null($offset)) {
                throw new Rediska_Exception("Offset not used with sorting query. Use LIMIT in query.");
            }
            
            $command = "SORT {$this->_options['namespace']}$name $sort";
        }

        $values = $this->_sendCommandAndGetReply($connection, $command);

        foreach($values as &$value) {
            $value = $this->_unserialize($value);
        }

        return $values;
    }

    /**
     * Trim the list at key to the specified range of elements
     * 
     * @throws Rediska_Exception
     * @param string $name Key name
     * @param integer $start Start index
     * @param integer $end End index
     * @return boolean
     */
    public function truncateList($name, $limit, $offset = 0)
    {
        if (!is_integer($limit)) {
            throw new Rediska_Exception("Limit must be integer");
        }

        if (!is_integer($offset)) {
            throw new Rediska_Exception("Offset must be integer");
        }

        $start = $offset;
        $end   = $offset + $limit - 1;

        $connection = $this->_getConnectionByKeyName($name);

        $command = "LTRIM {$this->_options['namespace']}$name $start $end";

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Return element of List by index at key
     * 
     * @throws Rediska_Exception
     * @param string  $name  Key name
     * @param integer $index Index
     * @return mixin
     */
    public function getFromList($name, $index)
    {
        if (!is_integer($index)) {
            throw new Rediska_Exception("Index must be integer");
        }

        $connection = $this->_getConnectionByKeyName($name);

        $command = "LINDEX {$this->_options['namespace']}$name $index";

        $reply = $this->_sendCommandAndGetReply($connection, $command);

        return $this->_unserialize($reply);
    }

    /**
     * Set a new value as the element at index position of the List at key
     * 
     * @throws Rediska_Exception
     * @param string $name Key name
     * @param mixin $value Value
     * @param integer $index Index
     * @return boolean
     */
    public function setToList($name, $index, $value) 
    {
        if (!is_integer($index)) {
            throw new Rediska_Exception("Index must be integer");
        }

        $connection = $this->_getConnectionByKeyName($name);

        $value = $this->_serialize($value);

        $command = "LSET {$this->_options['namespace']}$name $index " . strlen($value) . self::EOL . $value;

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Delete element from list by value at key
     * 
     * @throws Rediska_Exception
     * @param $name Key name
     * @param $value Element value
     * @param $count Limit of deleted items
     * @return integer
     */
    public function deleteFromList($name, $value, $count = 0)
    {        
        if (!is_integer($count)) {
            throw new Rediska_Exception("Count must be integer");
        }

        $connection = $this->_getConnectionByKeyName($name);

        $value = $this->_serialize($value);

        $command = "LREM {$this->_options['namespace']}$name $count " . strlen($value) . self::EOL . $value;

        return $this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Return and remove the first element of the List at key
     * 
     * @throws Rediska_Exception
     * @param string $name
     * @return mixin
     */
    public function shiftFromList($name) 
    {
        $connection = $this->_getConnectionByKeyName($name);

        $command = "LPOP {$this->_options['namespace']}$name";

        $reply = $this->_sendCommandAndGetReply($connection, $command);

        return $this->_unserialize($reply);
    }

    /**
     * Return and remove the last element of the List at key 
     * 
     * @param string $name
     * @return mixin
     */
    public function popFromList($name) 
    {
        $connection = $this->_getConnectionByKeyName($name);

        $command = "RPOP {$this->_options['namespace']}$name";

        $reply = $this->_sendCommandAndGetReply($connection, $command);

        return $this->_unserialize($reply);
    }

    /**
     * Commands operating on sets
     */

    /**
     * Add the specified member to the Set value at key
     * 
     * @param string $name  Key name
     * @param mixin  $value Value
     * @return boolean
     */
    public function addToSet($name, $value)
    {
        $connection = $this->_getConnectionByKeyName($name);

        return $this->_addToSet($connection, $name, $value);
    }
    
    protected function _addToSet(Rediska_Connection $connection, $name, $value)
    {
        $value = $this->_serialize($value);

        $command = "SADD {$this->_options['namespace']}$name " . strlen($value) . self::EOL . $value;

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Remove the specified member from the Set value at key
     * 
     * @param string $name  Key name
     * @param mixin  $value Value
     * @return boolean
     */
    public function deleteFromSet($name, $value)
    {
        $connection = $this->_getConnectionByKeyName($name);

        return $this->_deleteFromSet($connection, $name, $value);
    }
    
    protected function _deleteFromSet(Rediska_Connection $connection, $name, $value)
    {
        $value = $this->_serialize($value);

        $command = "SREM {$this->_options['namespace']}$name " . strlen($value) . self::EOL . $value;

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Get random element from the Set value at key
     * 
     * @throws Rediska_Exception
     * @param string  $name Key name
     * @param boolean $pop If true - pop value from the set
     * @return mixin
     */
    public function getRandomFromSet($name, $pop = false)
    {
        throw new Rediska_Exception('Not yet implemented');

        $connection = $this->_getConnectionByKeyName($name);

        if ($pop) {
            $command = "SPOP";
        } else {
            $command = "SRANDMEMBER";
        }

        $command = " {$this->_options['namespace']}$name";

        $reply = $this->_sendCommandAndGetReply($connection, $command);

        return $this->_unserialize($reply);
    }

    /**
     * Move the specified member from one Set to another atomically
     * 
     * @param string $fromName From key name
     * @param string $toName   To key name
     * @param mixin  $value    Value
     * @return boolean
     */
    public function moveToSet($fromName, $toName, $value)
    {
        $connection = false;

        if (count($this->_connections) == 1) {
            $connections = $this->_getConnections();
            $connection = $connections[0];
        } else {
            $fromNameConnection = $this->_getConnectionByKeyName($fromName);
            $toNameConnection = $this->_getConnectionByKeyName($toName);
            if ("$fromNameConnection" == "$toNameConnection") {
               $connection = $toNameConnection;
            }
        }

        if ($connection) {
            $value = $this->_serialize($value);

            $command = "SMOVE {$this->_options['namespace']}$fromName {$this->_options['namespace']}$toName "  . strlen($value) . self::EOL . $value;

            return (boolean)$this->_sendCommandAndGetReply($connection, $command);
        } else {
            // TODO: Add locks?
            if ($this->_existsInSet($fromNameConnection, $fromName, $value)) {
                $this->_deleteFromSet($fromNameConnection, $fromName, $value);
                return $this->_addToSet($toNameConnection, $toName, $value);
            } else {
                return false;
            }
        }
    }
    
    /**
     * Return the number of elements (the cardinality) of the Set at key
     * 
     * @param string $name Key name
     * @return integer
     */
    public function getSetLength($name)
    {
        $connection = $this->_getConnectionByKeyName($name);

        $command = "SCARD {$this->_options['namespace']}$name";

        return $this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Test if the specified value is a member of the Set at key
     * 
     * @param string $name  Key value
     * @prarm mixin  $value Value
     * @return boolean
     */
    public function existsInSet($name, $value) 
    {
        $connection = $this->_getConnectionByKeyName($name);

        return $this->_existsInSet($connection, $name, $value);
    }
    
    protected function _existsInSet(Rediska_Connection $connection, $name, $value)
    {
        $value = $this->_serialize($value);

        $command = "SISMEMBER {$this->_options['namespace']}$name " . strlen($value) . self::EOL . $value;

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Return the intersection between the Sets stored at key1, key2, ..., keyN
     * 
     * @todo Refactor
     * @param array       $names     Array of key names
     * @param string|null $storeName Store intersection to set with key name
     * @return array|boolean
     */
    public function intersectSets(array $names, $storeName = null) 
    {
        if (!empty($names)) {
            $connections = array();
            foreach ($names as $name) {
                $connection = $this->_getConnectionByKeyName($name);
                $connectionString = "$connection";
                if (!array_key_exists($connectionString, $connections)) {
                    $connections[$connectionString] = array();
                }
                $connections[$connectionString][] = $name;
            }

            if (count($connections) == 1) {
                $connectionStrings = array_keys($connections);
                $connectionString = $connectionStrings[0];

                if (is_null($storeName)) {
                    $command = "SINTER";
                    $connection = $this->_connections[$connectionString];
                } else {
                    $command = "SINTERSTORE {$this->_options['namespace']}$storeName";
                    $connection = $this->_getConnectionByKeyName($storeName);
                }
                foreach($connections[$connectionString] as $name) {
                    $command .= " {$this->_options['namespace']}$name";
                }

                $reply = $this->_sendCommandAndGetReply($connection, $command);

                if (is_null($storeName)) {
                    foreach($reply as &$value) {
                        $value = $this->_unserialize($value);
                    }
                } else {
                    $reply = (boolean)$reply;
                }

                return $reply;
            } else {
                $results = array();
                foreach($connections as $connectionString => $keys) {
                    foreach($keys as $key) {
                        $command = "SMEMBERS {$this->_options['namespace']}$key";
                        $reply = $this->_sendCommandAndGetReply($this->_connections[$connectionString], $command);
                        if (is_array($reply)) {
                            $results[] = $reply;
                        }
                    }
                }
                if (!empty($results)) {
                    $values = call_user_func_array('array_intersect', $results);
                    if (is_null($storeName)) {
                        foreach($values as &$value) {
                            $value = $this->_unserialize($value);
                        }
                        return $values;
                    } else {
                        if (!empty($values)) {
                            $connection = $this->_getConnectionByKeyName($storeName);
                            foreach($values as $value) {
                                $command = "SADD {$this->_options['namespace']}$storeName " . strlen($value) . self::EOL . $value;
                                $reply = (bool)$this->_sendCommandAndGetReply($connection, $command);
                                if (!$reply) {
                                    return false;
                                }
                            }
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }

        if (is_null($storeName)) {
            return array();
        } else {
            return false;
        }
    }
    
    /**
     * Return the union between the Sets stored at key1, key2, ..., keyN
     * 
     * @todo Refactor
     * @param array       $names     Array of key names
     * @param string|null $storeName Store union to set with key name
     * @return array|boolean
     */
    public function unionSets(array $names, $storeName = null) 
    {
        if (!empty($names)) {
            $connections = array();
            foreach ($names as $name) {
                $connection = $this->_getConnectionByKeyName($name);
                $connectionString = "$connection";
                if (!array_key_exists($connectionString, $connections)) {
                    $connections[$connectionString] = array();
                }
                $connections[$connectionString][] = $name;
            }

            if (count($connections) == 1) {
                $connectionStrings = array_keys($connections);
                $connectionString = $connectionStrings[0];

                if (is_null($storeName)) {
                    $command = "SUNION";
                    $connection = $this->_connections[$connectionString];
                } else {
                    $command = "SUNIONSTORE {$this->_options['namespace']}$storeName";
                    $connection = $this->_getConnectionByKeyName($storeName);
                }
                foreach($connections[$connectionString] as $name) {
                    $command .= " {$this->_options['namespace']}$name";
                }

                $reply = $this->_sendCommandAndGetReply($connection, $command);

                if (is_null($storeName)) {
                    foreach($reply as &$value) {
                        $value = $this->_unserialize($value);
                    }
                } else {
                    $reply = (boolean)$reply;
                }

                return $reply;
            } else {
                $results = array();
                foreach($connections as $connectionString => $keys) {
                    foreach($keys as $key) {
                        $command = "SMEMBERS {$this->_options['namespace']}$key";
                        $reply = $this->_sendCommandAndGetReply($this->_connections[$connectionString], $command);
                        $results = array_merge($results, $reply);
                    }
                }
                if (!empty($results)) {
                    $values = array_unique($results);
                    if (is_null($storeName)) {
                        foreach($values as &$value) {
                            $value = $this->_unserialize($value);
                        }

                        return $values;
                    } else {
                        if (!empty($values)) {
                            $connection = $this->_getConnectionByKeyName($storeName);
                            foreach($values as $value) {
                                $command = "SADD {$this->_options['namespace']}$storeName " . strlen($value) . self::EOL . $value;
                                $reply = (bool)$this->_sendCommandAndGetReply($connection, $command);
                                if (!$reply) {
                                    return false;
                                }
                            }

                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }

        if (is_null($storeName)) {
            return array();
        } else {
            return false;
        }
    }
    
    /**
     * Return the difference between the Set stored at key1 and all the Sets key2, ..., keyN
     * 
     * @todo Refactor
     * @param array       $names     Array of key names
     * @param string|null $storeName Store union to set with key name
     * @return array|boolean
     */
    public function diffSets(array $names, $storeName = null) 
    {
        if (!empty($names)) {
            $connections = array();
            foreach ($names as $name) {
                $connection = $this->_getConnectionByKeyName($name);
                $connectionString = "$connection";
                if (!array_key_exists($connectionString, $connections)) {
                    $connections[$connectionString] = array();
                }
                $connections[$connectionString][] = $name;
            }

            if (count($connections) == 1) {
                $connectionStrings = array_keys($connections);
                $connectionString = $connectionStrings[0];

                if (is_null($storeName)) {
                    $command = "SDIFF";
                    $connection = $this->_connections[$connectionString];
                } else {
                    $command = "SDIFFSTORE {$this->_options['namespace']}$storeName";
                    $connection = $this->_getConnectionByKeyName($storeName);
                }
                foreach($connections[$connectionString] as $name) {
                    $command .= " {$this->_options['namespace']}$name";
                }

                $reply = $this->_sendCommandAndGetReply($connection, $command);

                if (is_null($storeName)) {
                    foreach($reply as &$value) {
                        $value = $this->_unserialize($value);
                    }
                } else {
                    $reply = (boolean)$reply;
                }

                return $reply;
            } else {
                $results = array();
                foreach($connections as $connectionString => $keys) {
                    foreach($keys as $key) {
                        $command = "SMEMBERS {$this->_options['namespace']}$key";
                        $reply = $this->_sendCommandAndGetReply($this->_connections[$connectionString], $command);
                        $results[] = $reply;
                    }
                }
                if (!empty($results)) {
                    $values = call_user_func_array('array_diff', $results);
                    if (is_null($storeName)) {
                        foreach($values as &$value) {
                            $value = $this->_unserialize($value);
                        }
                        return $values;
                    } else {
                        if (!empty($values)) {
                            $connection = $this->_getConnectionByKeyName($storeName);
                            foreach($values as $value) {
                                $command = "SADD {$this->_options['namespace']}$storeName " . strlen($value) . self::EOL . $value;
                                $reply = (bool)$this->_sendCommandAndGetReply($connection, $command);
                                if (!$reply) {
                                    return false;
                                }
                            }
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }

        if (is_null($storeName)) {
            return array();
        } else {
            return false;
        }
    }
    
    
    /**
     * Return all the members of the Set value at key
     * 
     * @param string $name Key name
     * @param string $sort Sorting query see: http://code.google.com/p/redis/wiki/SortCommand
     *                     ALPHA work incorrect becouse values in Set serailized
     * @return array
     */
    public function getSet($name, $sort = null)
    {
        $connection = $this->_getConnectionByKeyName($name);

        if (is_null($sort)) {
            $command = "SMEMBERS {$this->_options['namespace']}$name";
        } else {
            $command = "SORT {$this->_options['namespace']}$name $sort";
        }

        $values = $this->_sendCommandAndGetReply($connection, $command);

        foreach($values as &$value) {
            $value = $this->_unserialize($value);
        }

        return $values;
    }

    /**
     * Control commands
     */

    /**
     * Select the DB having the specified index
     * 
     * @param integer $index Db index
     * @return boolean
     */
    public function selectDb($index)
    {
        if (!is_integer($index) || $index < 0) {
            throw new Rediska_Exception("Index must be zero or positive integer");
        }

        $command = "SELECT $index";

        foreach($this->_getConnections() as $connection) {
            $this->_sendCommandAndGetReply($connection, $command);
        }

        return true;
    }
    
    /**
     * Move the key from the currently selected DB to the DB having as index dbindex
     * 
     * @throws Rediska_Exception
     * @param string  $name  Key name
     * @param integer $index Db index
     * @return boolean
     */
    public function moveToDb($name, $dbIndex)
    {
        if (!is_integer($dbIndex) || $dbIndex < 0) {
            throw new Rediska_Exception("Index must be zero or positive integer");
        }

        $connection = $this->_getConnectionByKeyName($name);

        $command = "MOVE {$this->_options['namespace']}$name $dbIndex";

        return (boolean)$this->_sendCommandAndGetReply($connection, $command);
    }

    /**
     * Remove all the keys of the currently selected DB
     * 
     * @param boolean $all Remove from all Db
     * @return boolean
     */
    public function flushDb($all = false)
    {
        if ($all) {
            $command = "FLUSHALL";
        } else {
            $command = "FLUSHDB";
        }

        foreach($this->_getConnections() as $connection) {
            $this->_sendCommandAndGetReply($connection, $command);
        }

        return true;
    }

    /**
     * Synchronously save the DB on disk
     * 
     * @param boolean $background Save asynchronously
     * @return boolean
     */
    public function save($background = false) 
    {
        if ($background) {
            $command = "BGSAVE";
        } else {
            $command = "SAVE";
        }

        foreach($this->_getConnections() as $connection) {
            $this->_sendCommandAndGetReply($connection, $command);
        }

        return true;
    }

    /**
     * Return the UNIX time stamp of the last successfully saving of the dataset on disk
     * 
     * @return array|integer
     */
    public function getLastSaveTime()
    {
        $timestamps = array();
        foreach($this->_getConnections() as $connection) {
            $command = "LASTSAVE";
            $timestamp = $this->_sendCommandAndGetReply($connection, $command);
            $timestamps[$connection->getHost() . ':' . $connection->getPort()] = $timestamp;
        }

        if (count($timestamps) == 1) {
            $timestamps = array_values($timestamps);
            $timestamps = $timestamps[0];
        }
        
        return $timestamps;
    }

    /**
     * Synchronously save the DB on disk, then shutdown the server
     * 
     * @return boolean
     */
    public function shutdown()
    {
        foreach($this->_getConnections() as $connection) {
            $command = "SHUTDOWN";
            $this->_sendCommandAndGetReply($connection, $command);
        }

        return true;
    }

    /**
     * Provide information and statistics about the server
     * 
     * @return array
     */
    public function info()
    {
        $info = array();
        foreach($this->_getConnections() as $connection) {
            $command = 'INFO';
            $data = $this->_sendCommandAndGetReply($connection, $command);
            $connectionString = $connection->getHost() . ':' . $connection->getPort();
            $info[$connectionString] = array();
            foreach (explode(self::EOL, $data) as $param) {
                if (!$param) {
                    continue;
                }

                list($name, $stringValue) = explode(':', $param, 2);

                if (strpos($stringValue, '.') !== false) {
                    $value = (float)$stringValue;
                } else {
                    $value = (integer)$stringValue;
                }

                if ((string)$value != $stringValue) {
                    $value = $stringValue;
                }

                $info[$connectionString][$name] = $value;
            }
        }

        if (count($info) == 1) {
            $info = array_values($info);
            $info = $info[0];
        }

        return $info;
    }

    /**
     * Internal methods
     */

    protected function _getConnectionByKeyName($name)
    {
        if (count($this->_connections) == 1) {
            $connections = array_values($this->_connections);
            $connection = $connections[0];
        } else {
            $connectionString = $this->_keyDistributor->getConnectionByKeyName($name);
            $connection = $this->_connections[$connectionString];
        }

        try {
            $connection->connect();
        } catch (Rediska_Connection_Exception $e) {
            $connectionString = "$connection";
            unset($this->_connections[$connectionString]);
            $this->_keyDistributor->removeConnection($connectionString);
            trigger_error($e->getMessage(), E_USER_WARNING);
            if (empty($this->_connections)) {
                throw new Rediska_Connection_Exception('No one working server connections!');
            } else {
                $connection = $this->_getConnectionByKeyName($name);
            }
        }

        return $connection;
    }

    protected function _getConnections()
    {
        foreach($this->_connections as $connectionString => $connection) {
            try {
                $connection->connect();
            } catch (Rediska_Connection_Exception $e) {
                unset($this->_connections[$connectionString]);
                $this->_keyDistributor->removeConnection($connectionString);
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        if (empty($this->_connections)) {
            throw new Rediska_Connection_Exception('No one working server connections!');
        }

        return array_values($this->_connections);
    }

    protected function _serialize($value)
    {
        if (is_numeric($value)) {
            return (string)$value;
        } else {
            return call_user_func($this->_options['serializer'], $value);
        }
    }

    protected function _unserialize($value)
    {
        if (is_null($value)) {
            return null;
        } else if (is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                $number = (integer)$value;
            } else {
                $number = (float)$value;
            }

            if ((string)$number != $value) {
            	$number = $value;
            }

            return $number;
        } else {
            return call_user_func($this->_options['unserializer'], $value);
        }
    }

    protected function _sendCommandAndGetReply(Rediska_Connection $connection, $command)
    {
        $connection->write($command);

        return $this->_getReplay($connection);
    }

    protected function _getReplay(Rediska_Connection $connection)
    {
        $reply = $connection->readLine();

        $type = substr($reply, 0, 1);
        $data = substr($reply, 1);

        switch($type) {
            case self::REPLY_STATUS:
                return $data;
            case self::REPLY_ERROR:
                $message = substr($data, 4);

                throw new Rediska_Exception($message);
            case self::REPLY_INTEGER:
                if (strpos($data, '.') !== false) {
                    $number = (integer)$data;
                } else {
                    $number = (float)$data;
                }

                if ((string)$number != $data) {
                    throw new Rediska_Exception("Can't convert data ':$data' to integer");
                }

                return $number;
            case self::REPLY_BULK:
                if ($data == '-1') {
                    return null;
                } else {
                    $length = (integer)$data;
        
                    if ((string)$length != $data) {
                        throw new Rediska_Exception("Can't convert bulk reply header '$$data' to integer");
                    }

                    return $connection->read($length);
                }
            case self::REPLY_MULTY_BULK:
                $count = (integer)$data;

                if ((string)$count != $data) {
                    throw new Rediska_Exception("Can't convert multi-response header '$data' to integer");
                }

                $replies = array();
                for ($i = 0; $i < $count; $i++) {
                    $replies[] = $this->_getReplay($connection);
                }

                return $replies;
            default:
                throw new Rediska_Exception("Invalid reply type: '$type'");
        }
    }
}