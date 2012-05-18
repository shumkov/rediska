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
    const REDISKA_TAGS = '__REDISKA__TAGS__SET__';
    const REDISKA_IDS =  '__REDISKA_IDS__';
    /**
     * Rediska instance
     *
     * @var Rediska
     */
    protected $_rediska = Rediska::DEFAULT_NAME;

    /**
     * Contruct Zend_Cache Redis backend
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
    }

    public function setRediska($rediska)
    {
        $this->_rediska = $rediska;

        return $this;
    }

    public function getRediska()
    {
        if (!is_object($this->_rediska)) {
            $this->_rediska = Rediska_Options_RediskaInstance::getRediskaInstance($this->_rediska, 'Zend_Cache_Exception', 'backend');
        }

        return $this->_rediska;
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
        $tmp = $this->getRediska()->get($id);

        if (is_array($id)) {
            foreach ($id as $k) {
                if (isset($tmp[$k])) {
                    $tmp[$k] = $tmp[$k][0];
                }
            }
            return $tmp;
        } else if (is_array($tmp)) {
            return $tmp[0];
        }

        return false;
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id Cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        $tmp = $this->getRediska()->get($id);
        if (is_array($tmp)) {
            return $tmp[1];
        }
        return false;
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $id               Cache id
     * @param  array  $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $lifetime = $this->getLifetime($specificLifetime);

        if ($lifetime) {
            $result = $this->getRediska()->setAndExpire($id, array($data, time(), $lifetime), $lifetime);
            $result = $this->getRediska()->setAndExpire(
                self::REDISKA_IDS . $id, array($tags, time(), $lifetime), $lifetime
            );
        } else {
            $result = $this->getRediska()->set($id, array($data, time(), $lifetime));
            $result = $this->getRediska()->set(
                self::REDISKA_IDS . $id, array($tags, time(), $lifetime)
            );
        }
        foreach ($tags as $tag) {
            $this->getRediska()->addToSet(self::REDISKA_TAGS . $tag, $id);
            $this->getRediska()->addToSet(self::REDISKA_TAGS, $tag);
        }

        return $result;
    }

    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        return $this->getRediska()->delete($id);
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => unsupported
     * 'matchingTag'    => unsupported
     * 'notMatchingTag' => unsupported
     * 'matchingAnyTag' => unsupported
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @throws Zend_Cache_Exception
     * @return boolean True if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        $ids = null;
        switch ($mode) {
            case Zend_Cache::CLEANING_MODE_ALL:
                return $this->getRediska()->flushDb();
                break;
            case Zend_Cache::CLEANING_MODE_OLD:
                $this->_log("Rediska_Zend_Cache_Backend_Redis::clean() : CLEANING_MODE_OLD is unsupported by the Redis backend");
                break;
            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                $ids = $this->getIdsMatchingTags($tags);
                break;
            case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                $ids = $this->getIdsNotMatchingTags($tags);
                break;
            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                $ids = $this->getIdsMatchingAnyTags($tags);
                break;
            default:
                Zend_Cache::throwException('Invalid mode for clean() method');
                break;
        }
        if((bool) $ids){
            $pipe = $this->getRediska()->pipeline();
            foreach ($ids as $key) {
                $pipe->expire($key, -1000);
                $pipe->expire(self::REDISKA_IDS . $key, -1000);
            }
            $pipe->execute();
        }
        return true;
    }

    /**
     * Return true if the automatic cleaning is available for the backend
     *
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {
        return false;
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
        if ($lifetime > 2592000) {
            $this->_log('redis backend has a limit of 30 days (2592000 seconds) for the lifetime');
        }
    }

    /**
     * Return an array of stored cache ids
     *
     * @return array array of stored cache ids (string)
     */
    public function getIds()
    {
        $result = $this->getRediska()->getKeysByPattern(self::REDISKA_IDS . '*');
        $result = array_map(array($this, '_filterIds'),$result);
        return (bool) $result ? $result : array();
    }
    /**
     * Return an array of stored tags
     *
     * @return array array of stored tags (string)
     */
    public function getTags()
    {
        $result = $this->getRediska()->getSet(self::REDISKA_TAGS);
        return (bool) $result ? $result : array();
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
        foreach ($tags as $tag) {
            $t[] = self::REDISKA_TAGS . $tag;
        }
        if((bool) $t){
            $result = $this->getRediska()->intersectSets($t);
        }
        return (bool) $result ? $result : array();
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
        $result = array();
        $ids = $this->getIds();
        foreach ($tags as $tag) {
            $t[] = self::REDISKA_TAGS . $tag;
        }
        $tagSet = $this->getRediska()->unionSets($t);
        $result = array_diff($ids,$tagSet);
        return (bool) $result  ? $result : array();
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
        foreach ($tags as $tag) {
            $t[] = self::REDISKA_TAGS . $tag;
        }
        if((bool) $t){
            $result = $this->getRediska()->unionSets($t);
        }
        return (bool) $result ? array_unique($result) : array();
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
        $tmp = $this->getRediska()->get($id);
        $tags = $this->getRediska()->get(self::REDISKA_IDS . $id);
        if (is_array($tmp)) {
            $data = $tmp[0];
            $mtime = $tmp[1];
            if (!isset($tmp[2])) {
                // because this record is only with 1.7 release
                // if old cache records are still there...
                return false;
            }
            $lifetime = $tmp[2];
            return array(
                'expire' => $mtime + $lifetime,
                'tags' => (bool) $tags[0] ? $tags[0] : array(),
                'mtime' => $mtime
            );
        }
        return false;
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
        $tmp = $this->getRediska()->get($id);
        if (is_array($tmp)) {
            $data = $tmp[0];
            $mtime = $tmp[1];
            if (!isset($tmp[2])) {
                // because this record is only with 1.7 release
                // if old cache records are still there...
                return false;
            }
            $lifetime = $tmp[2];
            $newLifetime = $lifetime - (time() - $mtime) + $extraLifetime;
            if ($newLifetime <=0) {
                return false;
            }
            return $this->getRediska()->setAndExpire($id, array($data, time(), $newLifetime), $newLifetime);
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
            'automatic_cleaning' => false,
            'tags'               => true,
            'expired_read'       => false,
            'priority'           => false,
            'infinite_lifetime'  => false,
            'get_list'           => true
        );
    }
    /**
     * Utilized by the `getIds` methods to acquire the list of all IDs
     *
     * @param string $val
     * @return string
     */
    protected function _filterIds($val)
    {
        static $length = 0;
        if(!$length){
            $length = strlen(self::REDISKA_IDS);
        }
        return substr($val, $length);
    }
}