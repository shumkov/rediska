<?php

// Require Rediska
require_once dirname(__FILE__) . '/../../../../Rediska.php';

/**
 * @see Zend_Cache_Backend
 */
require_once 'Zend/Cache/Backend.php';

/**
 * @see Zend_Cache_Backend_ExtendedInterface
 */
require_once 'Zend/Cache/Backend/ExtendedInterface.php';

/**
 * Redis adapter for Zend_Cache
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage ZendFrameworkIntegration
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Zend_Cache_Backend_Redis extends Zend_Cache_Backend implements Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Defines the hash field name for the cached data.
     * @var string
     */
    const FIELD_DATA      = 'd';
    /**
     * Defines the hash field name for the cached data mtime.
     * @var string
     */
    const FIELD_MTIME     = 'm';
    /**
     * Defines the hash field name for the cached item tags.
     * @var string
     */
    const FIELD_TAGS      = 't';
    /**
     * Defines the hash field name for the infinite item.
     * @var string
     */
    const FIELD_INF       = 'i';

    /**
     * Redis backend limit
     * @var integer
     */
    const MAX_LIFETIME    = 2592000;
    /**
     * The options storage for this Backend
     * @var array
     */
    protected $_options = array(
        'storage' => array(
            'set_ids'         => 'zc:ids',
            'set_tags'        => 'zc:tags',
            'prefix_key'      => 'zc:k:',
            'prefix_tag_ids'  => 'zc:ti:',
        )
    );
    /**
     * Rediska instance
     *
     * @var Rediska
     */
    protected $_rediska = Rediska::DEFAULT_NAME;
    /**
     *
     * @var Rediska_Transaction
     */
    protected $_transaction;

    /**
     * Contruct Zend_Cache Redis backend
     *
     * Available options are :
     * 'storage`
     *     - set_ids        : the set name that all ids are storage
     *     - set_tags       : the set name that stores the tags
     *     - prefix_key     : the prefix value for all item keys
     *     - prefix_tag_ids : the key prefix value for all tag -to- id items
     *
     * @param mixed $rediska Rediska instance name, Rediska object or array of options
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (isset($options['rediska'])) {
            $this->setRediska($options['rediska']);
        }
        if(isset($options['storage'])){
            $this->setStorage($options['storage']);
        }
    }
    /**
     *
     * @param  array $options
     * @return Rediska_Zend_Cache_Backend_Redis
     * @throws Zend_Cache_Exception
     */
    public function setStorage($options)
    {
        foreach ($options as $name => $value) {
            if (!is_string($value)) {
                Zend_Cache::throwException(
                    sprintf(
                        'Incorrect value for option %s : `string` expected `%s` provided',
                        $name, gettype($value)
                    )
                );
            }
            $name = strtolower($name);
            if(isset($this->_options['storage'][$name]) ){
                $this->_options['storage'][$name] = $value;
            }
        }
        return $this;
    }
    /**
     *
     * @param  Rediska $rediska
     * @return Rediska_Zend_Cache_Backend_Redis
     */
    public function setRediska($rediska)
    {
        $this->_rediska = $rediska;
        return $this;
    }
    /**
     *
     * @return Rediska
     */
    public function getRediska()
    {
        if (!is_object($this->_rediska)) {
            $this->_rediska = Rediska_Options_RediskaInstance::getRediskaInstance(
                $this->_rediska, 'Zend_Cache_Exception', 'backend'
            );
        }

        return $this->_rediska;
    }
    /**
     *
     * @param  string $name
     * @return multitype:|boolean
     */
    public function getOption($name)
    {
        if (!is_string($name)) {
            Zend_Cache::throwException(
                sprintf(
                    'Incorrect option name %s provided, `string` expected `%s` provided',
                    $name, gettype($name)
                )
            );
        }
        $name = strtolower($name);
        if(isset($this->_options[$name])){
            return $this->_options[$name];
        }
        return false;
    }
    /**
     * Load value with given id from cache
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $id = (array) $id;
        foreach ($id as $key){
            $key = $this->_options['storage']['prefix_key'] . $key;
            $this->_getTransactionByKey($key)
                ->getFromHash(
                    $key,
                    self::FIELD_DATA
                );
        }
        $oldSerializaerAdapter = $this->getRediska()->getSerializer()->getAdapter();
        $this->getRediska()->setSerializerAdapter('toString');
        $result = $this->_execTransactions();
        $this->getRediska()->getSerializer()->setAdapter($oldSerializaerAdapter);
        if(count($result) == 1){
            if(null === ($result = array_shift($result))){
                return false;
            } else {
                return $result;
            }
        } else {
            return $result;
        }
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id Cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        $mtime = $this->getRediska()->getFromHash(
            $this->_options['storage']['prefix_key'].$id, self::FIELD_MTIME
        );
        return ($mtime ? $mtime : false);
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string  $data             Datas to cache
     * @param  string  $id               Cache id
     * @param  array   $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int     $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        if(!is_array($tags)) $tags = array($tags);

        $lifetime = $this->getLifetime($specificLifetime);

        $oldTags = explode(
            ',', $this->getRediska()->getFromHash(
                $this->_options['storage']['prefix_key'].$id, self::FIELD_TAGS
            )
        );

        $this->_getTransactionByKey($this->_options['storage']['prefix_key'].$id)
            ->setToHash(
                $this->_options['storage']['prefix_key'].$id,  array(
                self::FIELD_DATA => $data,
                self::FIELD_TAGS => implode(',',$tags),
                self::FIELD_MTIME => time(),
                self::FIELD_INF => $lifetime ? 0 : 1)
            );
        $this->_getTransactionByKey($this->_options['storage']['prefix_key'].$id)
            ->expire($this->_options['storage']['prefix_key'].$id, $lifetime ? $lifetime : self::MAX_LIFETIME);
        if ($addTags = ($oldTags ? array_diff($tags, $oldTags) : $tags)) {
            foreach ($addTags as $add) {
                $this->_getTransactionByKey($this->_options['storage']['set_tags'])
                    ->addToSet($this->_options['storage']['set_tags'], $add);
            }
            foreach($addTags as $tag){
                $this->_getTransactionByKey($this->_options['storage']['prefix_tag_ids'] . $tag)
                    ->addToSet($this->_options['storage']['prefix_tag_ids'] . $tag, $id);
            }
        }
        if ($remTags = ($oldTags ? array_diff($oldTags, $tags) : false)){
            foreach($remTags as $tag){
                $this->_getTransactionByKey($this->_options['storage']['prefix_tag_ids'] . $tag)
                    ->deleteFromSet($this->_options['storage']['prefix_tag_ids'] . $tag, $id);
            }
        }
        $this->_getTransactionByKey($this->_options['storage']['set_ids'])
            ->addToSet($this->_options['storage']['set_ids'], $id);
        try {
            $this->_execTransactions();
            return true;
        } catch(Rediska_Transaction_Exception $e){
            $this->_log($e->getMessage(), Zend_Log::ERR);
            return false;
        }

    }

    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        $tags = explode(
            ',', $this->getRediska()->getFromHash(
                $this->_options['storage']['prefix_key'].$id, self::FIELD_TAGS
            )
        );

        $this->_getTransactionByKey($this->_options['storage']['prefix_key'].$id)
            ->delete($this->_options['storage']['prefix_key'].$id);
        $this->_getTransactionByKey($this->_options['storage']['set_ids'], $id)
            ->deleteFromSet( $this->_options['storage']['set_ids'], $id );
        foreach($tags as $tag) {
            $this->_getTransactionByKey($this->_options['storage']['prefix_tag_ids'] . $tag)
                ->deleteFromSet($this->_options['storage']['prefix_tag_ids'] . $tag, $id);
        }
        $result = $this->_execTransactions();
        if(count($result)){
            return array_shift($result);
        } else {
            return false;
        }
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => supported
     * 'matchingTag'    => supported
     * 'notMatchingTag' => supported
     * 'matchingAnyTag' => supported
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @throws Zend_Cache_Exception
     * @return boolean True if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        if( $tags && ! is_array($tags)) {
            $tags = array($tags);
        }
        $result = true;
        switch ($mode) {
            case Zend_Cache::CLEANING_MODE_ALL:
                $this->_removeIds($this->getIds());
                break;

            case Zend_Cache::CLEANING_MODE_OLD:
                break;

            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                $this->_removeIdsByMatchingTags($tags);
                break;

            case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                $this->_removeIdsByNotMatchingTags($tags);
                break;

            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                $this->_removeIdsByMatchingAnyTags($tags);
                break;

            default:
                Zend_Cache::throwException('Invalid mode for clean() method: '.$mode);
        }
        return $this->_collectGarbage();
    }
    /**
     *
     * @param  array $ids
     * @return boolean
     */
    protected function _removeIds($ids = array())
    {
        foreach($ids as $id){
            $this->_getTransactionByKey($this->_options['storage']['prefix_key']. $id)
                ->delete($this->_options['storage']['prefix_key']. $id);
            $this->_getTransactionByKey($this->_options['storage']['set_ids'])
                ->deleteFromSet( $this->_options['storage']['set_ids'], $id);
        }
        return (bool) $this->_execTransactions();
    }

    /**
     * @param array $tags
     */
    protected function _removeIdsByNotMatchingTags($tags)
    {
        $ids = $this->getIdsNotMatchingTags($tags);
        $this->_removeIds($ids);
    }
    /**
     * @param array $tags
     */
    protected function _removeIdsByMatchingTags($tags)
    {
        $ids = $this->getIdsMatchingTags($tags);
        $this->_removeIds($ids);
    }

    /**
     * @param array $tags
     */
    protected function _removeIdsByMatchingAnyTags($tags)
    {
        $ids = $this->getIdsMatchingAnyTags($tags);
        $this->_removeIds($ids);

        foreach ($this->_preprocessTagIds($tags) as $tag) {
            $this->_getTransactionByKey($tag)->delete($tag);
        }
        $this->_getTransactionByKey($this->_options['storage']['set_tags'])
            ->deleteFromSet( $this->_options['storage']['set_tags'], $tags);

        return (bool) $this->_execTransactions();
    }
    /**
     * Return true if the automatic cleaning is available for the backend
     *
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }

    /**
     * Set the frontend directives
     *
     * @param  array $directives Assoc of directives
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setDirectives($directives)
    {
        parent::setDirectives($directives);
        $lifetime = $this->getLifetime(false);
        if ($lifetime > self::MAX_LIFETIME) {
            $this->_log(
                'redis backend has a limit of 30 days (' . self::MAX_LIFETIME
                . ' seconds) for the lifetime'
            );
        }
    }

    /**
     * Return an array of stored cache ids
     *
     * @return array array of stored cache ids (string)
     */
    public function getIds()
    {
        return (array) $this->getRediska()->getSet($this->_options['storage']['set_ids']);
    }

    /**
     * Return an array of stored tags
     *
     * @return array array of stored tags (string)
     */
    public function getTags()
    {
        return $this->getRediska()->getSet($this->_options['storage']['set_tags']);
    }

    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     * @return array array of matching cache ids (string)
     */
    public function getIdsMatchingTags($tags = array())
    {
        return (array) $this->getRediska()->intersectSets(
            $this->_preprocessTagIds($tags)
        );
    }

    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param array $tags array of tags
     * @return array array of not matching cache ids (string)
     */
    public function getIdsNotMatchingTags($tags = array())
    {
            $sets = $this->_preprocessTagIds($tags);
            array_unshift($sets, $this->_options['storage']['set_ids']);
            $data = $this->getRediska()->diffSets($sets);
            return $data;
    }

    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     * @return array array of any matching cache ids (string)
     */
    public function getIdsMatchingAnyTags($tags = array())
    {
        return (array) $this->getRediska()->unionSets($this->_preprocessTagIds($tags));
    }

    /**
     * Return the filling percentage of the backend storage
     *
     * @throws Zend_Cache_Exception
     * @return int integer between 0 and 100
     */
    public function getFillingPercentage()
    {
        $this->_log("Filling percentage not supported by the Redis backend");
        return 0;
    }

    /**
     * Return an array of metadatas for the given cache id
     *
     * The array must include these keys :
     * - expire : the expire timestamp
     * - tags : a string array of tags
     * - mtime : timestamp of last modification time
     *
     * @param string $id cache id
     * @return array array of metadatas (false if the cache id is not found)
     */
    public function getMetadatas($id)
    {
        $metaData = $this->getRediska()->getFromHash(
            $this->_options['storage']['prefix_key'].$id,
            array(self::FIELD_DATA, self::FIELD_TAGS, self::FIELD_MTIME, self::FIELD_INF)
        );
        if(!$metaData[self::FIELD_MTIME]) {
          return false;
        }
        $lifetime = $this->getRediska()
            ->getLifetime($this->_options['storage']['prefix_key'] . $id);
        $tags = explode(',', $metaData[self::FIELD_TAGS]);
        $expire = $metaData[self::FIELD_INF] === '1' ? false : time() + $lifetime;

        return array(
            'expire' => $expire,
            'tags'   => $tags,
            'mtime'  => $metaData[self::FIELD_MTIME],
        );
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id cache id
     * @param int $extraLifetime
     * @return boolean true if ok
     */
    public function touch($id, $extraLifetime)
    {
        $data = $this->getRediska()->getFromHash(
            $this->_options['storage']['prefix_key'].$id, array(self::FIELD_INF)
        );
        $lifetime = $this->getRediska()
            ->getLifetime($this->_options['storage']['prefix_key'] . $id);
        if ($data[self::FIELD_INF] === 0) {
            $expireAt = time() + $lifetime + $extraLifetime;
            return (bool) $this->getRediska()->expire(
                $this->_options['storage']['prefix_key'].$id, $expireAt, true
            );
        }
        return false;
    }

    /**
     * Return an associative array of capabilities (booleans) of the backend
     *
     * The array must include these keys :
     * - automatic_cleaning (is automating cleaning necessary)
     * - tags (are tags supported)
     * - expired_read (is it possible to read expired cache records
     *                 (for doNotTestCacheValidity option for example))
     * - priority does the backend deal with priority when saving
     * - infinite_lifetime (is infinite lifetime can work with this backend)
     * - get_list (is it possible to get the list of cache ids and the complete list of tags)
     *
     * @return array associative of with capabilities
     */
    public function getCapabilities()
    {
        return array(
            'automatic_cleaning' => true,
            'tags'               => true,
            'expired_read'       => true,
            'priority'           => false,
            'infinite_lifetime'  => true,
            'get_list'           => true
        );
    }
    /**
     * Cleans up expired keys and list members
     * @return boolean
     */
    protected function _collectGarbage()
    {
        $exists = array();
        $tags = $this->getTags();
        foreach($tags as $tag){
            $prefix_tag_ids = $this->_options['storage']['prefix_tag_ids'] . $tag;
            $tagMembers = $this->getRediska()->getSet($prefix_tag_ids);
            $this->_getTransactionByKey($prefix_tag_ids)->watch($prefix_tag_ids);
            $expired = array();
            if(count($tagMembers)) {
                foreach($tagMembers as $id) {
                    if( ! isset($exists[$id])) {
                        $exists[$id] = $this->getRediska()
                            ->exists($this->_options['storage']['prefix_key'].$id);
                    }
                    if(!$exists[$id]) {
                        $expired[] = $id;
                    }
                }
                if(!count($expired)) continue;
            }
            if(!count($tagMembers) || count($expired) == count($tagMembers)) {
                $this->_getTransactionByKey($this->_options['storage']['set_tags'])
                    ->deleteFromSet($this->_options['storage']['set_tags'], $tag);
                $this->_getTransactionByKey($this->_options['storage']['prefix_tag_ids'] . $tag)
                    ->delete($this->_options['storage']['prefix_tag_ids'] . $tag);
            } else {
                $this->_getTransactionByKey($this->_options['storage']['prefix_tag_ids'] . $tag)
                    ->deleteFromSet( $this->_options['storage']['prefix_tag_ids'] . $tag, $expired);
            }
            $this->_getTransactionByKey($this->_options['storage']['set_ids'])
                ->deleteFromSet( $this->_options['storage']['set_ids'], $expired);
        }
        try{
            return (bool) $this->_execTransactions();
        } catch (Rediska_Transaction_AbortedException $e ){
            $this->_log($e->getMessage(), Zend_Log::ERR);
            return false;
        }
    }
    /**
     * @param $item
     * @param $index
     * @param $prefix
     */
    protected function _preprocess(&$item, $index, $prefix)
    {
        $item = $prefix . $item;
    }

    /**
     * @param $ids
     * @return array
     */
    protected function _preprocessIds($ids)
    {
        array_walk($ids, array($this, '_preprocess'), $this->_options['storage']['prefix_key']);
        return $ids;
    }
    /**
     * @param $tags
     * @return array
     */
    protected function _preprocessTagIds($tags)
    {
        if($tags){
            array_walk($tags, array($this, '_preprocess'), $this->_options['storage']['prefix_tag_ids']);
        }
        return $tags;
    }

    /**
     * @param string $key
     *
     * @return Rediska_Transaction
     */
    protected function _getTransactionByKey($key)
    {
        $connection = $this->getRediska()->getConnectionByKeyName($key);
        if(!$this->_transaction[$connection->getAlias()]){
            $this->_transaction[$connection->getAlias()] = $this->getRediska()
                ->transaction($connection);
        }
        return $this->_transaction[$connection->getAlias()];
    }

    /**
     * @return array
     */
    protected function _execTransactions()
    {
        $result = array();
        /* @var Rediska_Transaction $transaction */
        if ($this->_transaction) {
            foreach ($this->_transaction as $transaction) {
                /*
                 * Do not execute empty transaction avoiding a false
                 * `Rediska_Transaction_AbortedException`
                 */
                $preamble = substr(
                    (string)$transaction, 0,
                    strlen(Rediska_Transaction::TRANSACTION_PREAMBLE)
                );
                if (Rediska_Transaction::TRANSACTION_PREAMBLE == $preamble){
                    $result = array_merge($result, $transaction->execute());
                } else {
                    $transaction->discard();
                }
            }
        }
        return $result;
    }
}
