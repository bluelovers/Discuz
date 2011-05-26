<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_rss.php 20828 2011-03-04 09:51:43Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pagenum = 20;

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
} else {
	$space['username'] = $space['username'].'@'.$_G['setting']['sitename'];
	$space['space_url'] = $siteurl."home.php?mod=space&amp;uid=$space[uid]";
}

$uidsql = empty($space['uid'])?'':" AND b.uid='$space[uid]'";
$query = DB::query("SELECT bf.message, b.*
	FROM ".DB::table('home_blog')." b
	LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
	WHERE b.friend='0' $uidsql
	ORDER BY dateline DESC
	LIMIT 0,$pagenum");


$charset = $_G['config']['output']['charset'];
dheader("Content-type: application/xml");
echo 	"<?xml version=\"1.0\" encoding=\"".$charset."\"?>\n".
	"<rss version=\"2.0\">\n".
	"  <channel>\n".
	"    <title>{$space[username]}</title>\n".
	"    <link>{$space[space_url]}</link>\n".
	"    <description>{$_G[setting][bbname]}</description>\n".
	"    <copyright>Copyright(C) {$_G[setting][bbname]}</copyright>\n".
	"    <generator>Discuz! Board by Comsenz Inc.</generator>\n".
	"    <lastBuildDate>".gmdate('r', TIMESTAMP)."</lastBuildDate>\n".
	"    <image>\n".
	"      <url>{$_G[siteurl]}static/image/common/logo_88_31.gif</url>\n".
	"      <title>{$_G[setting][bbname]}</title>\n".
	"      <link>{$_G[siteurl]}</link>\n".
	"    </image>\n";

while ($value = DB::fetch($query)) {
	$value['message'] = getstr($value['message'], 300, 0, 0, 0, -1);
	if($value['pic']) {
		$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
		$value['message'] .= "<br /><img src=\"$value[pic]\">";
	}
	echo 	"    <item>\n".
			"      <title>".$value['subject']."</title>\n".
			"      <link>$_G[siteurl]home.php?mod=space&amp;uid=$value[uid]&amp;do=blog&amp;id=$value[blogid]</link>\n".
			"      <description><![CDATA[".dhtmlspecialchars($value['message'])."]]></description>\n".
			"      <author>".dhtmlspecialchars($value['username'])."</author>\n".
			"      <pubDate>".gmdate('r', $value['dateline'])."</pubDate>\n".
			"    </item>\n";
}

echo 	"  </channel>\n".
	"</rss>";
?>