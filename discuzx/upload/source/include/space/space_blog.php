<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_blog.php 16856 2010-09-16 02:53:58Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);

if($id) {
	$query = DB::query("SELECT bf.*, b.* FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid WHERE b.blogid='$id' AND b.uid='$space[uid]'");
	$blog = DB::fetch($query);
	if(!(!empty($blog) && ($blog['status'] == 0 || $blog['uid'] == $_G['uid'] || $_G['adminid'] == 1 || $_G['gp_modblogkey'] == modauthkey($blog['blogid'])))) {
		showmessage('view_to_info_did_not_exist');
	}
	if(!ckfriend($blog['uid'], $blog['friend'], $blog['target_ids'])) {
		require_once libfile('function/friend');
		$isfriend = friend_check($blog['uid']);
		space_merge($space, 'count');
		space_merge($space, 'profile');
		$_G['privacy'] = 1;
		require_once libfile('space/profile', 'include');
		include template('home/space_privacy');
		exit();
	} elseif(!$space['self'] && $blog['friend'] == 4) {
		$cookiename = "view_pwd_blog_$blog[blogid]";
		$cookievalue = empty($_G['cookie'][$cookiename])?'':$_G['cookie'][$cookiename];
		if($cookievalue != md5(md5($blog['password']))) {
			$invalue = $blog;
			include template('home/misc_inputpwd');
			exit();
		}
	}

	$query = DB::query("SELECT classid, classname FROM ".DB::table('home_class')." WHERE classid='$blog[classid]'");
	$classarr = DB::fetch($query);

	if($blog['catid']) {
		$blog['catname'] = DB::result(DB::query("SELECT catname FROM ".DB::table('home_blog_category')." WHERE catid='$blog[catid]'"), 0);
		$blog['catname'] = dhtmlspecialchars($blog['catname']);
	}

	require_once libfile('function/blog');
	$blog['message'] = blog_bbcode($blog['message']);

	$otherlist = $newlist = array();

	$otherlist = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_blog')." WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,6");
	while ($value = DB::fetch($query)) {
		if($value['blogid'] != $blog['blogid'] && empty($value['friend'])) {
			$otherlist[] = $value;
		}
	}

	$newlist = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_blog')." WHERE hot>='$minhot' ORDER BY dateline DESC LIMIT 0,6");
	while ($value = DB::fetch($query)) {
		if($value['blogid'] != $blog['blogid'] && empty($value['friend'])) {
			$newlist[] = $value;
		}
	}

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page-1)*$perpage;

	ckstart($start, $perpage);

	$count = $blog['replynum'];

	$list = array();
	if($count) {
		$csql = '';
		if($_GET['goto']) {
			$page = ceil($count/$perpage);
			$start = ($page-1)*$perpage;
		} else {
			$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
			$csql = $cid?"cid='$cid' AND":'';
		}

		$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE $csql id='$id' AND idtype='blogid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$list[] = $value;
		}

		if(empty($list) && empty($cid)) {
			$count = getcount('home_comment', array('id'=>$id, 'idtype'=>'blogid'));
			DB::update('home_blog', array('replynum'=>$count), array('blogid'=>$blog['blogid']));
		}
	}

	$multi = multi($count, $perpage, $page, "home.php?mod=space&uid=$blog[uid]&do=$do&id=$id#comment");

	if(!$space['self'] && $_G['cookie']['view_blogid'] != $blog['blogid']) {
		DB::query("UPDATE ".DB::table('home_blog')." SET viewnum=viewnum+1 WHERE blogid='$blog[blogid]'");
		dsetcookie('view_blogid', $blog['blogid']);
	}

	$hash = md5($blog['uid']."\t".$blog['dateline']);
	$id = $blog['blogid'];
	$idtype = 'blogid';

	$maxclicknum = 0;
	loadcache('click');
	$clicks = empty($_G['cache']['click']['blogid'])?array():$_G['cache']['click']['blogid'];

	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $blog["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$clickuserlist = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,24");
	while ($value = DB::fetch($query)) {
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		$clickuserlist[] = $value;
	}
	$actives = array('me' =>' class="a"');

	$diymode = intval($_G['cookie']['home_diymode']);

	$navtitle = $blog['subject'].' - '.lang('space', 'sb_blog', array('who' => $blog['username']));
	$metakeywords = $blog['tag'] ? $blog['tag'] : $blog['subject'];
	$metadescription = cutstr(strip_tags($blog['message']), 140);

	include_once template("diy:home/space_blog_view");

} else {

	loadcache('blogcategory');
	$category = $_G['cache']['blogcategory'];

	if(empty($_GET['view'])) $_GET['view'] = 'we';

	$perpage = 10;
	$perpage = mob_perpage($perpage);
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	$summarylen = 300;

	$classarr = array();
	$list = array();
	$userlist = array();
	$count = $pricount = 0;

	$gets = array(
		'mod' => 'space',
		'uid' => $space['uid'],
		'do' => 'blog',
		'view' => $_GET['view'],
		'order' => $_GET['order'],
		'classid' => $_GET['classid'],
		'catid' => $_GET['catid'],
		'clickid' => $_GET['clickid'],
		'fuid' => $_GET['fuid'],
		'searchkey' => $_GET['searchkey'],
		'from' => $_GET['from'],
		'friend' => $_GET['friend']
	);
	$theurl = 'home.php?'.url_implode($gets);
	$multi = '';

	$wheresql = '1';
	$f_index = '';
	$ordersql = 'b.dateline DESC';
	$need_count = true;

	if($_GET['view'] == 'all') {
		if($_GET['order'] == 'hot') {
			$wheresql .= " AND b.hot>='$minhot'";

			$orderactives = array('hot' => ' class="a"');
		} else {
			$orderactives = array('dateline' => ' class="a"');
		}

	} elseif($_GET['view'] == 'me') {

		$wheresql .= " AND b.uid='$space[uid]'";

		$classid = empty($_GET['classid'])?0:intval($_GET['classid']);
		if($classid) {
			$wheresql .= " AND b.classid='$classid'";
		}

		$privacyfriend = empty($_G['gp_friend'])?0:intval($_G['gp_friend']);
		if($privacyfriend) {
			$wheresql .= " AND b.friend='$privacyfriend'";
		}
		$query = DB::query("SELECT classid, classname FROM ".DB::table('home_class')." WHERE uid='$space[uid]'");
		while ($value = DB::fetch($query)) {
			$classarr[$value['classid']] = $value['classname'];
		}

		if($_GET['from'] == 'space') $diymode = 1;

	} else {

		space_merge($space, 'field_home');

		if($space['feedfriend']) {

			$fuid_actives = array();

			require_once libfile('function/friend');
			$fuid = intval($_GET['fuid']);
			if($fuid && friend_check($fuid, $space['uid'])) {
				$wheresql = "b.uid='$fuid'";
				$fuid_actives = array($fuid=>' selected');
			} else {
				$wheresql = "b.uid IN ($space[feedfriend])";
				$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&view=we";
				$f_index = 'USE INDEX(dateline)';
			}

			$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$space[uid]' ORDER BY num DESC LIMIT 0,100");
			while ($value = DB::fetch($query)) {
				$userlist[] = $value;
			}
		} else {
			$need_count = false;
		}
	}

	$actives = array($_GET['view'] =>' class="a"');

	if($need_count) {
		if($searchkey = stripsearchkey($_GET['searchkey'])) {
			$wheresql .= " AND b.subject LIKE '%$searchkey%'";
		}

		$catid = empty($_GET['catid'])?0:intval($_GET['catid']);
		if($catid) {
			$wheresql .= " AND b.catid='$catid'";
		}


		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_blog')." b WHERE $wheresql"),0);
		if($count) {
			$query = DB::query("SELECT bf.*, b.* FROM ".DB::table('home_blog')." b $f_index
				LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
				WHERE $wheresql
				ORDER BY $ordersql LIMIT $start,$perpage");
		}
	}

	if($count) {
		while ($value = DB::fetch($query)) {
			if(ckfriend($value['uid'], $value['friend'], $value['target_ids']) && ($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1)) {
				if($value['friend'] == 4) {
					$value['message'] = $value['pic'] = '';
				} else {
					$value['message'] = getstr($value['message'], $summarylen, 0, 0, 0, -1);
				}
				$value['message'] = preg_replace("/&[a-z]+\;/i", '', $value['message']);
				if($value['pic']) $value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
				$value['dateline'] = dgmdate($value['dateline']);
				$list[] = $value;
			} else {
				$pricount++;
			}
		}

		$multi = multi($count, $perpage, $page, $theurl);
	}

	dsetcookie('home_diymode', $diymode);

	if($_G['uid']) {
		if($_G['gp_view'] == 'all') {
			$navtitle = lang('core', 'title_view_all').lang('core', 'title_blog');
		} elseif($_G['gp_view'] == 'me') {
			$navtitle = lang('core', 'title_my_blog');
		} else {
			$navtitle = lang('core', 'title_friend_blog');

		}
	} else {
		if($_G['gp_order'] == 'hot') {
			$navtitle = lang('core', 'title_recommend_blog');
		} else {
			$navtitle = lang('core', 'title_newest_blog');
		}
	}
	if($space['username']) {
		$navtitle = lang('space', 'sb_blog', array('who' => $space['username']));
	}
	$metakeywords = $navtitle;
	$metadescription = $navtitle;

	space_merge($space, 'field_home');
	include_once template("diy:home/space_blog_list");

}

?>