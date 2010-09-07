<?php

/**
 * Rediska command interface
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Commands
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
interface Rediska_Command_Interface
{
    public function __construct(Rediska $rediska, $name, $arguments = array());
    public function write();
    public function read();
    public function execute();
    public function isAtomic();
    public function isQueued();
}