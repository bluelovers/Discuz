<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_recyclebin.php 14289 2010-08-10 06:08:50Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


$op = !in_array($op , array('list', 'delete', 'search', 'restore')) ? 'list' : $op;
$do = !empty($_G['gp_do']) ? dhtmlspecialchars($_G['gp_do']) : '';

$tidarray = array();
$action = $_G['gp_action'];

$result = array();
foreach (array('threadoption', 'viewsless', 'viewsmore', 'repliesless', 'repliesmore', 'noreplydays') as $key) {
	$$key = isset($_G['gp_'.$key]) && is_numeric($_G['gp_'.$key]) ? intval($_G['gp_'.$key]) : '';
	$result[$key] = $$key;
}

foreach (array('starttime', 'endtime', 'keywords', 'users') as $key) {
	$$key = isset($_G['gp_'.$key]) ? trim($_G['gp_'.$key]) : '';
	$result[$key] = isset($_G['gp_'.$key]) ? dhtmlspecialchars($_G['gp_'.$key]) : '';
}

$threadoptionselect = array('','','','','','', '', '', '', '', 999=>'', 888=>'');
$threadoptionselect[$threadoption] = 'selected';

$postlist = array();

$total = $multipage = '';

if($_G['fid'] && $_G['forum']['ismoderator'] && $modforums['recyclebins'][$_G['fid']]) {

	$srchupdate = false;

	if(in_array($_G['adminid'], array(1, 2, 3)) && ($op == 'delete' || $op == 'restore') && submitcheck('dosubmit')) {
		if($ids = dimplode($_G['gp_moderate'])) {
			$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE tid IN($ids) AND fid='$_G[fid]' AND displayorder='-1'");
			while($tid = DB::fetch($query)) {
				$tidarray[] = $tid['tid'];
				if($op == 'restore') {
					my_thread_log('restore', array('tid' => $tid['tid']));
				}
			}
			if($tidarray) {
				require_once libfile('function/post');
				($op == 'delete' && $_G['group']['allowclearrecycle']) && deletethreads($tidarray);
				($op == 'restore') && undeletethreads($tidarray);

				if($_G['gp_oldop'] == 'search') {
					$srchupdate = true;
				}
			}
		}

		$op = dhtmlspecialchars($_G['gp_oldop']);

	}



	if($op == 'search' &&  submitcheck('searchsubmit')) {

		$sql = '';

		if($threadoption > 0 && $threadoption < 255) {
			$sql .= " AND special='$threadoption'";
		} elseif($threadoption == 999) {
			$sql .= " AND digest in(1,2,3)";
		} elseif($threadoption == 888) {
			$sql .= " AND displayorder IN(1,2,3)";
		}

		$sql .= $viewsless !== ''? " AND views<='$viewsless'" : '';
		$sql .= $viewsmore !== ''? " AND views>='$viewsmore]'" : '';
		$sql .= $repliesless !== ''? " AND replies<='$repliesless]'" : '';
		$sql .= $repliesmore !== ''? " AND replies>='$repliesmore]'" : '';
		$sql .= $noreplydays !== ''? " AND lastpost<='".(TIMESTAMP -$noreplydays*86400)."'" : '';
		$sql .= $starttime != '' ? " AND dateline>='".strtotime($starttime)."'" : '';
		$sql .= $endtime != '' ? " AND dateline<='".strtotime($endtime)."'" : '';

		if(trim($keywords)) {
			$sqlkeywords = '';
			$or = '';
			$keywords = explode(',', str_replace(' ', '', $keywords));
			for($i = 0; $i < count($keywords); $i++) {
				$sqlkeywords .= " $or subject LIKE '%".$keywords[$i]."%'";
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";

			$keywords = implode(', ', $keywords);
		}

		if(trim($users)) {
			$sql .= " AND author IN ('".str_replace(',', '\',\'', str_replace(' ', '', trim($users)))."')";
		}

		if($sql) {

			$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE fid='$_G[fid]' AND displayorder='-1' $sql ORDER BY lastpost DESC LIMIT 1000");
			$tids = $comma = '';
			$count = 0;
			while($tid = DB::fetch($query)) {
				$tids .= $comma.$tid['tid'];
				$comma = ',';
				$count ++;
			}

			$result['tids'] = $tids;
			$result['count'] = $count;
			$result['fid'] = $_G['fid'];

			$modsession->set('srchresult_r', $result, true);

			DB::free_result($query);
			unset($result, $tids);
			$page = 1;

		} else {
			$op = 'list';
		}
	}

	$page = max(1, intval($_G['page']));
	$total = 0;
	$query = $multipage = '';

	if($op == 'list') {
		$total = DB::result_first("SELECT count(*) FROM ".DB::table('forum_thread')." WHERE fid='$_G[fid]' AND displayorder='-1'");
		$tpage = ceil($total / $_G['tpp']);
		$page = min($tpage, $page);
		$multipage = multi($total, $_G['tpp'], $page, "$cpscript?action=$action&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do");
		if($total) {
			$start = ($page - 1) * $_G['tpp'];
			$query = DB::query("SELECT t.*, tm.reason FROM ".DB::table('forum_thread')." t LEFT JOIN ".DB::table('forum_threadmod')." tm ON tm.tid=t.tid WHERE t.fid='$_G[fid]' AND t.displayorder='-1' GROUP BY t.tid ORDER BY t.lastpost DESC LIMIT $start, $_G[tpp]");
		}
	}

	if($op == 'search') {

		$result = $modsession->get('srchresult_r');

		if($result['fid'] == $_G['fid']) {

			if($srchupdate && $result['count'] && $tidarray) {
				$td = explode(',', $result['tids']);
				$newtids = $comma = $newcount = '';
				if(is_array($td)) {
					foreach ($td as $v) {
						$v = intval($v);
						if(!in_array($v, $tidarray)) {
							$newcount ++;
							$newtids .= $comma.$v; $comma = ',';
						}
					}
					$result['count'] = $newcount;
					$result['tids'] = $newtids;
					$modsession->set('srchresult_r'.$_G['fid'], $result, true);
				}
			}

			$threadoptionselect[$result['threadoption']] = 'selected';

			$total = $result['count'];
			$tpage = ceil($total / $_G['tpp']);
			$page = min($tpage, $page);
			$multipage = multi($total, $_G['tpp'], $page, "$cpscript?action=$action&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do");
			if($total) {
				$start = ($page - 1) * $_G['tpp'];
				$query = DB::query("SELECT t.*, tm.reason FROM ".DB::table('forum_thread')." t LEFT JOIN ".DB::table('forum_threadmod')." tm ON tm.tid=t.tid WHERE t.tid in($result[tids]) AND t.fid='$_G[fid]' AND t.displayorder='-1' ORDER BY t.lastpost DESC LIMIT $start, $_G[tpp]");
			}

		} else {
			$result = array();
			$modsession->set('srchresult_r', array());
		}

	}

	$postlist = array();
	if($query) {
		require_once libfile('function/misc');
		while ($thread = DB::fetch($query)) {
			$post = procthread($thread);
			$post['modthreadkey'] = modauthkey($post['tid']);
			$postlist[] = $post;
		}
	}

}

?>