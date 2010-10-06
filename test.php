<?php

require_once 'library/Rediska.php';
$rediska = new Rediska(array('profiler' => array('enable' => true)));
$rediska->set('a', 'b');
$rediska->get('a');

foreach(array('a', 'b', 'c') as $value) {
    $rediska->appendToList('test', $value);
}
$rediska->delete(array('a', 'test'));

foreach($rediska->getProfiler() as $profile) {
    print $profile->getLabel() . " => " . $profile->getElapsedTime(4) . "\n";
}

print $rediska->getProfiler()->getTotalElapsedTime(4);