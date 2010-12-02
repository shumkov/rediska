<?php

/**
 * Rediska null profiler
 *
 * @author Ivan Shumkov
 * @package Rediska
 * @subpackage Profiler
 * @version @package_version@
 * @link http://rediska.geometria-lab.net
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Profiler_Null implements Rediska_Profiler_Interface
{
    /**
     * Start profile
     *
     * @param mixed $context
     */
    public function start($context) {}

    /**
     * Stop profile
     */
    public function stop() {}
}