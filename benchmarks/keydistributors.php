<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath('../library'),
    get_include_path(),
)));


// CRC32

print 'Benchmark crc32: ';

require_once 'Rediska/KeyDistributor/Crc32.php';

$startTime = microtime(true);

$consistentHashing = new Rediska_KeyDistributor_Crc32();
$consistentHashing->addConnection('127.0.0.1:6379');
$consistentHashing->addConnection('127.0.0.1:6380');
$consistentHashing->addConnection('127.0.0.1:6381');

for ($i = 0; $i <= 1000; $i++) {
    $consistentHashing->getConnectionByKeyName('key_' . $i);
}

$elapsedTime = microtime(true) - $startTime;

print sprintf('%.4f', $elapsedTime)  . "\n";


// CONSISTENT HASHING

print 'Benchmark consistent hashing: ';

require_once 'Rediska/KeyDistributor/ConsistentHashing.php';

$startTime = microtime(true);

$consistentHashing = new Rediska_KeyDistributor_ConsistentHashing();
$consistentHashing->addConnection('127.0.0.1:6379');
$consistentHashing->addConnection('127.0.0.1:6380');
$consistentHashing->addConnection('127.0.0.1:6381');

for ($i = 0; $i <= 1000; $i++) {
    $consistentHashing->getConnectionByKeyName('key_' . $i);
}

$elapsedTime = microtime(true) - $startTime;

print sprintf('%.4f', $elapsedTime)  . "\n";