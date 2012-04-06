<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_share.php 28048 2012-02-21 09:29:40Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$_GET['type'] = in_array($_GET['type'], array('all', 'link', 'video', 'music', 'flash', 'blog', 'album', 'pic', 'poll', 'space', 'thread', 'article'))? $_GET['type'] : 'all';
if($id) {

	if(!IS_ROBOT) {
		$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE sid='$id' AND uid='$space[uid]'");
		$share = DB::fetch($query);
		if(empty($share)) {
			showmessage('share_does_not_exist');
		}

		require_once libfile('function/share');
		$share = mkshare($share);

		$perpage = 50;
		$start = ($page-1)*$perpage;

		ckstart($start, $perpage);

		$list = array();
		$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
		$csql = $cid?"cid='$cid' AND":'';

		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='sid'"),0);
		if($count) {
			$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='sid' ORDER BY dateline LIMIT $start,$perpage");
			while ($value = DB::fetch($query)) {
				$list[] = $value;
			}
			$multi = multi($count, $perpage, $page, "home.php?mod=space&uid=$share[uid]&do=share&id=$id", '', 'comment_ul');
		}
		$diymode = intval($_G['cookie']['home_diymode']);
	}
	include_once template("diy:home/space_share_view");

} else {

	$perpage = 20;

	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	$gets = array(
		'mod' => 'space',
		'uid' => $space['uid'],
		'do' => 'share',
		'view' => $_GET['view'],
		'from' => $_GET['from']
	);
	$navtheurl = $theurl = 'home.php?'.url_implode($gets);
	$theurl .= '&type='.$_GET['type'];
	if(!IS_ROBOT) {
		$f_index = '';
		$need_count = true;

		if(empty($_GET['view'])) $_GET['view'] = 'we';

		if($_GET['view'] == 'all') {
			$wheresql = "1";

		} elseif($_GET['view'] == 'we') {

			space_merge($space, 'field_home');

			if($space['feedfriend']) {
				$wheresql = "uid IN ($space[feedfriend])";
				$f_index = 'USE INDEX(dateline)';
			} else {
				$need_count = false;
			}

		} else {

			if($_GET['from'] == 'space') $diymode = 1;

			$wheresql = "uid='$space[uid]'";

		}
		$actives = array($_GET['view'] => ' class="a"');

		if($_GET['type'] && $_GET['type'] != 'all') {
			$sub_actives = array('type_'.$_GET['type'] => ' class="a"');
			$wheresql .= " AND type='$_GET[type]'";
		} else {
			$sub_actives = array('type_all' => ' class="a"');
		}

		$list = array();
		$pricount = 0;

		$sid = empty($_GET['sid'])?0:intval($_GET['sid']);
		$sharesql = $sid?"sid='$sid' AND":'';

		if($need_count) {
			$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_share')." WHERE $sharesql $wheresql"),0);
			if($count) {
				require_once libfile('function/share');
				$query = DB::query("SELECT * FROM ".DB::table('home_share')." $f_index
					WHERE $sharesql $wheresql
					ORDER BY dateline DESC
					LIMIT $start,$perpage");
				while ($value = DB::fetch($query)) {
					$value = mkshare($value);
					if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
						$list[] = $value;
					} else {
						$pricount ++;
					}
				}
				$multi = multi($count, $perpage, $page, $theurl);
			}
		}
	}
	dsetcookie('home_diymode', $diymode);
	$navtitle = lang('core', 'title_share_'.$_GET['type']);
	$navtitle .= lang('core', 'title_share');
	if($space['username']) {
		$navtitle = lang('space', 'sb_sharing', array('who' => $space['username']));
	}
	$metakeywords = $navtitle;
	$metadescription = $navtitle;

	include_once template("diy:home/space_share_list");
}

?>