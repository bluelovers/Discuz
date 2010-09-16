<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: userapp_app.php 16448 2010-09-07 02:45:57Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($appid == '1036584') {
} else {
	require_once libfile('function/spacecp');
	ckrealname('userapp');

	ckvideophoto('userapp');

	if(!checkperm('allowmyop')) {
		showmessage('no_privilege', '', array(), array('return' => true));
	}
}

$app = array();
$query = DB::query("SELECT * FROM ".DB::table('common_myapp')." WHERE appid='$appid' LIMIT 1");
if($app = DB::fetch($query)) {
	if($app['flag']<0) {
		showmessage('no_privilege_myapp');
	}
	$today = strtotime(dgmdate($_G['timestamp'], 'Y-m-d'));
	if($appid != '1036584' && !getcount('home_userapp_stat', array('uid' => $_G['uid'], 'appid' => $appid, 'dateline'=>$today))) {
		DB::query("UPDATE ".DB::table('common_myapp_count')." SET todayuse='0', usedate='$today' WHERE appid='$appid' AND  usedate != '$today'");

		DB::insert('home_userapp_stat', array('uid' => $_G['uid'], 'appid' => $appid, 'dateline'=>$today));
		space_merge($space, 'profile');
		$extsql = '';
		if($space['gender']) {
			$extsql = $space['gender'] == 1 ? ', boyuse=boyuse+1' : ', girluse=girluse+1';
		}
		DB::query("UPDATE ".DB::table('common_myapp_count')." SET usetotal=usetotal+1, todayuse=todayuse+1 $extsql WHERE appid='$appid'");
	}
}

$canvasTitle = '';
$isFullscreen = 0;
$displayUserPanel = 0;
if($app['canvastitle']) {
	$canvasTitle =$app['canvastitle'];
}
if($app['fullscreen']) {
	$isFullscreen = $app['fullscreen'];
}
if($app['displayuserpanel']) {
	$displayUserPanel = $app['displayuserpanel'];
}

$my_appId = $appid;
$my_suffix = base64_decode(urldecode($_GET['my_suffix']));

$my_prefix = getsiteurl();

updatecreditbyaction('useapp', 0, array(), $appid);

if (!$my_suffix) {
	header('Location: userapp.php?mod=app&id='.$my_appId.'&my_suffix='.urlencode(base64_encode('/')));
	exit;
}

if (preg_match('/^\//', $my_suffix)) {
	$url = 'http://apps.manyou.com/'.$my_appId.$my_suffix;
} else {
	if ($my_suffix) {
		$url = 'http://apps.manyou.com/'.$my_appId.'/'.$my_suffix;
	} else {
		$url = 'http://apps.manyou.com/'.$my_appId;
	}
}
if (strpos($my_suffix, '?')) {
	$url = $url.'&my_uchId='.$_G['uid'].'&my_sId='.$_G['setting']['my_siteid'];
} else {
	$url = $url.'?my_uchId='.$_G['uid'].'&my_sId='.$_G['setting']['my_siteid'];
}
$url .= '&my_prefix='.urlencode($my_prefix).'&my_suffix='.urlencode($my_suffix);
$current_url = getsiteurl().'userapp.php';
if ($_SERVER['QUERY_STRING']) {
	$current_url = $current_url.'?'.$_SERVER['QUERY_STRING'];
}
$extra = $_GET['my_extra'];
$timestamp = $_G['timestamp'];
$url .= '&my_current='.urlencode($current_url);
$url .= '&my_extra='.urlencode($extra);
$url .= '&my_ts='.$timestamp;
$url .= '&my_appVersion='.$app['version'];
$url .= '&my_fullscreen='.$isFullscreen;
$hash = $_G['setting']['my_siteid'].'|'.$_G['uid'].'|'.$appid.'|'.$current_url.'|'.$extra.'|'.$timestamp.'|'.$_G['setting']['my_sitekey'];
$hash = md5($hash);
$url .= '&my_sig='.$hash;
$my_suffix = urlencode($my_suffix);

$canvasTitle = '';
$isFullscreen = 0;
$displayUserPanel = 0;
if ($app['canvastitle']) {
	$canvasTitle =$app['canvastitle'];
}
if ($app['fullscreen']) {
	$isFullscreen = $app['fullscreen'];
}
if ($app['displayuserpanel']) {
	$displayUserPanel = $app['displayuserpanel'];
}

$navtitle = $app['appname'].' - '.$navtitle;

include_once template("userapp/userapp_app");
?>