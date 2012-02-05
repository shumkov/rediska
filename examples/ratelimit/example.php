<?php
require_once dirname(__FILE__) . '/../../library/Rediska.php';
require_once dirname(__FILE__) . '/ratelimit.php';

$rediska = new Rediska();
$rateLimit = new Ratelimit($rediska, 'someAction');
$count = $rateLimit->incrementAndGetCountByInterval('ipUserNameOrCookie2', 10);
echo "Rate count - {$count}";
if (60 < $count) {
	//do something e.g. throw exception, show captcha, etc ...
	throw new Exception("Rate limit was reached");
}