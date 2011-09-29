<?php
error_reporting(E_ERROR);
ob_start();
header("HTTP/1.1 301 Moved Permanently");

$url = 'forum.php?';

if(is_numeric($_GET['tid'])) {
	$url .= 'mod=viewthread&tid='.$_GET['tid'];
	/*
	if(is_numeric($_GET['page'])) {
		$url .= '&page='.$_GET['page'];
	}
	*/
	// bluelovers
	foreach (array(
		'tid',
		'mod',
		'sid',
	) as $_k) {
		unset($_GET[$_k]);
	}

	/**
	 * 支援更多舊版網址的參數
	 *
	 * @example http://discuz.bluelovers.net/viewthread.php?action=printable&tid=5229
	 */
	$url .= '&'.http_build_query($_GET);
	// bluelovers
}

header("location: $url");

?>