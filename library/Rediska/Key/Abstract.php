<?php

/**
 * Rediska key abstract class
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
abstract class Rediska_Key_Abstract extends Rediska_Options
{
    /**
     * Rediska instance
     *
     * @var string|Rediska
     */
    protected $_rediska = Rediska::DEFAULT_NAME;

    /**
     * Options
     *
     * @var array
     */
    protected $_options = array(
        'name'              => null,
        'serverAlias'       => null,
        'expire'            => null,
        'expireIsTimestamp' => false,
    );

    /**
     * Construct key
     *
     * @param string                    $nameOrOptions  Key name or options
     * @param integer                   $expire         Expire time in seconds. Deprecated!
     * @param string|Rediska_Connection $serverAlias    Server alias or Rediska_Connection object where key is placed. Deprecated!
     */
    public function __construct($nameOrOptions, $expire = null, $serverAlias = null)
    {
        if (!is_null($expire)) {
            throw new Rediska_Key_Exception("\$expire argument is deprectated. Use first argument as array with 'name' and 'expire' option");
        }
        if (!is_null($serverAlias)) {
            throw new Rediska_Key_Exception("\$serverAlias argument is deprectated. Use first argument as array with 'name' and 'serverAlias' option");
        }

        if (is_string($nameOrOptions)) {
            $options = array('name' => $nameOrOptions);
        } else if (is_array($nameOrOptions)) {
            $options = $nameOrOptions;
        } else {
            throw new Rediska_Key_Exception('$nameOrOptions must be options array or key name');
        }

        parent::__construct($options);
    }

    /**
     * Delete key
     *
     * @return boolean
     */
    public function delete()
    {
        return $this->_getRediskaOn()->delete($this->_name);
    }

    /**
     * Exists in db
     * 
     * @return boolean
     */
    public function isExists()
    {
        return $this->_getRediskaOn()->exists($this->_name);
    }

    /**
     * Get key type
     *
     * @see Rediska#getType
     * @return string
     */
    public function getType()
    {
        return $this->_getRediskaOn()->getType($this->_name);
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
            $this->_getRediskaOn()->rename($this->_name, $newName, $overwrite);
        } catch (Rediska_Exception $e) {
            return false;
        }

        $this->_name = $newName;

        if (!is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
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
        return $this->_getRediskaOn()->expire($this->_name, $secondsOrTimestamp, $isTimestamp);
    }

    /**
     * Get key lifetime
     *
     * @return integer
     */
    public function getLifetime()
    {
        return $this->_getRediskaOn()->getLifetime($this->_name);
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
        $result = $this->_getRediskaOn()->moveToDb($this->_name, $dbIndex);

        if (!is_null($this->_expire) && $result) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
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
        $this->_expire = $secondsOrTimestamp;
        $this->_isExpireTimestamp = $isTimestamp;
        
        return $this;
    }

    /**
     * Get expire seconds or timestamp
     * 
     * @return integer
     */
    public function getExpire()
    {
        return $this->_expire;
    }

    /**
     * Is expire is timestamp
     * 
     * @return boolean
     */
    public function isExpireTimestamp()
    {
        return $this->_isExpireTimestamp;
    }

    /**
     * Set server alias
     * 
     * @param $serverAlias
     * @return Rediska_Key_Abstract
     */
    public function setServerAlias($serverAlias)
    {
        $this->_serverAlias = $serverAlias;

        return $this;
    }

    /**
     * Get server alias
     * 
     * @return null|string
     */
    public function getServerAlias()
    {
        return $this->_serverAlias;
    }
    
    /**
     * Set Rediska instance
     * 
     * @param Rediska $rediska Rediska instance or name
     * @return Rediska_Key_Abstract
     */
    public function setRediska($rediska)
    {
        if (is_object($rediska) && !$rediska instanceof Rediska) {
            throw new Rediska_Key_Exception('$rediska must be Rediska instance or name');
        }

        $this->_rediska = $rediska;

        return $this;
    }

    /**
     * Get Rediska instance
     *
     * @throws Rediska_Exception
     * @return Rediska
     */
    public function getRediska()
    {
        if (is_string($this->_rediska)) {
            try {
                $rediska = Rediska_Manager::get($this->_rediska);
            } catch(Rediska_Exception $e) {
                if ($this->_rediska == Rediska::DEFAULT_NAME) {
                    $rediska = new Rediska();
                } else {
                    throw $e;
                }
            }

            $this->_rediska = $rediska;
        }

        return $this->_rediska;
    }

    /**
     *  Get rediska and set specified connection
     *  
     *  @return Rediska
     */
    protected function _getRediskaOn()
    {
        $rediska = $this->getRediska();

        if (!is_null($this->_serverAlias)) {
            $rediska = $rediska->on($this->_serverAlias);
        }

        return $rediska;
    }

    /**
     * Setup Rediska instance
     */
    protected function _setupRediskaDefaultInstance()
    {
        $this->_rediska = Rediska::getDefaultInstance();
        if (!$this->_rediska) {
            
        }
    }
}