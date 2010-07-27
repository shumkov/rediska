<?php

error_reporting(E_ALL);

require_once 'library/Rediska.php';

$rediska = new Rediska(array(
    'servers' => array(
        array('port' => 6380)
    ),
    'redisVersion' => '1.3.12'
));

$channel1 = $rediska->subscribe('test', 10);
$channel2 = $rediska->subscribe('test2', 10);

while(true) {
    foreach($channel1 as $message) {
         print "channel1: $message\n";
    }
    
    foreach($channel2 as $message) {
        print "channel2: $message\n";
    }
}