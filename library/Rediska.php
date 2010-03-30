<?php

/**
 * @see Rediska_Exception
 */
require_once 'Rediska/Exception.php';

/**
 * @see Rediska_Connection
 */
require_once 'Rediska/Connection.php';

/**
 * @see Rediska_Connection_Specified
 */
require_once 'Rediska/Connection/Specified.php';

/**
 * @see Rediska_Command_Interface
 */
require_once 'Rediska/Command/Interface.php';

/**
 * @see Rediska_Command_Abstract
 */
require_once 'Rediska/Command/Abstract.php';

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
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska
{
    const EOL = "\r\n";

    /**
     * Default rediska instance
     * 
     * @var Rediska
     */
    protected static $_defaultInstance;

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
        // Basic
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

        // Single value
        'set'       => 'Rediska_Command_Set',
        'setandget' => 'Rediska_Command_SetAndGet',
        'get'       => 'Rediska_Command_Get',
        'increment' => 'Rediska_Command_Increment',
        'decrement' => 'Rediska_Command_Decrement',

        // Lists
        'appendtolist'   => 'Rediska_Command_AppendToList',
        'prependtolist'  => 'Rediska_Command_PrependToList',
        'getlistlength'  => 'Rediska_Command_GetListLength',
        'getlist'        => 'Rediska_Command_GetList',
        'truncatelist'   => 'Rediska_Command_TruncateList',
        'getfromlist'    => 'Rediska_Command_GetFromList',
        'settolist'      => 'Rediska_Command_SetToList',
        'deletefromlist' => 'Rediska_Command_DeleteFromList',
        'shiftfromlist'  => 'Rediska_Command_ShiftFromList',
        'popfromlist'    => 'Rediska_Command_PopFromList',

        // Sets
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

        // Sorted sets
        'addtosortedset'             => 'Rediska_Command_AddToSortedSet',
        'deletefromsortedset'        => 'Rediska_Command_DeleteFromSortedSet',
        'getsortedset'               => 'Rediska_Command_GetSortedSet',
        'incrementscoreinsortedset'  => 'Rediska_Command_IncrementScoreInSortedSet',
        'getfromsortedsetbyscore'    => 'Rediska_Command_GetFromSortedSetByScore',
        'deletefromsortedsetbyscore' => 'Rediska_Command_DeleteFromSortedSetByScore',
        'getsortedsetlength'         => 'Rediska_Command_GetSortedSetLength',
        'getscorefromsortedset'      => 'Rediska_Command_GetScoreFromSortedSet',

        // Controls
        'save'                  => 'Rediska_Command_Save',
        'getlastsavetime'       => 'Rediska_Command_GetLastSaveTime',
        'info'                  => 'Rediska_Command_Info',
        'quit'                  => 'Rediska_Command_Quit',
        'shutdown'              => 'Rediska_Command_Shutdown',
        'rewriteappendonlyfile' => 'Rediska_Command_RewriteAppendOnlyFile',
        'slaveof'               => 'Rediska_Command_SlaveOf',
    );

    /**
     * Object for distribution keys by servers 
     * 
     * @var Rediska_KeyDistributor_Abstract
     */
    protected $_keyDistributor;

    /**
     * Configuration
     * 
     * namespace      - Key names prefix
     * servers        - Array of servers: array(
     *                                        array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 1, 'password' => '123', 'alias' => 'example'),
     *                                        'alias' => array('host' => '127.0.0.1', 'port' => 6380, 'weight' => 2)
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
     *                                        array('host' => '127.0.0.1', 'port' => 6379, 'weight' => 1, 'password' => '123', 'alias' => 'example'),
     *                                        'alias' => array('host' => '127.0.0.1', 'port' => 6380, 'weight' => 2)
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

        // Check class
        $classReflection = new ReflectionClass($className);
        if (!in_array('Rediska_Command_Interface', $classReflection->getInterfaceNames())) {
            throw new Rediska_Exception("Class '$className' must implement Rediska_Command_Interface interface");
        }
        $methodCreate = $classReflection->getMethod('create');
        if (!$methodCreate || !$methodCreate->isPublic()) {
            throw new Rediska_Exception("Class '$className' must have public method 'create'");
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

    	$connection = null;

    	$this->_connections[$connectionString] = new Rediska_Connection($options);

        $this->_keyDistributor->addConnection(
            $connectionString,
            isset($options['weight']) ? $options['weight'] : Rediska_Connection::DEFAULT_WEIGHT
        );

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
        if (count($this->_connections) == 1) {
            $connections = array_values($this->_connections);
            $connection = $connections[0];
        } else if ($this->_specifiedConnection->getConnection()) {
            $connection = $this->_specifiedConnection->getConnection();
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
        require_once 'Rediska/Pipeline.php';

        return new Rediska_Pipeline($this, $this->_specifiedConnection);
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

        // Load native Rediska command class
        if (strpos(self::$_commands[$lowerName], 'Rediska_Command_') === 0) {
            require_once 'Rediska/Command/' . substr(self::$_commands[$lowerName], 16) . '.php';
        }

        // Initailize command
        return new self::$_commands[$lowerName]($this, $name, $arguments);
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
     * Serialize value
     * 
     * @param mixin $value Value for serialize
     * @return string
     */
    public function serialize($value)
    {
        if (is_numeric($value)) {
            return (string)$value;
        } else {
            return call_user_func($this->_options['serializer'], $value);
        }
    }

    /**
     * Unserailize value
     * 
     * @param string $value Serialized value
     * @return mixin
     */
    public function unserialize($value)
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
    
    public function __call($name, $args)
    {
        $this->_specifiedConnection->resetConnection();

        $command = $this->getCommand($name, $args);
        $command->write();
        return $command->read();
    }
}