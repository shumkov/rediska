<?php

/**
 * Return a subset of the string from offset start to offset end (both offsets are inclusive)
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetRange extends Rediska_Command_Abstract
{
    protected $_version = '1.3.4';

    /**
     * Create command
     *
     * @param string            $key   Key name
     * @param integer           $start Start
     * @param integer[optional] $end   End. If end is omitted, the substring starting from $start until the end of the string will be returned. For default end of string
     * @return Rediska_Connection_Exec
     */
    public function create($key, $start, $end = -1)
    {
        if ($this->getName() == 'substring') {
            trigger_error('Substring is deprecated. Use getRange command instead', E_USER_WARNING);
        }

        $redisVersion = $this->getRediska()->getOption('redisVersion');
        $isVersionLessThen2 = version_compare('2.0.4', $redisVersion) >= 0;

        $command = array($isVersionLessThen2 ? 'SUBSTR' : 'GETRANGE',
                         $this->_rediska->getOption('namespace') . $key,
                         $start,
                         $end);

        $connection = $this->_rediska->getConnectionByKeyName($key);

        return new Rediska_Connection_Exec($connection, $command);
    }

    /**
     * Parse response
     *
     * @param string $response
     * @return mixin
     */
    public function parseResponse($response)
    {
        return $this->getRediska()->getSerializer()->unserialize($response);
    }
}