<?php

include_once('../source/class/class_core.php');
include_once('../source/function/function_core.php');
include_once('../source/discuz_version.php');
substr(DISCUZ_VERSION, 0, 2) != 'X2' && show_msg('此小说模块只适用于 Discuz!X1.5 系列，您的 Discuz! 版本是 '.DISCUZ_VERSION.'，请下载适用于 Discuz!'.DISCUZ_VERSION.' 的小说模块。');

$cachelist = array();
$discuz = & discuz_core::instance();

define('DBCHARSET', 'gbk');
define('ORIG_TABLEPRE', 'pre_');

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = false;
$discuz->init_user = false;
$discuz->init_session = false;
$discuz->init_misc = false;

$discuz->init();

$lockfile = DISCUZ_ROOT.'./data/pdnovelinstall.lock';
if(file_exists($lockfile)) {
	echo '请您先登录服务器ftp，手工删除 ./data/pdnovelinstall.lock 文件，再次运行本文件进行卸载。';
	exit();
}

$ac = $_GET['ac'];

if($ac == 'del'){
	DB::query("DELETE FROM ".DB::table('pdmodule_power')." WHERE moduleid=11;");
	DB::query("DELETE FROM ".DB::table('pdmodule_view')." WHERE moduleid=11;");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_chapter').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_collect').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_comment').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_download').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_collect').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_mark').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_view').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_rate').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_volume').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_star').";");
	DB::query("DROP TABLE IF EXISTS ".DB::table('pdnovel_vote').";");
	DB::query("DELETE FROM ".DB::table('common_syscache')." WHERE cname = 'pdnovelcategory';");
	DB::query("DELETE FROM ".DB::table('common_credit_rule')." WHERE action LIKE 'pdnovel%';");
	DB::query("DELETE FROM ".DB::table('common_nav')." WHERE url LIKE 'pdnovel.php%';");
	
	echo '卸载成功，本次卸载只是删除了数据库中安装时所添加的表，并没有删除文件，如果要彻底卸载，请对应安装文件，删除相应的文件和文件夹';
}else{
	echo '<a href="uninstall.php?ac=del">你确定要卸载[Pdnovel V1.5]剖度小说模块1.0版？</a>';
}

?>
