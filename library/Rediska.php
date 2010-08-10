<?php

if (!defined('REDISKA_PATH')) {
    define('REDISKA_PATH', dirname(__FILE__));
    require_once REDISKA_PATH . '/Rediska/Options.php';
    require_once REDISKA_PATH . '/Rediska/Connection.php';
}

/**
 * Rediska (radish on russian) - PHP client 
 * for key-value database Redis (http://code.google.com/p/redis)
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 *
 * @method mixed quit() quit() Ask the server to silently close the connection.
 * @method boolean exists() exists(string $key) Test if a key exists
 * @method mixed delete() delete(string|array $keyOrKeys) Delete a key or keys
 * @method mixed getType() getType(string $key) Get key type
 * @method mixed getKeysByPattern() getKeysByPattern(string $pattern) Returns all the keys matching the glob-style pattern
 * @method string|null getRandomKey() getRandomKey() Return a random key from the key space
 * @method mixed rename() rename(string $oldKey, string $newKey, boolean[optional] $overwrite) Rename the old key in the new one
 * @method mixed getKeysCount() getKeysCount() Get the number of keys
 * @method boolean expire() expire(string $key, integer $secondsOrTimestamp, boolean $isTimestamp) Set a time to live in seconds or timestamp on a key
 * @method string|null getLifetime() getLifetime(string $key) Get key lifetime
 * @method mixed selectDb() selectDb(integer $index) Select the DB having the specified index
 * @method boolean moveToDb() moveToDb(string $key, integer $dbIndex) Move the key from the currently selected DB to the DB having as index dbindex
 * @method boolean flushDb() flushDb(boolean[optional] $all) Remove all the keys of the currently selected DB
 * @method mixed set() set(string|array $keyOrData, mixed $valueOrOverwrite, boolean $overwrite) Set value to a key or muliple values to multiple keys
 * @method mixed setAndGet() setAndGet(string $key, mixed $value) Atomic set value and return old
 * @method mixed get() get(string|array $keyOrKeys) Get value of key or array of values by array of keys
 * @method mixed setAndExpire() setAndExpire(string $key, mixed $value, integer $time) Set + Expire atomic command
 * @method mixed increment() increment(string $key, integer[optional] $amount) Increment the number value of key by integer
 * @method mixed decrement() decrement(string $key, integer[optional] $amount) Decrement the number value of key by integer
 * @method mixed substring() substring(string $key, integer $start, integer[optional] $end) Return a subset of the string from offset start to offset end (both offsets are inclusive)
 * @method mixed append() append($key Key, $value Value) Append value to a end of string key
 * @method mixed appendToList() appendToList(string $key, mixed $value) Append value to the end of List
 * @method mixed prependToList() prependToList(string $key, mixed $member) Append value to the head of List
 * @method mixed getListLength() getListLength(string $key) Return the length of the List value at key
 * @method array getList() getList(string $key, integer $start, integer $end) Get List by key
 * @method boolean truncateList() truncateList(string $key, integer $start, integer $end) Trim the list at key to the specified range of elements
 * @method mixed getFromList() getFromList(string $key, integer $index) Return element of List by index at key
 * @method boolean setToList() setToList(string $key, mixed $value, integer $index) Set a new value as the element at index position of the List at key
 * @method mixed deleteFromList() deleteFromList($key Key, $value Element, $count[optional] Limit) Delete element from list by member at key
 * @method mixed shiftFromList() shiftFromList(string $key) Return and remove the first element of the List at key
 * @method mixed shiftFromListBlocking() shiftFromListBlocking(string $keyOrKeys, string $timeout) Return and remove the first element of the List at key and block if list is empty or not exists
 * @method mixed popFromList() popFromList(string $name, string[optional] $pushToName) Return and remove the last element of the List at key
 * @method mixed popFromListBlocking() popFromListBlocking(string|array $keyOrKeys, integer $timeout) Return and remove the last element of the List at key and block if list is empty or not exists
 * @method boolean addToSet() addToSet(string $key, mixed $member) Add the specified member to the Set value at key
 * @method boolean deleteFromSet() deleteFromSet(string $key, mixed $member) Remove the specified member from the Set value at key
 * @method mixed getRandomFromSet() getRandomFromSet(string $key, boolean[optional] $pop) Get random element from the Set value at key
 * @method mixed getSetLength() getSetLength(string $key) Return the number of elements (the cardinality) of the Set at key
 * @method boolean existsInSet() existsInSet(string $key) Test if the specified value is a member of the Set at key
 * @method mixed intersectSets() intersectSets(array $keys, string[optional] $storeKey) Return the intersection between the Sets stored at key1, key2, ..., keyN
 * @method mixed unionSets() unionSets(array $keys, string[optional] $storeKey) Return the union between the Sets stored at key1, key2, ..., keyN
 * @method mixed diffSets() diffSets(array $keys, string[optional] $storeKey) Return the difference between the Set stored at key1 and all the Sets key2, ..., keyN
 * @method array getSet() getSet(string $key) Return all the members of the Set value at key
 * @method mixed moveToSet() moveToSet(string $fromKey, string $toKey, mixed $member) Move the specified member from one Set to another atomically
 * @method boolean addToSortedSet() addToSortedSet(string $key, mixed $member, number $score) Add member to sorted set
 * @method boolean deleteFromSortedSet() deleteFromSortedSet(string $key, mixed $member) Delete the specified member from the sorted set by value
 * @method array getSortedSet() getSortedSet(string $key, integer $withScores, integer $start, integer $end, boolean $revert) Get all the members of the Sorted Set value at key
 * @method array getFromSortedSetByScore() getFromSortedSetByScore(string $key, number $min, number $max, boolean[optional] $withScores, integer[optional] $limit, integer[optional] $offset) Get members from sorted set by min and max score
 * @method mixed getSortedSetLength() getSortedSetLength(string $key) Get length of Sorted Set
 * @method mixed incrementScoreInSortedSet() incrementScoreInSortedSet(string $key, mixed $value, integer|float $score) Increment score of sorted set element
 * @method mixed deleteFromSortedSetByScore() deleteFromSortedSetByScore(string $key, numeric $min, numeric $max) Remove all the elements in the sorted set at key with a score between min and max (including elements with score equal to min or max).
 * @method mixed deleteFromSortedSetByRank() deleteFromSortedSetByRank(string $key, numeric $start, numeric $end) Remove all elements in the sorted set at key with rank between start  and end
 * @method mixed getScoreFromSortedSet() getScoreFromSortedSet(string $key, mixed $member) Get member score from Sorted Set
 * @method mixed getRankFromSortedSet() getRankFromSortedSet(string $key, integer $member, boolean $revert) Get rank of member from sorted set
 * @method mixed unionSortedSets() unionSortedSets(array $keys, string $storeKey, string $aggregation) Store to key union between the sorted sets
 * @method mixed intersectSortedSets() intersectSortedSets(array $keys, string $storeKey, string $aggregation) Store to key intersection between sorted sets
 * @method boolean setToHash() setToHash(string $key, array|string $fieldOrData, mixed $value, boolean $overwrite) Set value to a hash field or fields
 * @method mixed getFromHash() getFromHash(string $key, string|array $fieldOrFields) Get value from hash field or fields
 * @method mixed incrementInHash() incrementInHash(string $key, mixed $field, number $amount) Increment field value in hash
 * @method boolean existsInHash() existsInHash(string $key, mixed $field) Test if field is present in hash
 * @method boolean deleteFromHash() deleteFromHash(string $key, mixed $field) Delete field from hash
 * @method mixed getHashLength() getHashLength(string $key) Return the number of fields in hash
 * @method array getHash() getHash(string $key) Get hash fields and values
 * @method mixed getHashFields() getHashFields(string $key) Get hash fields
 * @method array getHashValues() getHashValues(string $key) Get hash values
 * @method array sort() sort(string $key, string|array $value) Get sorted elements contained in the List, Set, or Sorted Set value at key.
 * @method mixed publish() publish(array|string $channelOrChannels, mixed $message) Publish message to pubsub channel
 * @method mixed save() save(boolean[optional] $background) Save the DB on disk
 * @method mixed getLastSaveTime() getLastSaveTime() Return the UNIX time stamp of the last successfully saving of the dataset on disk
 * @method mixed shutdown() shutdown(boolean $background) Save the DB on disk, then shutdown the server
 * @method mixed rewriteAppendOnlyFile() rewriteAppendOnlyFile() Rewrite the Append Only File in background when it gets too big
 * @method mixed info() info() Provide information and statistics about the server
 * @method mixed slaveOf() slaveOf(string|Rediska_Connection|false $aliasOrConnection) Change the replication settings of a slave on the fly
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
    const STABLE_REDIS_VERSION = '1.2.6';

    /**
     * Default rediska instance
     * 
     * @var Rediska
     */
    protected static $_defaultInstance;

    /**
     * Is registered Rediska autoload
     * 
     * @var boolean
     */
    protected static $_autoloadRegistered;

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
     * Redis commands
     * 
     * @var array
     */
    protected static $_commands = array(
        // Connection handling
        'quit' => 'Rediska_Command_Quit',

        // Commands operating on all value types
        'exists'           => 'Rediska_Command_Exists',
        'delete'           => 'Rediska_Command_Delete',
        'gettype'          => 'Rediska_Command_GetType',
        'getkeysbypattern' => 'Rediska_Command_GetKeysByPattern',
        'getrandomkey'     => 'Rediska_Command_GetRandomKey',
        'rename'           => 'Rediska_Command_Rename',
        'getkeyscount'     => 'Rediska_Command_GetKeysCount',
        'expire'           => 'Rediska_Command_Expire',
        'getlifetime'      => 'Rediska_Command_GetLifetime',
        'selectdb'         => 'Rediska_Command_SelectDb',
        'movetodb'         => 'Rediska_Command_MoveToDb',
        'flushdb'          => 'Rediska_Command_FlushDb',

        // Commands operating on string values
        'set'          => 'Rediska_Command_Set',
        'setandget'    => 'Rediska_Command_SetAndGet',
        'get'          => 'Rediska_Command_Get',
        'setandexpire' => 'Rediska_Command_SetAndExpire',
        'increment'    => 'Rediska_Command_Increment',
        'decrement'    => 'Rediska_Command_Decrement',
        'substring'    => 'Rediska_Command_Substring',
        'append'       => 'Rediska_Command_Append',

        // Commands operating on lists
        'appendtolist'          => 'Rediska_Command_AppendToList',
        'prependtolist'         => 'Rediska_Command_PrependToList',
        'getlistlength'         => 'Rediska_Command_GetListLength',
        'getlist'               => 'Rediska_Command_GetList',
        'truncatelist'          => 'Rediska_Command_TruncateList',
        'getfromlist'           => 'Rediska_Command_GetFromList',
        'settolist'             => 'Rediska_Command_SetToList',
        'deletefromlist'        => 'Rediska_Command_DeleteFromList',
        'shiftfromlist'         => 'Rediska_Command_ShiftFromList',
        'shiftfromlistblocking' => 'Rediska_Command_ShiftFromListBlocking',
        'popfromlist'           => 'Rediska_Command_PopFromList',
        'popfromlistblocking'   => 'Rediska_Command_PopFromListBlocking',

        // Commands operating on sets
        'addtoset'         => 'Rediska_Command_AddToSet',
        'deletefromset'    => 'Rediska_Command_DeleteFromSet',
        'getrandomfromset' => 'Rediska_Command_GetRandomFromSet',
        'getsetlength'     => 'Rediska_Command_GetSetLength',
        'existsinset'      => 'Rediska_Command_ExistsInSet',
        'intersectsets'    => 'Rediska_Command_IntersectSets',
        'unionsets'        => 'Rediska_Command_UnionSets',
        'diffsets'         => 'Rediska_Command_DiffSets',
        'getset'           => 'Rediska_Command_GetSet',
        'movetoset'        => 'Rediska_Command_MoveToSet',

        // Commands operating on sorted sets
        'addtosortedset'             => 'Rediska_Command_AddToSortedSet',
        'deletefromsortedset'        => 'Rediska_Command_DeleteFromSortedSet',
        'getsortedset'               => 'Rediska_Command_GetSortedSet',
        'getfromsortedsetbyscore'    => 'Rediska_Command_GetFromSortedSetByScore',
        'getsortedsetlength'         => 'Rediska_Command_GetSortedSetLength',
        'incrementscoreinsortedset'  => 'Rediska_Command_IncrementScoreInSortedSet',
        'deletefromsortedsetbyscore' => 'Rediska_Command_DeleteFromSortedSetByScore',
        'deletefromsortedsetbyrank'  => 'Rediska_Command_DeleteFromSortedSetByRank',
        'getscorefromsortedset'      => 'Rediska_Command_GetScoreFromSortedSet',
        'getrankfromsortedset'       => 'Rediska_Command_GetRankFromSortedSet',
        'unionsortedsets'            => 'Rediska_Command_UnionSortedSets',
        'intersectsortedsets'        => 'Rediska_Command_IntersectSortedSets',

        // Commands operating on hashes
        'settohash'        => 'Rediska_Command_SetToHash',
        'getfromhash'      => 'Rediska_Command_GetFromHash',
        'incrementinhash'  => 'Rediska_Command_IncrementInHash',
        'existsinhash'     => 'Rediska_Command_ExistsInHash',
        'deletefromhash'   => 'Rediska_Command_DeleteFromHash',
        'gethashlength'    => 'Rediska_Command_GetHashLength',
        'gethash'          => 'Rediska_Command_GetHash',
        'gethashfields'    => 'Rediska_Command_GetHashFields',
        'gethashvalues'    => 'Rediska_Command_GetHashValues',
        
        // Sorting
        'sort' => 'Rediska_Command_Sort',

        // Publish/Subscribe
        'publish' => 'Rediska_Command_Publish',

        // Persistence control commands
        'save'                  => 'Rediska_Command_Save',
        'getlastsavetime'       => 'Rediska_Command_GetLastSaveTime',
        'shutdown'              => 'Rediska_Command_Shutdown',
        'rewriteappendonlyfile' => 'Rediska_Command_RewriteAppendOnlyFile',

        // Remote server control commands
        'info'    => 'Rediska_Command_Info',
        'slaveof' => 'Rediska_Command_SlaveOf'
    );

    /**
     * Object for distribution keys by servers 
     * 
     * @var Rediska_KeyDistributor_Abstract
     */
    protected $_keyDistributor;
    
    /**
     * Serializer object
     * 
     * @var Rediska_Serializer_Interface
     */
    protected $_serializer;

    /**
     * Configuration
     * 
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
     *
     * @var array
     */
    protected $_options = array(
        'namespace' => '',
        'servers'   => array(
            array(
                'host'   => Rediska_Connection::DEFAULT_HOST,
                'port'   => Rediska_Connection::DEFAULT_PORT,
                'weight' => Rediska_Connection::DEFAULT_WEIGHT,
            )
        ),
        'serializeradapter' => 'phpSerialize',
        'keydistributor'    => 'consistentHashing',
        'redisversion'      => self::STABLE_REDIS_VERSION,
    );

    /**
     * Contruct Rediska
     * 
     * @param array $options Options
     * 
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
     * 
     */
    public function __construct(array $options = array()) 
    {
        parent::__construct($options);

        self::setDefaultInstace($this);

        $this->_specifiedConnection = new Rediska_Connection_Specified($this);
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
     * @return Rediska_Transation
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
     * @var string|array $channelOrChannels
     * @var mixed        $timeout
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
     * Add command
     * 
     * @param string $name      Command name
     * @param string $className Name of class
     */
    public static function addCommand($name, $className)
    {
        if (!class_exists($className)) {
            throw new Rediska_Exception("Class '$className' not found. You must include before or setup autoload");
        }

        $classReflection = new ReflectionClass($className);
        if (!in_array('Rediska_Command_Interface', $classReflection->getInterfaceNames())) {
            throw new Rediska_Exception("Class '$className' must implement Rediska_Command_Interface interface");
        }

        $lowerName = strtolower($name);
        self::$_commands[$lowerName] = $className;

        return true;
    }

    /**
     * Remove command
     * 
     * @param string $name Command name
     */
    public static function removeCommand($name)
    {
        $lowerName = strtolower($name);
        if (!isset(self::$_commands[$lowerName])) {
            throw new Rediska_Exception("Command '$name' not found");
        }
        unset(self::$_commands[$lowerName]);

        return true;
    }

    /**
     * Get commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return self::$_commands;
    }

    /**
     * Get Rediska Command instance
     * 
     * @throws Rediska_Exception
     * @param string $name      Command name 
     * @param array  $arguments Command arguments
     * @return Rediska_Command_Abstract
     */
    public function getCommand($name, $arguments)
    {
        $lowerName = strtolower($name);
        if (!isset(self::$_commands[$lowerName])) {
            throw new Rediska_Exception("Command '$name' not found");
        }

        // Initailize command
        return new self::$_commands[$lowerName]($this, $name, $arguments);
    }

    /**
     * Call Redis command
     * 
     * @param string $name Command name
     * @param array $args  Command arguments
     * @return mixed
     */
    public function __call($name, $args)
    {
        $this->_specifiedConnection->resetConnection();

        $command = $this->getCommand($name, $args);

        return $command->execute();
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
        $this->_options['keydistributor'] = $name;

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
     * Set Rediska serializer adapter
     * 
     * @param mixed $serializer
     * @return Rediska
     */
    public function setSerializerAdapter($adapter)
    {
        $this->_options['serializeradapter'] = $adapter;
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
        if (!$this->_serializer) {
            $this->_serializer = new Rediska_Serializer($this->_options['serializeradapter']);
        }

        return $this->_serializer;
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
     * Register Rediska autoload
     * 
     * @return boolean
     */
    public static function registerAutoload()
    {
        if (self::isRegisteredAutoload()) {
            return false;
        }

        self::$_autoloadRegistered = spl_autoload_register(array('Rediska', 'autoload'));

        return self::$_autoloadRegistered;
    }

    /**
     * Unregister Rediska autoload
     * 
     * @return boolean
     */
    public static function unregisterAutoload()
    {
        if (!self::isRegisteredAutoload()) {
            return false;
        }

        self::$_autoloadRegistered = !spl_autoload_unregister(array('Rediska', 'autoload'));

        return self::$_autoloadRegistered;
    }

    /**
     * Is Rediska autoload registered
     * 
     * @return boolean
     */
    public static function isRegisteredAutoload()
    {
        return self::$_autoloadRegistered;
    }

    /**
     * Autoload method
     * 
     * @param string $className
     */
    public static function autoload($className)
    {
        if (0 !== strpos($className, 'Rediska')) {
            return false;
        }

        $path = dirname(__FILE__) . '/' . str_replace('_', '/', $className) . '.php';

        if (!file_exists($path)) {
            return false;
        }

        require_once $path;
    }
}

Rediska::registerAutoload();