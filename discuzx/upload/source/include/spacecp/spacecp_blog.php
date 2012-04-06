<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_blog.php 20084 2011-02-14 02:58:04Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
$op = empty($_GET['op'])?'':$_GET['op'];

$blog = array();
if($blogid) {
	$query = DB::query("SELECT bf.*, b.* FROM ".DB::table('home_blog')." b
		LEFT JOIN ".DB::table('home_blogfield')." bf USING(blogid)
		WHERE b.blogid='$blogid'");
	$blog = DB::fetch($query);
	if($blog['tag']) {
		$tagarray_all = $array_temp = $blogtag_array = array();
		$tagarray_all = explode("\t", $blog['tag']);
		if($tagarray_all) {
			foreach($tagarray_all as $var) {
				if($var) {
					$array_temp = explode(',', $var);
					$blogtag_array[] = $array_temp['1'];
				}
			}
		}
		$blog['tag'] = implode(',', $blogtag_array);
	}
}

if(empty($blog)) {
	if(!checkperm('allowblog')) {
		showmessage('no_authority_to_add_log', '', array(), array('return' => true));
	}

	cknewuser();

	$waittime = interval_check('post');
	if($waittime > 0) {
		showmessage('operating_too_fast', '', array('waittime' => $waittime), array('return' => true));
	}

	$blog['subject'] = empty($_GET['subject'])?'':getstr($_GET['subject'], 80, 1, 0);
	$blog['message'] = empty($_GET['message'])?'':getstr($_GET['message'], 5000, 1, 0);

} else {

	if($_G['uid'] != $blog['uid'] && !checkperm('manageblog') && $_G['gp_modblogkey'] != modauthkey($blog['blogid'])) {
		showmessage('no_authority_operation_of_the_log');
	}
}

if(submitcheck('blogsubmit', 0, $seccodecheck, $secqaacheck)) {

	if(empty($blog['blogid'])) {
		$blog = array();
	} else {
		if(!checkperm('allowblog')) {
			showmessage('no_privilege_blog');
		}
	}

	if($_G['setting']['blogcategorystat'] && $_G['setting']['blogcategoryrequired'] && !$_POST['catid']) {
		showmessage('blog_choose_system_category');
	}
	require_once libfile('function/blog');
	if($newblog = blog_post($_POST, $blog)) {
		if(empty($blog) && $newblog['topicid']) {
			$url = 'home.php?mod=space&uid='.$_G['uid'].'&do=topic&topicid='.$newblog['topicid'].'&view=blog&quickforward=1';
		} else {
			$url = 'home.php?mod=space&uid='.$newblog['uid'].'&do=blog&quickforward=1&id='.$newblog['blogid'];
		}
		if($_G['gp_modblogkey']) {
			$url .= "&modblogkey=$_G[gp_modblogkey]";
		}
		dsetcookie('clearUserdata', 'home');
		showmessage('do_success', $url);
	} else {
		showmessage('that_should_at_least_write_things', NULL, array(), array('return'=>1));
	}
}

if($_GET['op'] == 'delete') {
	if(submitcheck('deletesubmit')) {
		require_once libfile('function/delete');
		if(deleteblogs(array($blogid))) {
			showmessage('do_success', "home.php?mod=space&uid=$blog[uid]&do=blog&view=me");
		} else {
			showmessage('failed_to_delete_operation');
		}
	}

} elseif($_GET['op'] == 'stick') {
	space_merge($space, 'field_home');

	$stickflag = $_GET['stickflag'] ? 1 : 0;
	if(submitcheck('sticksubmit')) {
		if($space['uid'] === $blog['uid'] && empty($blog['status'])) {
			$stickblogs = explode(',', $space['stickblogs']);
			$pos = array_search($blogid, $stickblogs);
			if($pos !== false) {
				unset($stickblogs[$pos]);
			}
			$blogs = implode(',', $stickblogs);
			$blogs = empty($_POST['stickflag']) ? $blogs : $blogid.','.$blogs;
			$stickblogs = explode(',', $blogs);
			$stickblogs = array_filter($stickblogs);
			$space['stickblogs'] = implode(',', $stickblogs);
			DB::update('common_member_field_home', array('stickblogs' => $space['stickblogs']), array('uid' => $space['uid']));
			showmessage('do_success', dreferer("home.php?mod=space&uid=$blog[uid]&do=blog&view=me"));
		} else {
			showmessage('failed_to_stick_operation');
		}
	}

} elseif($_GET['op'] == 'edithot') {
	if(!checkperm('manageblog')) {
		showmessage('no_privilege_edithot_blog');
	}

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		DB::update('home_blog', array('hot'=>$_POST['hot']), array('blogid'=>$blog['blogid']));
		if($_POST['hot']>0) {
			require_once libfile('function/feed');
			feed_publish($blog['blogid'], 'blogid');
		} else {
			DB::update('home_feed', array('hot'=>$_POST['hot']), array('id'=>$blog['blogid'], 'idtype'=>'blogid'));
		}

		showmessage('do_success', "home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]");
	}

} else {
	$classarr = $blog['uid']?getclassarr($blog['uid']):getclassarr($_G['uid']);
	$albums = getalbums($_G['uid']);

	$friendarr = array($blog['friend'] => ' selected');

	$passwordstyle = $selectgroupstyle = 'display:none';
	if($blog['friend'] == 4) {
		$passwordstyle = '';
	} elseif($blog['friend'] == 2) {
		$selectgroupstyle = '';
		if($blog['target_ids']) {
			$names = array();
			$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid IN ($blog[target_ids])");
			while ($value = DB::fetch($query)) {
				$names[] = $value['username'];
			}
			$blog['target_names'] = implode(' ', $names);
		}
	}


	$blog['message'] = dhtmlspecialchars($blog['message']);

	$allowhtml = checkperm('allowhtml');

	require_once libfile('function/friend');
	$groups = friend_group_list();

	if($_G['setting']['blogcategorystat']) {
		loadcache('blogcategory');
		$category = $_G['cache']['blogcategory'];

		$categoryselect = '';
		if($category) {
			include_once libfile('function/portalcp');
			$categoryselect = category_showselect('blog', 'catid', !$_G['setting']['blogcategoryrequired'] ? true : false, $blog['catid']);
		}
	}
	$menuactives = array('space'=>' class="active"');
}

include_once template("home/spacecp_blog");

?>