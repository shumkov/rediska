<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath('../library'),
    get_include_path(),
)));


// STREAM

print 'Benchmark stream: ';

require_once 'Rediska.php';

$startTime = microtime(true);

$rediska = new Rediska(array(
    'servers' => array(array('host' => '127.0.0.1', 'port' => 6379))
));

$value = str_repeat('a', 1000000);

for ($i = 0; $i <= 1000; $i++) {
    $rediska->set('test', $value);
    $rediska->get('test');
    $rediska->delete('test');
}

$elapsedTime = microtime(true) - $startTime;

print sprintf('%.4f', $elapsedTime)  . "\n";


// SOCKET

print 'Benchmark socket: ';

$startTime = microtime(true);

$rediska = new Rediska(array(
    'servers' => array(array('host' => '127.0.0.1', 'port' => 6379, 'useSocket' => true))
));

for ($i = 0; $i <= 1000; $i++) {
    $rediska->set('test', $value);
    $rediska->get('test');
    $rediska->delete('test');
}

$elapsedTime = microtime(true) - $startTime;

print sprintf('%.4f', $elapsedTime)  . "\n";