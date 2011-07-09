<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$navtitle = $lang['cat_index'].' - '.$navtitle;
loadcache( "diytemplatename" );
include template('diy:pdnovel/cat');

?>