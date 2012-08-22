<?php

/**
 * Rediska transaction
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Transaction
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
     * Rediska specified connection instance
     * 
     * @var Rediska_Connection_Specified
     */
    protected $_specifiedConnection;

    /**
     * Commands buffer
     * 
     * @var array
     */
    protected $_commands = array();

    /**
     * If transaction watched something
     *
     * @var bool
     */
    protected $_isWatched = false;

    /**
     * Constructor
     * 
     * @param Rediska            $rediska    Rediska instance
     * @param Rediska_Connection $connection Trancation connection
     */
    public function __construct(Rediska $rediska, Rediska_Connection_Specified $specifiedConnection, Rediska_Connection $connection)
    {
        $this->_rediska             = $rediska;
        $this->_specifiedConnection = $specifiedConnection;
        $this->_connection          = $connection;

        $this->_throwIfNotSupported('Transactions', '1.3.8');
    }

    /**
     * Marks the given keys to be watched for conditional execution of a transaction.
     *
     * @throws Rediska_Transaction_Exception
     * @param  $keyOrKeys Key or array of keys
     * @return Rediska_Transaction
     */
    public function watch($keyOrKeys)
    {
        $this->_throwIfNotSupported('Watch', '2.1');

        $command = array('WATCH');

        if (!is_array($keyOrKeys)) {
            $keys = array($keyOrKeys);
        } else {
            $keys = $keyOrKeys;
        }

        foreach($keys as $key) {
            $command[] = $this->_rediska->getOption('namespace') . $key;
        }

        $exec = new Rediska_Connection_Exec($this->_connection, $command);
        $exec->execute();

        $this->_isWatched = true;

        return $this;
    }

    /**
     * Flushes all the previously watched keys for a transaction.
     *
     * @return Rediska_Transaction
     */
    public function unwatch()
    {
        $this->_throwIfNotSupported('Unwatch', '2.1');

        $command = 'UNWATCH';

        $exec = new Rediska_Connection_Exec($this->_connection, $command);
        $exec->execute();

        $this->_isWatched = false;

        return $this;
    }

    /**
     * Execute transaction
     * 
     * @return array
     */
    public function execute()
    {
        $results = array();

        $this->_rediska->getProfiler()->start($this);

        $multi = new Rediska_Connection_Exec($this->_connection, 'MULTI');
        $multi->execute();

        foreach($this->_commands as $command) {
            $command->execute();
        }

        $exec = new Rediska_Connection_Exec($this->_connection, 'EXEC');
        $responses = $exec->execute();

        $this->_rediska->getProfiler()->stop();

        if (!$responses) {
            throw new Rediska_Transaction_AbortedException('Transaction has been aborted by server');
        }

        foreach($this->_commands as $i => $command) {
            $results[] = $command->parseResponses(array($responses[$i]));
        }

        $this->_reset();

        return $results;
    }

    /**
     * Magic method for execute
     *
     * @return array
     */
    public function __invoke()
    {
        return $this->execute();
    }

    /**
     * Discard transaction
     * 
     * @return boolean
     */
    public function discard()
    {
        if ($this->_isWatched) {
            $this->unwatch();
        }

        $this->_reset();

        return true;
    }

    /**
     * Magic method for adding command to transaction
     * 
     * @param $name
     * @param $args
     * @return Rediska_Transaction
     */
    public function __call($name, $args)
    {
        if (in_array(strtolower($name), array('on', 'pipeline', 'monitor', 'config', 'subscribe'))) {
            throw new Rediska_Transaction_Exception("You can't use '$name' in transaction");
        }

        return $this->_addCommand($name, $args);
    }

    /**
     * Add command to transaction
     *
     * @param string $name Command name
     * @param array  $args Arguments
     * @return Rediska_Transaction
     */
    protected function _addCommand($name, $args = array())
    {
        $this->_specifiedConnection->setConnection($this->_connection);

        $command = Rediska_Commands::get($this->_rediska, $name, $args);
        $command->initialize();

        if (!$command->isAtomic()) {
            throw new Rediska_Exception("Command '$name' doesn't work properly (not atomic) in transaction on multiple servers");
        }

        $this->_commands[] = $command;

        $this->_specifiedConnection->resetConnection();

        return $this;
    }

    /**
     * Throw if transaction not supported by Redis
     */
    protected function _throwIfNotSupported($title, $version)
    {
        $redisVersion = $this->_rediska->getOption('redisVersion');
        if (version_compare($version, $this->_rediska->getOption('redisVersion')) == 1) {
            throw new Rediska_Transaction_Exception("$title requires {$version}+ version of Redis server. Current version is {$redisVersion}. To change it specify 'redisVersion' option.");
        }
    }

    public function  __toString()
    {
        if (empty($this->_commands)) {
            return 'Empty transaction';
        } else {
            return 'Transaction: ' . implode(', ', $this->_commands);
        }
    }

    protected function _reset()
    {
        $this->_commands = array();
        $this->_isWatched = false;
    }

    /**
     * Generated command methods by 'scripts/add_command_methods.php'
     */

    /**
     * Ask the server to silently close the connection.
     *
     * @return Rediska_Transaction
     */
    public function quit() { $args = func_get_args(); return $this->_addCommand('quit', $args); }

    /**
     * Test if a key exists
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function exists($key) { $args = func_get_args(); return $this->_addCommand('exists', $args); }

    /**
     * Delete a key or keys
     *
     * @param string|array $keyOrKeys Key name or array of key names
     * @return Rediska_Transaction
     */
    public function delete($keyOrKeys) { $args = func_get_args(); return $this->_addCommand('delete', $args); }

    /**
     * Get key type
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getType($key) { $args = func_get_args(); return $this->_addCommand('getType', $args); }

    /**
     * Returns all the keys matching the glob-style pattern
     * Glob style patterns examples:
     *   h?llo will match hello hallo hhllo
     *   h*llo will match hllo heeeello
     *   h[ae]llo will match hello and hallo, but not hillo
     *
     * @param string $pattern Pattern
     * @return Rediska_Transaction
     */
    public function getKeysByPattern($pattern) { $args = func_get_args(); return $this->_addCommand('getKeysByPattern', $args); }

    /**
     * Return a random key from the key space
     *
     * @return Rediska_Transaction
     */
    public function getRandomKey() { $args = func_get_args(); return $this->_addCommand('getRandomKey', $args); }

    /**
     * Rename the old key in the new one
     *
     * @param string            $oldKey    Old key name
     * @param string            $newKey    New key name
     * @param boolean[optional] $overwrite Overwrite the new name key if it already exists. For default is false.
     * @return Rediska_Transaction
     */
    public function rename($oldKey, $newKey, $overwrite = true) { $args = func_get_args(); return $this->_addCommand('rename', $args); }

    /**
     * Get the number of keys
     *
     * @return Rediska_Transaction
     */
    public function getKeysCount() { $args = func_get_args(); return $this->_addCommand('getKeysCount', $args); }

    /**
     * Set a time to live in seconds or timestamp on a key
     *
     * @param string  $key                   Key name
     * @param integer $secondsOrTimestamp    Time in seconds or timestamp
     * @param boolean $isTimestamp[optional] Time is timestamp. For default is false.
     * @return Rediska_Transaction
     */
    public function expire($key, $secondsOrTimestamp, $isTimestamp = false) { $args = func_get_args(); return $this->_addCommand('expire', $args); }

    /**
     * Get key lifetime
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getLifetime($key) { $args = func_get_args(); return $this->_addCommand('getLifetime', $args); }

    /**
     * Select the DB having the specified index
     *
     * @param integer $index Db index
     * @return Rediska_Transaction
     */
    public function selectDb($index) { $args = func_get_args(); return $this->_addCommand('selectDb', $args); }

    /**
     * Move the key from the currently selected DB to the DB having as index dbindex
     *
     * @param string  $key     Key name
     * @param integer $dbIndex Redis DB index
     * @return Rediska_Transaction
     */
    public function moveToDb($key, $dbIndex) { $args = func_get_args(); return $this->_addCommand('moveToDb', $args); }

    /**
     * Remove all the keys of the currently selected DB
     *
     * @param boolean[optional] $all Remove from all Db. For default is false.
     * @return Rediska_Transaction
     */
    public function flushDb($all = false) { $args = func_get_args(); return $this->_addCommand('flushDb', $args); }

    /**
     * Set value to a key or muliple values to multiple keys
     *
     * @param string|array $keyOrData                  Key name or array with key => value.
     * @param mixed        $valueOrOverwrite[optional] Value or overwrite property for array of values. For default true.
     * @param boolean      $overwrite[optional]        Overwrite for single value (if false don't set and return false if key already exist). For default true.
     * @return Rediska_Transaction
     */
    public function set($keyOrData, $valueOrOverwrite = null, $overwrite = true) { $args = func_get_args(); return $this->_addCommand('set', $args); }

    /**
     * Set + Expire atomic command
     *
     * @param string  $key      Key name
     * @param mixed   $value    Value
     * @param integer $seconds  Expire time in seconds
     * @return Rediska_Transaction
     */
    public function setAndExpire($key, $value, $seconds) { $args = func_get_args(); return $this->_addCommand('setAndExpire', $args); }

    /**
     * Atomic set value and return old 
     *
     * @param string $key   Key name
     * @param mixed  $value Value
     * @return Rediska_Transaction
     */
    public function setAndGet($key, $value) { $args = func_get_args(); return $this->_addCommand('setAndGet', $args); }

    /**
     * Get value of key or array of values by array of keys
     *
     * @param string|array $keyOrKeys Key name or array of names
     * @return Rediska_Transaction
     */
    public function get($keyOrKeys) { $args = func_get_args(); return $this->_addCommand('get', $args); }

    /**
     * Append value to a end of string key
     *
     * @param string $key    Key name
     * @param mixed  $value  Value
     * @return Rediska_Transaction
     */
    public function append($key, $value) { $args = func_get_args(); return $this->_addCommand('append', $args); }

    /**
     * Increment the number value of key by integer
     *
     * @param string            $key    Key name
     * @param integer[optional] $amount Amount to increment. One for default
     * @return Rediska_Transaction
     */
    public function increment($key, $amount = 1) { $args = func_get_args(); return $this->_addCommand('increment', $args); }

    /**
     * Decrement the number value of key by integer
     *
     * @param string            $key    Key name
     * @param integer[optional] $amount Amount to decrement. One for default
     * @return Rediska_Transaction
     */
    public function decrement($key, $amount = 1) { $args = func_get_args(); return $this->_addCommand('decrement', $args); }

    /**
     * Overwrite part of a string at key starting at the specified offset
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @param integer $value  Value
     * @return Rediska_Transaction
     */
    public function setRange($key, $offset, $value) { $args = func_get_args(); return $this->_addCommand('setRange', $args); }

    /**
     * Return a subset of the string from offset start to offset end (both offsets are inclusive)
     *
     * @param string            $key   Key name
     * @param integer           $start Start
     * @param integer[optional] $end   End. If end is omitted, the substring starting from $start until the end of the string will be returned. For default end of string
     * @return Rediska_Transaction
     */
    public function getRange($key, $start, $end = -1) { $args = func_get_args(); return $this->_addCommand('getRange', $args); }

    /**
     * Return a subset of the string from offset start to offset end (both offsets are inclusive)
     *
     * @param string            $key   Key name
     * @param integer           $start Start
     * @param integer[optional] $end   End. If end is omitted, the substring starting from $start until the end of the string will be returned. For default end of string
     * @return Rediska_Transaction
     */
    public function substring($key, $start, $end = -1) { $args = func_get_args(); return $this->_addCommand('substring', $args); }

    /**
     * Returns the bit value at offset in the string value stored at key
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @param integer $bit    Bit (0 or 1)
     * @return Rediska_Transaction
     */
    public function setBit($key, $offset, $bit) { $args = func_get_args(); return $this->_addCommand('setBit', $args); }

    /**
     * Returns the bit value at offset in the string value stored at key
     *
     * @param string  $key    Key name
     * @param integer $offset Offset
     * @return Rediska_Transaction
     */
    public function getBit($key, $offset) { $args = func_get_args(); return $this->_addCommand('getBit', $args); }

    /**
     * Returns the length of the string value stored at key
     *
     * @param string  $key Key name
     * @return Rediska_Transaction
     */
    public function getLength($key) { $args = func_get_args(); return $this->_addCommand('getLength', $args); }

    /**
     * Append value to the end of List
     *
     * @param string            $key                Key name
     * @param mixed             $value              Element value
     * @param boolean[optional] $createIfNotExists  Create list if not exists
     * @return Rediska_Transaction
     */
    public function appendToList($key, $value, $createIfNotExists = true) { $args = func_get_args(); return $this->_addCommand('appendToList', $args); }

    /**
     * Append value to the head of List
     *
     * @param string            $key                Key name
     * @param mixed             $value              Element value
     * @param boolean[optional] $createIfNotExists  Create list if not exists
     * @return Rediska_Transaction
     */
    public function prependToList($key, $value, $createIfNotExists = true) { $args = func_get_args(); return $this->_addCommand('prependToList', $args); }

    /**
     * Return the length of the List value at key
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getListLength($key) { $args = func_get_args(); return $this->_addCommand('getListLength', $args); }

    /**
     * Get List by key
     *
     * @param string  $key                         Key name
     * @param integer $start[optional]             Start index. For default is begin of list
     * @param integer $end[optional]               End index. For default is end of list
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created 
     * @return Rediska_Transaction
     */
    public function getList($key, $start = 0, $end = -1, $responseIterator = false) { $args = func_get_args(); return $this->_addCommand('getList', $args); }

    /**
     * Trim the list at key to the specified range of elements
     *
     * @param string  $key   Key name
     * @param integer $start Start index
     * @param integer $end   End index
     * @return Rediska_Transaction
     */
    public function truncateList($key, $start, $end) { $args = func_get_args(); return $this->_addCommand('truncateList', $args); }

    /**
     * Return element of List by index at key
     *
     * @param string  $key   Key name
     * @param integer $index Index
     * @return Rediska_Transaction
     */
    public function getFromList($key, $index) { $args = func_get_args(); return $this->_addCommand('getFromList', $args); }

    /**
     * Set a new value as the element at index position of the List at key
     *
     * @param string  $key   Key name
     * @param mixed   $value Value
     * @param integer $index Index
     * @return Rediska_Transaction
     */
    public function setToList($key, $index, $member) { $args = func_get_args(); return $this->_addCommand('setToList', $args); }

    /**
     * Delete element from list by member at key
     *
     * @param $key             Key name
     * @param $value           Element value
     * @param $count[optional] Limit of deleted items. For default no limit.
     * @return Rediska_Transaction
     */
    public function deleteFromList($key, $value, $count = 0) { $args = func_get_args(); return $this->_addCommand('deleteFromList', $args); }

    /**
     * Return and remove the first element of the List at key
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function shiftFromList($key) { $args = func_get_args(); return $this->_addCommand('shiftFromList', $args); }

    /**
     * Return and remove the first element of the List at key and block if list is empty or not exists
     *
     * @param string $keyOrKeys   Key name or array of names
     * @param string $timeout     Blocking timeout in seconds. Timeout disabled for default.
     * @return Rediska_Transaction
     */
    public function shiftFromListBlocking($keyOrKeys, $timeout = 0) { $args = func_get_args(); return $this->_addCommand('shiftFromListBlocking', $args); }

    /**
     * Return and remove the last element of the List at key 
     *
     * @param string           $name       Key name
     * @param string[optional] $pushToName If not null - push value to another key.
     * @return Rediska_Transaction
     */
    public function popFromList($key, $pushToKey = null) { $args = func_get_args(); return $this->_addCommand('popFromList', $args); }

    /**
     * Return and remove the last element of the List at key and block if list is empty or not exists
     *
     * @param string|array $keyOrKeys           Key name or array of names
     * @param integer      $timeout[optional]   Timeout. 0 for default - timeout is disabled.
     * @param string       $pushToKey[optional] If not null - push value to another list.
     * @return Rediska_Transaction
     */
    public function popFromListBlocking($keyOrKeys, $timeout = 0, $pushToKey = null) { $args = func_get_args(); return $this->_addCommand('popFromListBlocking', $args); }

    /**
     * Insert a new value as the element before or after the reference value
     *
     * @param string  $key            Key name
     * @param string  $position       BEFORE or AFTER
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return Rediska_Transaction
     */
    public function insertToList($key, $position, $referenceValue, $value) { $args = func_get_args(); return $this->_addCommand('insertToList', $args); }

    /**
     * Insert a new value as the element after the reference value
     *
     * @param string  $key            Key name
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return Rediska_Transaction
     */
    public function insertToListAfter($key, $referenceValue, $value) { $args = func_get_args(); return $this->_addCommand('insertToListAfter', $args); }

    /**
     * Insert a new value as the element before the reference value
     *
     * @param string  $key            Key name
     * @param mixed   $referenceValue Reference value
     * @param mixed   $value          Value
     * @return Rediska_Transaction
     */
    public function insertToListBefore($key, $referenceValue, $value) { $args = func_get_args(); return $this->_addCommand('insertToListBefore', $args); }

    /**
     * Add the specified member to the Set value at key
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @return Rediska_Transaction
     */
    public function addToSet($key, $member) { $args = func_get_args(); return $this->_addCommand('addToSet', $args); }

    /**
     * Remove the specified member from the Set value at key
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @return Rediska_Transaction
     */
    public function deleteFromSet($key, $member) { $args = func_get_args(); return $this->_addCommand('deleteFromSet', $args); }

    /**
     * Get random element from the Set value at key
     *
     * @param string            $key  Key name
     * @param boolean[optional] $pop  If true - pop value from the set. For default is false
     * @return Rediska_Transaction
     */
    public function getRandomFromSet($key, $pop = false) { $args = func_get_args(); return $this->_addCommand('getRandomFromSet', $args); }

    /**
     * Return the number of elements (the cardinality) of the Set at key
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getSetLength($key) { $args = func_get_args(); return $this->_addCommand('getSetLength', $args); }

    /**
     * Test if the specified value is a member of the Set at key
     *
     * @param string $key    Key value
     * @param mixed  $member Member
     * @return Rediska_Transaction
     */
    public function existsInSet($key, $member) { $args = func_get_args(); return $this->_addCommand('existsInSet', $args); }

    /**
     * Return the intersection between the Sets stored at key1, key2, ..., keyN
     *
     * @param array            $keys     Array of key names
     * @param string[optional] $storeKey Store to set with key name
     * @return Rediska_Transaction
     */
    public function intersectSets(array $keys, $storeKey = null) { $args = func_get_args(); return $this->_addCommand('intersectSets', $args); }

    /**
     * Return the union between the Sets stored at key1, key2, ..., keyN
     *
     * @param array            $keys     Array of key names
     * @param string[optional] $storeKey Store to set with key name
     * @return Rediska_Transaction
     */
    public function unionSets(array $keys, $storeKey = null) { $args = func_get_args(); return $this->_addCommand('unionSets', $args); }

    /**
     * Return the difference between the Set stored at key1 and all the Sets key2, ..., keyN
     *
     * @param array            $keys     Array of key names
     * @param string[optional] $storeKey Store to set with key name
     * @return Rediska_Transaction
     */
    public function diffSets(array $keys, $storeKey = null) { $args = func_get_args(); return $this->_addCommand('diffSets', $args); }

    /**
     * Return all the members of the Set value at key
     *
     * @param string  $key Key name
     * @param boolean $responseIterator[optional]  If true - command return iterator which read from socket buffer.
     *                                             Important: new connection will be created 
     * @return Rediska_Transaction
     */
    public function getSet($key, $responseIterator = false) { $args = func_get_args(); return $this->_addCommand('getSet', $args); }

    /**
     * Move the specified member from one Set to another atomically
     *
     * @param string $fromKey From key name
     * @param string $toKey   To key name
     * @param mixed  $member  Member
     * @return Rediska_Transaction
     */
    public function moveToSet($fromKey, $toKey, $member) { $args = func_get_args(); return $this->_addCommand('moveToSet', $args); }

    /**
     * Add member to sorted set
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @param number $score  Score of member
     * @return Rediska_Transaction
     */
    public function addToSortedSet($key, $member, $score) { $args = func_get_args(); return $this->_addCommand('addToSortedSet', $args); }

    /**
     * Delete the specified member from the sorted set by value
     *
     * @param string $key    Key name
     * @param mixed  $member Member
     * @return Rediska_Transaction
     */
    public function deleteFromSortedSet($key, $member) { $args = func_get_args(); return $this->_addCommand('deleteFromSortedSet', $args); }

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
     * @return Rediska_Transaction
     */
    public function getSortedSet($key, $withScores = false, $start = 0, $end = -1, $revert = false, $responseIterator = false) { $args = func_get_args(); return $this->_addCommand('getSortedSet', $args); }

    /**
     * Get members from sorted set by min and max score
     *
     * @param string            $key        Key name
     * @param number            $min        Min score
     * @param number            $max        Max score
     * @param boolean[optional] $withScores Get with scores. For default is false
     * @param integer[optional] $limit      Limit. For default is no limit
     * @param integer[optional] $offset     Offset. For default is no offset
     * @return Rediska_Transaction
     */
    public function getFromSortedSetByScore($key, $min, $max, $withScores = false, $limit = null, $offset = null) { $args = func_get_args(); return $this->_addCommand('getFromSortedSetByScore', $args); }

    /**
     * Get length of Sorted Set
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getSortedSetLength($key) { $args = func_get_args(); return $this->_addCommand('getSortedSetLength', $args); }

    /**
     * Get count of members from sorted set by min and max score
     *
     * @param string $key Key name
     * @param number $min Min score
     * @param number $max Max score
     * @return Rediska_Transaction
     */
    public function getSortedSetLengthByScore($key, $min, $max) { $args = func_get_args(); return $this->_addCommand('getSortedSetLengthByScore', $args); }

    /**
     * Increment score of sorted set element
     *
     * @param string        $key   Key name
     * @param mixed         $value Member
     * @param integer|float $score Score to increment
     * @return Rediska_Transaction
     */
    public function incrementScoreInSortedSet($key, $value, $score) { $args = func_get_args(); return $this->_addCommand('incrementScoreInSortedSet', $args); }

    /**
     * Remove all the elements in the sorted set at key with a score between min and max (including elements with score equal to min or max).
     *
     * @param string  $key   Key name
     * @param numeric $min   Min value
     * @param numeric $max   Max value
     * @return Rediska_Transaction
     */
    public function deleteFromSortedSetByScore($key, $min, $max) { $args = func_get_args(); return $this->_addCommand('deleteFromSortedSetByScore', $args); }

    /**
     * Remove all elements in the sorted set at key with rank between start  and end
     *
     * @param string  $key   Key name
     * @param numeric $start Start position
     * @param numeric $end   End position
     * @return Rediska_Transaction
     */
    public function deleteFromSortedSetByRank($key, $start, $end) { $args = func_get_args(); return $this->_addCommand('deleteFromSortedSetByRank', $args); }

    /**
     * Get member score from Sorted Set
     *
     * @param string $key    Key name
     * @param mixed  $member Member value
     * @return Rediska_Transaction
     */
    public function getScoreFromSortedSet($key, $member) { $args = func_get_args(); return $this->_addCommand('getScoreFromSortedSet', $args); }

    /**
     * Get rank of member from sorted set
     *
     * @param string  $key              Key name
     * @param integer $member           Member value
     * @param boolean $revert[optional] Revert elements (not used in sorting). For default is false
     * @return Rediska_Transaction
     */
    public function getRankFromSortedSet($key, $member, $revert = false) { $args = func_get_args(); return $this->_addCommand('getRankFromSortedSet', $args); }

    /**
     * Store to key union between the sorted sets
     *
     * @param array  $keys       Array of key names or associative array with weights
     * @param string $storeKey   Result sorted set key name
     * @param string $aggregation Aggregation method: SUM (for default), MIN, MAX.
     * @return Rediska_Transaction
     */
    public function unionSortedSets(array $keys, $storeKey, $aggregation = Rediska_Command_UnionSortedSets::SUM) { $args = func_get_args(); return $this->_addCommand('unionSortedSets', $args); }

    /**
     * Store to key intersection between sorted sets
     *
     * @param array  $keys       Array of key names or associative array with weights
     * @param string $storeKey   Result sorted set key name
     * @param string $aggregation Aggregation method: SUM (for default), MIN, MAX.
     * @return Rediska_Transaction
     */
    public function intersectSortedSets(array $keys, $storeKey, $aggregation = Rediska_Command_IntersectSortedSets::SUM) { $args = func_get_args(); return $this->_addCommand('intersectSortedSets', $args); }

    /**
     * Set value to a hash field or fields
     *
     * @param string        $key          Key name
     * @param array|string  $fieldOrData  Field or array of many fields and values: field => value
     * @param mixed         $value        Value for single field
     * @param boolean       $overwrite    Overwrite for single field (if false don't set and return false if key already exist). For default true.
     * @return Rediska_Transaction
     */
    public function setToHash($key, $fieldOrData, $value = null, $overwrite = true) { $args = func_get_args(); return $this->_addCommand('setToHash', $args); }

    /**
     * Get value from hash field or fields
     *
     * @param string       $key           Key name
     * @param string|array $fieldOrFields Field or fields
     * @return Rediska_Transaction
     */
    public function getFromHash($key, $fieldOrFields) { $args = func_get_args(); return $this->_addCommand('getFromHash', $args); }

    /**
     * Increment field value in hash
     *
     * @param string $key              Key name
     * @param mixed  $field            Field
     * @param number $amount[optional] Increment amount. One for default
     * @return Rediska_Transaction
     */
    public function incrementInHash($key, $field, $amount = 1) { $args = func_get_args(); return $this->_addCommand('incrementInHash', $args); }

    /**
     * Test if field is present in hash
     *
     * @param string $key   Key name
     * @param mixed  $field Field
     * @return Rediska_Transaction
     */
    public function existsInHash($key, $field) { $args = func_get_args(); return $this->_addCommand('existsInHash', $args); }

    /**
     * Delete field from hash
     *
     * @param string $key   Key name
     * @param mixed  $field Field
     * @return Rediska_Transaction
     */
    public function deleteFromHash($key, $field) { $args = func_get_args(); return $this->_addCommand('deleteFromHash', $args); }

    /**
     * Return the number of fields in hash
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getHashLength($key) { $args = func_get_args(); return $this->_addCommand('getHashLength', $args); }

    /**
     * Get hash fields and values
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getHash($key) { $args = func_get_args(); return $this->_addCommand('getHash', $args); }

    /**
     * Get hash fields
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getHashFields($key) { $args = func_get_args(); return $this->_addCommand('getHashFields', $args); }

    /**
     * Get hash values
     *
     * @param string $key Key name
     * @return Rediska_Transaction
     */
    public function getHashValues($key) { $args = func_get_args(); return $this->_addCommand('getHashValues', $args); }

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
     * @return Rediska_Transaction
     */
    public function sort($key, array $options = array()) { $args = func_get_args(); return $this->_addCommand('sort', $args); }

    /**
     * Publish message to pubsub channel
     *
     * @param array|string $channelOrChannels Channel or array of channels
     * @param mixed        $message           Message
     * @return Rediska_Transaction
     */
    public function publish($channelOrChannels, $message) { $args = func_get_args(); return $this->_addCommand('publish', $args); }

    /**
     * Save the DB on disk
     *
     * @param boolean[optional] $background Save asynchronously. For default is false
     * @return Rediska_Transaction
     */
    public function save($background = false) { $args = func_get_args(); return $this->_addCommand('save', $args); }

    /**
     * Return the UNIX time stamp of the last successfully saving of the dataset on disk
     *
     * @return Rediska_Transaction
     */
    public function getLastSaveTime() { $args = func_get_args(); return $this->_addCommand('getLastSaveTime', $args); }

    /**
     * Stop all the clients, save the DB, then quit the server
     *
     * @return Rediska_Transaction
     */
    public function shutdown() { $args = func_get_args(); return $this->_addCommand('shutdown', $args); }

    /**
     * Rewrite the Append Only File in background when it gets too big
     *
     * @return Rediska_Transaction
     */
    public function rewriteAppendOnlyFile() { $args = func_get_args(); return $this->_addCommand('rewriteAppendOnlyFile', $args); }

    /**
     * Provide information and statistics about the server
     *
     * @return Rediska_Transaction
     */
    public function info() { $args = func_get_args(); return $this->_addCommand('info', $args); }

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
     * @return Rediska_Transaction
     */
    public function slaveOf($aliasOrConnection) { $args = func_get_args(); return $this->_addCommand('slaveOf', $args); }

}
