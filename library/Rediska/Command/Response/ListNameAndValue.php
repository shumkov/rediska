<?php

/**
 * Rediska command list name and value response
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Response_ListNameAndValue extends ArrayObject
{
    public function __set($name, $value)
    {
        $this[$name] = $value;
    }

    public function __get($name)
    {
        return $this[$name];
    }

    public static function factory(Rediska $rediska, $response)
    {
        if (!empty($response)) {
            $name = $response[0];
            if ($rediska->getOption('namespace') != '' && strpos($name, $rediska->getOption('namespace')) === 0) {
                $name = substr($name, strlen($rediska->getOption('namespace')));
            }
    
            $value = $rediska->getSerializer()->unserialize($response[1]);
    
            return new self(array('name' => $name, 'value' => $value));
        } else {
            return null;
        }
    }
}