<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('SKYGAGA_PLUGIN_ID', 'riaue_skygaga');
define('SKYGAGA_WWW', 'http://wwww.skygaga.com/');
$version = $_G['setting']['plugins']['version'][SKYGAGA_PLUGIN_ID];
$SKYGAGA_PLUGIN_ID = SKYGAGA_PLUGIN_ID;
$SKYGAGA_WWW = SKYGAGA_WWW;
$site = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
$site = urlencode($site);
$title = $_G['cache']['plugin'][SKYGAGA_PLUGIN_ID]['skygaga_title'];
$title = $title ? $title : '微博天空';
$lang = $_G['cache']['plugin'][SKYGAGA_PLUGIN_ID]['skygaga_lang'];
$lang = $lang ? $lang : 'zh-cn';

include template(SKYGAGA_PLUGIN_ID.':main');