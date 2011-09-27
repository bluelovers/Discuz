<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$url = substr($_SERVER['QUERY_STRING'],4);
$url = !empty($url) ? $url: $_G['siteurl'];
loadcache('plugin');
$navtitle = $_G['cache']['plugin']['study_linkkiller']['study_title'];
include template('study_linkkiller:link');

?>