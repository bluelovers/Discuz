<?php
error_reporting(E_ERROR);
ob_start();
header("HTTP/1.1 301 Moved Permanently");

$url = 'forum.php?';

if(is_numeric($_GET['tid'])) {
	$url .= 'mod=rss';

	$url .= '&'.http_build_query($_GET);
}

header("location: $url");

?>