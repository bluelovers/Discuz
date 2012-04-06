<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_tag.php 18220 2010-11-17 02:38:38Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$id = intval($_G['gp_id']);
$type = trim($_G['gp_type']);
$name = trim($_G['gp_name']);
$page = intval($_G['gp_page']);

$taglang = lang('tag/template', 'tag');
if($id || $name) {

	$tpp = 20;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;
	$where = $twhere = '';
	if($id) {
		$where = $twhere = " tagid='$id'";
	} else {
		if(!preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $name) || strlen($name) > 20) {
			showmessage('parameters_error');
		}
		$name = addslashes($name);
		$twhere = " tagname='$name'";
	}

	$tag = DB::fetch_first("SELECT tagid,tagname,status FROM ".DB::table('common_tag')." WHERE 1 AND $twhere");
	if($tag['status'] == 1) {
		showmessage('tag_closed');
	}
	$tagname = $tag['tagname'];
	$id = $tag['tagid'];
	$searchtagname = $name;
	$navtitle = $tagname ? $taglang.' - '.$tagname : $taglang;
	$metakeywords = $tagname ? $taglang.' - '.$tagname : $taglang;
	$metadescription = $tagname ? $taglang.' - '.$tagname : $taglang;

	$where = $where ? $where : " tagid='$id'";;

	$showtype = '';
	$count = '';
	$summarylen = 300;

	if($type == 'thread') {
		$showtype = 'thread';
		$tidarray = $threadlist = array();
		$count = DB::result_first("SELECT count(*) FROM ".DB::table('common_tagitem')." WHERE idtype='tid' AND $where");
		if($count) {
			$query = DB::query("SELECT itemid FROM ".DB::table('common_tagitem')." WHERE idtype='tid' AND $where LIMIT $start_limit, $tpp");
			while($result = DB::fetch($query)) {
				$tidarray[$result['itemid']] = $result['itemid'];
			}
			$threadlist = getthreadsbytids($tidarray);
			$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id=$tag[tagid]&type=thread");
		}
	} elseif($type == 'blog') {
		$showtype = 'blog';
		$blogidarray = $bloglist = array();
		$count = DB::result_first("SELECT count(*) FROM ".DB::table('common_tagitem')." WHERE idtype='blogid' AND $where");
		if($count) {
			$query = DB::query("SELECT itemid FROM ".DB::table('common_tagitem')." WHERE idtype='blogid' AND $where LIMIT $start_limit, $tpp");
			while($result = DB::fetch($query)) {
				$blogidarray[$result['itemid']] = $result['itemid'];
			}
			$bloglist = getblogbyid($blogidarray);

			$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id=$tag[tagid]&type=blog");
		}
	} else {
		$shownum = 20;

		$tidarray = $threadlist = array();
		$query = DB::query("SELECT itemid FROM ".DB::table('common_tagitem')." WHERE idtype='tid' AND $where LIMIT $shownum");
		while($result = DB::fetch($query)) {
			$tidarray[$result['itemid']] = $result['itemid'];
		}
		$threadlist = getthreadsbytids($tidarray);

		$blogidarray = $bloglist = array();
		$query = DB::query("SELECT itemid FROM ".DB::table('common_tagitem')." WHERE idtype='blogid' AND $where LIMIT $shownum");
		while($result = DB::fetch($query)) {
			$blogidarray[$result['itemid']] = $result['itemid'];
		}
		$bloglist = getblogbyid($blogidarray);

	}

	include_once template('tag/tagitem');

} else {
	$navtitle = $metakeywords = $metadescription = $taglang;
	$viewthreadtags = 100;
	$tagarray = array();
	$query = DB::query("SELECT tagid,tagname FROM ".DB::table('common_tag')." WHERE status=0 ORDER BY tagid DESC LIMIT $viewthreadtags");
	while($result =	DB::fetch($query)) {
		$tagarray[] = $result;
	}
	include_once template('tag/tag');
}

function getthreadsbytids($tidarray) {
	global $_G;

	$threadlist = array();
	if(!empty($tidarray)) {
		loadcache('forums');
		include_once libfile('function_misc', 'function');
		$query = DB::query("SELECT t.*,f.name FROM ".DB::table('forum_thread')." t LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid WHERE t.tid IN (".dimplode($tidarray).")  ORDER BY t.lastpost DESC");
		while($result = DB::fetch($query)) {
			if(!isset($_G['cache']['forums'][$result['fid']]['name'])) {
				$_G['cache']['forums'][$result['fid']]['name'] = $result['name'];
			}
			$threadlist[] = procthread($result);
		}
	}
	return $threadlist;
}

function getblogbyid($blogidarray) {
	global $_G;

	$bloglist = array();
	if(!empty($blogidarray)) {
		$query = DB::query("SELECT bf.*, b.* FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid WHERE b.blogid IN (".dimplode($blogidarray).") ORDER BY b.dateline DESC");
		require_once libfile('function/spacecp');
		require_once libfile('function/home');
		$classarr = array();
		while($result = DB::fetch($query)) {
			$result['dateline'] = dgmdate($result['dateline']);
			$classarr = getclassarr($result['uid']);
			$result['classname'] = $classarr[$result[classid]]['classname'];
			if($result['friend'] == 4) {
				$result['message'] = $result['pic'] = '';
			} else {
				$result['message'] = getstr($result['message'], $summarylen, 0, 0, 0, -1);
			}
			$result['message'] = preg_replace("/&[a-z]+\;/i", '', $result['message']);
			if($result['pic']) {
				$result['pic'] = pic_cover_get($result['pic'], $result['picflag']);
			}
			$bloglist[] = $result;
		}
	}
	return $bloglist;
}
?>