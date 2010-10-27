<?php

require_once 'library/Rediska.php';
$rediska = new Rediska(array('profiler' => true));
$rediska->set('a', 'b');
$rediska->get('a');

foreach(array('a', 'b', 'c') as $value) {
    $rediska->appendToList('test', $value);
}

$rediska->set(array('c' => 123));

$rediska->delete(array('a', 'test', 'c'));

foreach($rediska->getProfiler() as $profile) {
    print $profile . "\n";
}

print $rediska->getProfiler() . "\n";