<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_index.php 528 2010-08-30 06:11:37Z yexinhao $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(@file_exists(DISCUZ_ROOT.'./install/index.php') && !DISCUZ_DEBUG) {
	@unlink(DISCUZ_ROOT.'./install/index.php');
	if(@file_exists(DISCUZ_ROOT.'./install/index.php')) {
		dexit('Please delete install/index.php via FTP!');
	}
}

cpheader();

@include_once DISCUZ_ROOT.'./source/discuzxplus_version.php';
$isfounder = isfounder();

$siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
if(empty($siteuniqueid) || strlen($siteuniqueid) < 16 || strpos($siteuniqueid, 'XPLUS') !== 0) { //debug note
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$siteuniqueid = 'XPLUS'.$chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_G['clientip'].$_G['username'].TIMESTAMP), 0, 4).random(4);
	$temp = array(
		'skey' => 'siteuniqueid',
		'svalue' => $siteuniqueid
	);
	DB::insert('common_setting', $temp, false, true);
}

$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
$serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
$serversoft = $_SERVER['SERVER_SOFTWARE'];
$dbversion = DB::result_first("SELECT VERSION()");

if(@ini_get('file_uploads')) {
	$fileupload = ini_get('upload_max_filesize');
} else {
	$fileupload = '<font color="red">'.$lang['no'].'</font>';
}

$dbsize = 0;
$query = DB::query("SHOW TABLE STATUS LIKE '{$_G['config']['db'][1]['tablepre']}%'", 'SILENT');
while($table = DB::fetch($query)) {
	$dbsize += $table['Data_length'] + $table['Index_length'];
}
$dbsize = $dbsize ? sizecount($dbsize) : $lang['unknown'];

shownav();

showsubmenu('home_welcome', array(), '', array('bbname' => $_G['setting']['bbname']));

$onlines = '';
$query = DB::query("SELECT cps.uid,cps.dateline,m.username FROM ".DB::table('common_admincp_session')." cps
	LEFT JOIN ".DB::table('common_member')." m ON m.uid=cps.uid
	ORDER BY dateline DESC");
while($online = DB::fetch($query)) {
	$onlines .= '<a href="home.php?mod=space&uid='.$online['uid'].'" title="'.dgmdate($online['dateline']).'">'.$online['username'].'</a>&nbsp;&nbsp;&nbsp;';
}

echo '<div id="boardnews"></div>';

//showsubmenu('home_security_tips');
//echo '<ul class="safelist">'.$securityadvise.'</ul>';

//note 服務器環境及程序版權作者等
loaducenter();

showtableheader('home_sys_info', 'fixpadding');
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_discuz_version'),
	'Discuz! XPlus'.XPLUS_VERSION.' Release '.XPLUS_RELEASE.' <a href="http://www.comsenz.com/purchase/discuz/" class="lightlink2 smallfont" target="_blank">專業支持與服務</a> <a href="http://idc.comsenz.com" class="lightlink2 smallfont" target="_blank">&#68;&#105;&#115;&#99;&#117;&#122;&#33;專用主機</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_ucclient_version'),
	'UCenter '.UC_CLIENT_VERSION.' Release '.UC_CLIENT_RELEASE
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_environment'),
	$serverinfo
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_serversoftware'),
	$serversoft
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_database'),
	$dbversion
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_upload_perm'),
	$fileupload
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont"'), array(
	cplang('home_database_size'),
	$dbsize
));
showtablefooter();

showtableheader('home_dev', 'fixpadding');
showtablerow('', array('class="vtop td24 lineheight"'), array(
	cplang('home_dev_copyright'),
	'<span class="bold"><a href="http://www.comsenz.com" class="lightlink2" target="_blank">康盛創想(北京)科技有限公司 (Comsenz Inc.)</a></span>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight smallfont team"'), array(
	cplang('home_dev_manager'),
	'<a href="http://www.discuz.net/home.php?mod=space&uid=1" class="lightlink2 smallfont" target="_blank">戴志康 (Kevin \'Crossday\' Day)</a>'
));
showtablerow('', array('class="vtop td24 lineheight"', 'class="lineheight"'), array(
	cplang('home_dev_links'),
	'<a href="http://www.comsenz.com" class="lightlink2" target="_blank">公司網站</a>,
		<a href="http://idc.comsenz.com" class="lightlink2" target="_blank">虛擬主機</a>,
		<a href="http://www.comsenz.com/category-51" class="lightlink2" target="_blank">購買授權</a>,
		<a href="http://www.discuz.com/" class="lightlink2" target="_blank">&#x44;&#x69;&#x73;&#x63;&#x75;&#x7A;&#x21;&#x20;產品</a>,
		<a href="http://www.comsenz.com/downloads/styles/discuz" class="lightlink2" target="_blank">模板</a>,
		<a href="http://www.comsenz.com/downloads/plugins/discuz" class="lightlink2" target="_blank">插件</a>,
		<a href="http://faq.comsenz.com" class="lightlink2" target="_blank">文檔</a>,
		<a href="http://www.discuz.net/" class="lightlink2" target="_blank">討論區</a>'
));
showtablefooter();

echo '</div>';

?>