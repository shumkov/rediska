<?php

/**
 * Set a time to live in seconds or timestamp on a key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Expire extends Rediska_Command_Abstract
{
    /**
     * Create command
     *
     * @param string  $key                   Key name
     * @param integer $secondsOrTimestamp    Time in seconds or timestamp
     * @param boolean $isTimestamp[optional] Time is timestamp. For default is false.
     * @return Rediska_Connection_Exec
     */
    public function create($key, $secondsOrTimestamp, $isTimestamp = false)
    {
        $connection = $this->_rediska->getConnectionByKeyName($key);

        if ($isTimestamp) {
            $this->_throwExceptionIfNotSupported('1.1');
            $command = 'EXPIREAT';
        } else {
            $command = 'EXPIRE';
        }

        $command = array($command,
                         $this->_rediska->getOption('namespace') . $key,
                         $secondsOrTimestamp);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param integer $response
     * @return boolean
     */
    public function parseResponse($response)
    {
        return (boolean)$response;
    }
}