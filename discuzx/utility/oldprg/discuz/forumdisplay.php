<?php
error_reporting(E_ERROR);
ob_start();
header("HTTP/1.1 301 Moved Permanently");

$url = 'forum.php?';

if(is_numeric($_GET['fid'])) {
	$url .= 'mod=forumdisplay&fid='.$_GET['fid'];
	/*
	if(is_numeric($_GET['page'])) {
		$url .= '&page='.$_GET['page'];
	}
	*/
	// bluelovers
	foreach (array(
		'fid',
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
}

header("location: $url");

?>