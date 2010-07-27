<?php

error_reporting(E_ALL);

require_once 'library/Rediska.php';

$rediska = new Rediska(array(
    'servers' => array(
        array('port' => 6380)
    ),
    'redisVersion' => '1.3.12'
));

#$channel = $rediska->subscribe(array('test', 'test2'));

$rediska->publish(array('test', 'test2'), 'hello');