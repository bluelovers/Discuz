<?php

error_reporting(E_ERROR);
ob_start();
header("HTTP/1.1 301 Moved Permanently");

$url = 'misc.php?mod=tag';

// 避免被覆寫 mod
unset($_GET['mod']);

if (!empty($_GET)) {
	// 直接取用 $_GET
	$url .= '&'.http_build_query((array)$_GET);
}

header("location: $url");

?>