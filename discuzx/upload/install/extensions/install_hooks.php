<?php

if (!in_array($_SERVER['REMOTE_ADDR'], array(
	'122.116.39.240',
	'192.168.0.25',
	'192.168.1.25',
	'192.168.0.15',
))) {
	@header("HTTP/1.1 403 Forbidden");
	exit("HTTP/1.1 403 Forbidden");
}

?>