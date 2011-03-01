<?php

/**
 * Rediska key abstract class
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Key objects
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_Key_Abstract extends Rediska_Options_RediskaInstance
{
    /**
     * Key name
     * 
     * @var string
     */
    protected $_name;
    
    /**
     * Exception class name for options
     * 
     * @var string
     */
    protected $_optionsException = 'Rediska_Key_Exception';

    /**
     * Options:
     * 
     * expire            - Expire time
     * expireIsTimestamp - Expire time is timestamp. For default false (in seconds)
     * serverAlias       - Server alias or connection object
     * rediska           - Rediska instance name, Rediska object or Rediska options for new instance
     *
     * @var array
     */
    protected $_options = array(
        'serverAlias'       => null,
        'expire'            => null,
        'expireIsTimestamp' => false,
    );

    /**
     * Construct key
     *
     * @param string                    $name        Key name
     * @param integer                   $options     Options:
     *                                                  expire            - Expire time
     *                                                  expireIsTimestamp - Expire time is timestamp. For default false (in seconds)
     *                                                  serverAlias       - Server alias or connection object
     *                                                  rediska           - Rediska instance name, Rediska object or Rediska options for new instance
     * @param string|Rediska_Connection $serverAlias Server alias or Rediska_Connection object where key is placed. Deprecated!
     */
    public function __construct($name, $options = array(), $serverAlias = null)
    {
        if (!is_array($options)) {
            throw new Rediska_Key_Exception("\$expire argument is deprectated. Use 'expire' option");
        }
        if (!is_null($serverAlias)) {
            throw new Rediska_Key_Exception("\$serverAlias argument is deprectated. Use 'serverAlias' option");
        }

        $this->setName($name);

        parent::__construct($options);
    }

    /**
     * Delete key
     *
     * @return boolean
     */
    public function delete()
    {
        return $this->_getRediskaOn()->delete($this->getName());
    }

    /**
     * Exists in db
     * 
     * @return boolean
     */
    public function isExists()
    {
        return $this->_getRediskaOn()->exists($this->getName());
    }

    /**
     * Get key type
     *
     * @see Rediska#getType
     * @return string
     */
    public function getType()
    {
        return $this->_getRediskaOn()->getType($this->getName());
    }

    /**
     * Rename key
     *
     * @param string  $newName
     * @param boolean $overwrite
     * @return boolean
     */
    public function rename($newName, $overwrite = true)
    {
        try {
            $this->_getRediskaOn()->rename($this->getName(), $newName, $overwrite);
        } catch (Rediska_Exception $e) {
            return false;
        }
        
        $this->setName($newName);

        if (!is_null($this->getExpire())) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return true;
    }

    /**
     * Expire key
     *
     * @param integer $secondsOrTimestamp Time in seconds or timestamp
     * @param boolean $isTimestamp        Time is timestamp? Default is false.
     * @return boolean
     */
    public function expire($secondsOrTimestamp, $isTimestamp = false)
    {
        return $this->_getRediskaOn()->expire($this->getName(), $secondsOrTimestamp, $isTimestamp);
    }

    /**
     * Get key lifetime
     *
     * @return integer
     */
    public function getLifetime()
    {
        return $this->_getRediskaOn()->getLifetime($this->getName());
    }

    /**
     * Move key to other Db
     *
     * @see Rediska#moveToDb
     * @param integer $dbIndex
     * @return boolean
     */
    public function moveToDb($dbIndex)
    {
        $result = $this->_getRediskaOn()->moveToDb($this->getName(), $dbIndex);

        if (!is_null($this->getExpire()) && $result) {
            $this->expire($this->getExpire(), $this->isExpireTimestamp());
        }

        return $result;
    }

    /**
     * Get key name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set key name
     * 
     * @param string $name
     * @return Rediska_Key_Abstract
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * Set expire time
     * 
     * @param $secondsOrTimestamp Time in seconds or timestamp
     * @param $isTimestamp        Time is timestamp? Default is false.
     * @return Rediska_Key_Abstract
     */
    public function setExpire($secondsOrTimestamp, $isTimestamp = false)
    {
        if ($secondsOrTimestamp !== null) {
            trigger_error('Expire option is deprecated, because expire behaviour was changed in Redis 2.2. Use expire method instead.', E_USER_WARNING);
        }

        $this->_options['expire'] = $secondsOrTimestamp;
        $this->_options['expireIsTimestamp'] = $isTimestamp;
        
        return $this;
    }

    /**
     * Get expire seconds or timestamp
     * 
     * @return integer
     */
    public function getExpire()
    {
        return $this->_options['expire'];
    }

    /**
     * Is expire is timestamp
     * 
     * @return boolean
     */
    public function isExpireTimestamp()
    {
        return $this->_options['expireIsTimestamp'];
    }

    /**
     * Set server alias
     * 
     * @param $serverAlias
     * @return Rediska_Key_Abstract
     */
    public function setServerAlias($serverAlias)
    {
        $this->_options['serverAlias'] = $serverAlias;

        return $this;
    }

    /**
     * Get server alias
     * 
     * @return null|string
     */
    public function getServerAlias()
    {
        return $this->_options['serverAlias'];
    }

    /**
     *  Get rediska and set specified connection
     *  
     *  @return Rediska
     */
    protected function _getRediskaOn()
    {
        $rediska = $this->getRediska();

        if (!is_null($this->getServerAlias())) {
            $rediska = $rediska->on($this->getServerAlias());
        }

        return $rediska;
    }
}