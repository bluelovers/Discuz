<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_rss.php 12686 2010-07-13 06:46:51Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

@header("Content-type: application/xml");

$pagenum = 10;
$tag = '<?';
$rssdateformat = 'D, d M Y H:i:s T';

$siteurl = getsiteurl();
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$list = array();

if(!empty($uid)) {
	$space = getspace($uid);
}
if(empty($space)) {
	$space['username'] = $_G['setting']['sitename'];
	$space['name'] = $_G['setting']['sitename'];
	$space['email'] = $_G['setting']['adminemail'];
	$space['space_url'] = $siteurl;
	$space['lastupdate'] = dgmdate($rssdateformat);
	$space['privacy']['blog'] = 1;
} else {
	$space['username'] = $space['username'].'@'.$_G['setting']['sitename'];
	$space['space_url'] = $siteurl."home.php?mod=space&uid=$space[uid]";
	$space['lastupdate'] = dgmdate($rssdateformat, $space['lastupdate']);
}

$uidsql = empty($space['uid'])?'':" AND b.uid='$space[uid]'";
$query = DB::query("SELECT bf.message, b.*
	FROM ".DB::table('home_blog')." b
	LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
	WHERE b.friend='0' $uidsql
	ORDER BY dateline DESC
	LIMIT 0,$pagenum");
while ($value = DB::fetch($query)) {
	if(!empty($space['privacy']['blog'])) {
		$value['message'] = '';
	} else {
		$value['message'] = getstr($value['message'], 300, 0, 0, 0, -1);
		if($value['pic']) {
			$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
			$value['message'] .= "<br /><img src=\"$value[pic]\">";
		}
	}


	$value['dateline'] = dgmdate($rssdateformat, $value['dateline']);
	$list[] = $value;
}



include template('home/space_rss');

?>