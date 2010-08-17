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
        'serveralias'       => null,
        'expire'            => null,
        'expireistimestamp' => false,
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

        $this->_options['name'] = $newName;

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
        return $this->_options['name'];
    }

    /**
     * Set key name
     * 
     * @param string $name
     * @return Rediska_Key_Abstract
     */
    public function setName($name)
    {
        $this->_options['name'] = $name;

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
     * Set Rediska instance
     * 
     * @param Rediska $rediska Rediska instance or name
     * @return Rediska_Key_Abstract
     */
    public function setRediska($rediska)
    {
        if (is_object($rediska) && !$rediska instanceof Rediska) {
            throw new Rediska_Key_Exception('$rediska must be Rediska instance name, Rediska object or array of options');
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
        if (!is_object($this->_rediska)) {
            if ($this->_rediska == Rediska::DEFAULT_NAME) {
                if (Rediska_Manager::has($this->_rediska)) {
                    throw new Rediska_Key_Exception("You must instance '" . Rediska::DEFAULT_NAME . "' Rediska before or use 'rediska' option for specify instance");
                } else {
                    $this->_rediska = Rediska_Manager::get($this->_rediska);
                }
            } else if (is_array($this->_rediska)) {
                $this->_rediska = new Rediska($this->_rediska);
            } else {
                $this->_rediska = Rediska_Manager::get($this->_rediska);
            }
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

        if (!is_null($this->_options['serverAlias'])) {
            $rediska = $rediska->on($this->_options['serverAlias']);
        }

        return $rediska;
    }
}