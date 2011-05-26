<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_recyclebinpost.php 22097 2011-04-21 08:49:57Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}


$op = !in_array($op , array('list', 'delete', 'search', 'restore')) ? 'list' : $op;
$do = !empty($_G['gp_do']) ? dhtmlspecialchars($_G['gp_do']) : '';

$pidarray = array();
$action = $_G['gp_action'];

$result = array();

foreach (array('starttime', 'endtime', 'keywords', 'users') as $key) {
	$$key = isset($_G['gp_'.$key]) ? trim($_G['gp_'.$key]) : '';
	$result[$key] = isset($_G['gp_'.$key]) ? dhtmlspecialchars($_G['gp_'.$key]) : '';
}

$postlist = array();
$total = $multipage = '';

$posttableid = intval($_G['gp_posttableid']);
$posttableselect = getposttableselect();

$cachekey = 'srchresult_recycle_post_'.$posttableid.'_'.$_G['fid'];

if($_G['fid'] && $_G['forum']['ismoderator'] && $modforums['recyclebins'][$_G['fid']]) {

	$srchupdate = false;

	if(in_array($_G['adminid'], array(1, 2, 3)) && ($op == 'delete' || $op == 'restore') && submitcheck('dosubmit')) {
		if($ids = dimplode($_G['gp_moderate'])) {
			$pidarray = array();
			$query = DB::query('SELECT pid FROM '.DB::table(getposttable($posttableid))." WHERE pid IN ($ids) AND fid='$_G[fid]' AND invisible='-5'");
			while($post = DB::fetch($query)) {
				$pidarray[] = $post['pid'];
			}
			if($pidarray) {
				require_once libfile('function/misc');
				if ($op == 'delete' && $_G['group']['allowclearrecycle']){
					recyclebinpostdelete($pidarray, $posttableid);
				}
				if ($op == 'restore') {
					recyclebinpostundelete($pidarray, $posttableid);
				}

				if($_G['gp_oldop'] == 'search') {
					$srchupdate = true;
				}
			}
		}

		$op = dhtmlspecialchars($_G['gp_oldop']);
	}

	if($op == 'search' &&  submitcheck('searchsubmit')) {

		$sql = '';

		$sql .= $starttime != '' ? " AND dateline>='".strtotime($starttime)."'" : '';
		$sql .= $endtime != '' ? " AND dateline<='".strtotime($endtime)."'" : '';

		if(trim($keywords)) {
			$sqlkeywords = '';
			$or = '';
			$keywords = explode(',', str_replace(' ', '', $keywords));
			for($i = 0; $i < count($keywords); $i++) {
				$sqlkeywords .= " $or message LIKE '%".$keywords[$i]."%'";
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";

			$keywords = implode(', ', $keywords);
		}

		if(trim($users)) {
			$sql .= " AND author IN ('".str_replace(',', '\',\'', str_replace(' ', '', trim($users)))."')";
		}

		if($sql) {

			$pids = array();
			$query = DB::query('SELECT message, pid, tid, fid, author, dateline, subject, authorid
					FROM '.DB::table(getposttable($posttableid))."
					WHERE invisible='-5' $sql
					ORDER BY dateline DESC
					LIMIT 1000");
			while($value = DB::fetch($query)){
				$postlist[] = $value;
				$pids[] = $value['pid'];
			}

			$result['pids'] = implode(',', $pids);
			$result['count'] = count($pids);
			$result['fid'] = $_G['fid'];
			$result['posttableid'] = $posttableid;

			$modsession->set($cachekey, $result, true);

			DB::free_result($query);
			unset($result, $pids);
			$page = 1;

		} else {
			$op = 'list';
		}
	}

	$page = max(1, intval($_G['page']));
	$total = 0;
	$query = $multipage = '';
	$fields = 'message, useip, attachment, htmlon, smileyoff, bbcodeoff, pid, tid, fid, author, dateline, subject, authorid, anonymous';

	if($op == 'list') {
		$total = DB::result_first('SELECT COUNT(*) FROM '.DB::table(getposttable($posttableid))." WHERE fid='$_G[fid]' AND invisible='-5'");
		$tpage = ceil($total / $_G['tpp']);
		$page = min($tpage, $page);
		$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&action=$action&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do");
		if($total) {
			$start = ($page - 1) * $_G['tpp'];
			$query = DB::query("SELECT $fields
					FROM ".DB::table(getposttable($posttableid))."
					WHERE fid='$_G[fid]' AND invisible='-5'
					ORDER BY dateline DESC
					LIMIT $start, $_G[tpp]");
			while($value = DB::fetch($query)){
				$postlist[] = $value;
			}
		}
	}

	if($op == 'search') {

		$result = $modsession->get($cachekey);

		if($result) {

			if($srchupdate && $result['count'] && $pidarray) {
				$pd = explode(',', $result['pids']);
				$newpids = $comma = $newcount = '';
				if(is_array($pd)) {
					foreach ($pd as $v) {
						$v = intval($v);
						if(!in_array($v, $pidarray)) {
							$newcount ++;
							$newpids .= $comma.$v; $comma = ',';
						}
					}
					$result['count'] = $newcount;
					$result['pids'] = $newpids;
					$modsession->set($cachekey, $result, true);
				}
			}

			$total = $result['count'];
			$tpage = ceil($total / $_G['tpp']);
			$page = min($tpage, $page);
			$multipage = multi($total, $_G['tpp'], $page, "$cpscript?mod=modcp&action=$action&amp;op=$op&amp;fid=$_G[fid]&amp;do=$do");
			if($total) {
				$start = ($page - 1) * $_G['tpp'];
				$query = DB::query("SELECT $fields
						FROM ".DB::table(getposttable($posttableid))."
						WHERE pid IN ($result[pids]) AND fid='$_G[fid]' AND invisible='-5'
						ORDER BY dateline DESC
						LIMIT $start, $_G[tpp]");
				while($value = DB::fetch($query)){
					$postlist[] = $value;
				}
			}

		}

	}

	if($postlist) {
		require_once libfile('function/misc');
		require_once libfile('function/post');
		require_once libfile('function/discuzcode');
		foreach($postlist as $key => $post) {
			$post['modthreadkey'] = modauthkey($post['tid']);
			$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml']);
			$post['dateline'] = dgmdate($post['dateline'], 'Y-m-d H:i:s');
			$postlist[$key] = $post;
		}
	}
}

?>