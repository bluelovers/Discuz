<?php
error_reporting(E_ERROR);
ob_start();
header("HTTP/1.1 301 Moved Permanently");

$url = 'forum.php?';

/*
if(is_numeric($_GET['aid'])) {
	$url .= 'mod=attachment&aid='.$_GET['aid'];
}
*/
// bluelovers
$url .= 'mod=attachment';

foreach (array(
	'mod',
	'sid',
	'topicsubmit',
	'postsubmit',
	'formhash',
) as $_k) {
	unset($_GET[$_k]);
}

/**
 * 支援更多舊版網址的參數
 */
$url .= '&'.http_build_query($_GET);
// bluelovers

header("location: $url");

?>