<?php

/**
 * Rediska unix socket connection
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Connection
 * @version 0.5.10
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Connection_UnixSocket extends Rediska_Connection
{
    /**
     * @return string
     */
    public function getSocketAddress()
    {
        return 'unix://' . $this->getHost();
    }
}
