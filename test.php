<?php

$rediska = new Rediska(array(
	'servers' => array(
		array('host' => '127.0.0.1', 'port' => 6380),
		array('host' => '127.0.0.1', 'port' => 6381),
	)
));

$connectionOne = $rediska->getConnectionByKeyName('set-on-server1');
$connectionTwo = $rediska->getConnectionByKeyName('set-on-server2');
if ($connectionOne === $connectionTwo) {
	die('Keys on the same server. We need different for test.');
}

$rediska->addToSet('set-on-server1', 1);
$rediska->addToSet('set-on-server1', 2);

$rediska->addToSet('set-on-server2', 1);
$rediska->addToSet('set-on-server2', 3);

$diff = $rediska->diffSets(array('set-on-server1', 'set-on-server2'));

print_r($diff);

$rediska->delete(array('set-on-server1', 'set-on-server2'));

?>