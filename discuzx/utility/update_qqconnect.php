<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update_qqconnect.php 22781 2011-05-20 08:51:27Z monkey $
 */

include_once('../source/class/class_core.php');
include_once('../source/function/function_core.php');

@set_time_limit(0);

$cachelist = array();
$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = false;
$discuz->init_user = false;
$discuz->init_session = false;
$discuz->init_misc = false;

$discuz->init();

$theurl = 'update_qqconnect.php';

$limit = 1000;
$start = !empty($_GET['start']) ? $_GET['start'] : 0;

$query = DB::query("SELECT COUNT(*) FROM ".DB::table('common_member_connect'), 'SILENT');
$newcount = $query ? DB::result($query, 0) : 0;
$query = DB::query("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE conuin<>''", 'SILENT');
$count = $query ? DB::result($query, 0) : 0;

if($newcount < $count) {
	$query = DB::query("SELECT * FROM ".DB::table('common_member')." WHERE conuin<>'' ORDER BY uid LIMIT $start, $limit");
	while($row = DB::fetch($query)) {
		$row = daddslashes($row);
		$data = array(
			'uid' => $row['uid'],
			'conuin' => $row['conuin'],
			'conispublishfeed' => $row['conispublishfeed'],
			'conispublisht' => $row['conispublisht'],
			'conisregister' => $row['conisregister']
		);
		DB::insert('common_member_connect', $data, false, true);
	}
	$start += $limit;
	show_msg("QQ 互聯用戶數據升級 ... $start/$count", "$theurl?start=$start");
} else {
	show_msg("QQ 互聯用戶數據升級完畢");
}

function show_msg($message, $url_forward='') {

	if($url_forward) {
		$message = "<a href=\"$url_forward\">$message (跳轉中...)</a><script>setTimeout(\"window.location.href ='$url_forward';\", 1);</script>";
	}

	show_header();
	print<<<END
	<table>
	<tr><td>$message</td></tr>
	</table>
END;
	show_footer();
	exit();
}


function show_header() {
	global $config;

	$nowarr = array($_GET['step'] => ' class="current"');

	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
	<title> QQ 互聯數據庫升級工具 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>QQ 互聯數據庫升級工具</h1>
	<div style="width:90%;margin:0 auto;">
	<br>
END;
}

function show_footer() {
	print<<<END
	</div>
	<div id="footer">&copy; Comsenz Inc. 2001-2011 http://www.comsenz.com</div>
	</div>
	<br>
	</body>
	</html>
END;
}

?>