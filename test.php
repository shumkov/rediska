<?php

require_once 'library/Rediska.php';

$rediska = new Rediska(array('servers' => array(array('port' => 6380)), 'redisVersion' => '1.3.12'));

foreach($rediska->subscribe('test', 5) as $channel => $message) {
    print $message . "\n";
}