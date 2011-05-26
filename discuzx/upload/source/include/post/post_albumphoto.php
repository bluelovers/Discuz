<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id:$
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('function/home');
$perpage = 8;
$page = max(1, $_G['gp_page']);
$start_limit = ($page - 1) * $perpage;
$aid = intval($_G['gp_aid']);
$photolist = array();
$count = DB::result_first("SELECT picnum FROM " . DB::table('home_album') . " WHERE albumid='$aid' AND uid='$_G[uid]'");
$query = DB::query("SELECT * FROM " . DB::table('home_pic') . " WHERE albumid='$aid' ORDER BY dateline DESC LIMIT $start_limit,$perpage");
while ($value = DB::fetch($query)) {
	$value['bigpic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
	$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
	$value['count'] = $count;
	$value['url'] = (substr(strtolower($value['bigpic']), 0, 7) == 'http://' ? '' : $_G['siteurl']) . $value['bigpic'];
	$value['thumburl'] = (substr(strtolower($value['pic']), 0, 7) == 'http://' ? '' : $_G['siteurl']) . $value['pic'];
	$photolist[] = $value;
}
$_G['gp_ajaxtarget'] = 'albumphoto';
$multi = multi($count, $perpage, $page, "forum.php?mod=post&action=albumphoto&aid=$aid");
include template('forum/ajax_albumlist');
exit;