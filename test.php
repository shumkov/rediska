<?php

error_reporting(E_ALL);

require_once 'library/Rediska.php';

$rediska = new Rediska(array(
    'servers' => array(
        array('port' => 6380),
        array('port' => 6381)
    ),
    'redisVersion' => '1.3.12'
));

$test = $rediska->subscribe('test', 10);
$test2 = $rediska->subscribe('test2', 10);

while (true) {
    foreach($test as $channel => $message) {
         print "$channel: $message\n";
         $test->unsubscribe($channel);
    }
    print "done\n";
}