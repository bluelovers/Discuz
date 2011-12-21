<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_group.php 26486 2011-12-14 02:20:03Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/home');
require_once libfile('function/post');

if(!$_G['setting']['groupstatus']) {
	showmessage('group_status_off');
}
if(!$_G['setting']['search']['group']['status']) {
	showmessage('search_group_closed');
}

if($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 16)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['group']['searchctrl'] = intval($_G['setting']['search']['group']['searchctrl']);

$srchmod = 5;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_G['gp_srchtype']) ? '' : trim($_G['gp_srchtype']);
$searchid = isset($_G['gp_searchid']) ? intval($_G['gp_searchid']) : 0;

$srchtxt = $_G['gp_srchtxt'];
$srchfid = intval($_G['gp_srchfid']);
$viewgroup = intval($_G['gp_viewgroup']);
$keyword = isset($srchtxt) ? htmlspecialchars(trim($srchtxt)) : '';

if(!submitcheck('searchsubmit', 1)) {

	include template('search/group');

} else {

	$orderby = in_array($_G['gp_orderby'], array('dateline', 'replies', 'views')) ? $_G['gp_orderby'] : 'lastpost';
	$ascdesc = isset($_G['gp_ascdesc']) && $_G['gp_ascdesc'] == 'asc' ? 'asc' : 'desc';

	if(!empty($searchid)) {

		require_once libfile('function/group');

		$page = max(1, intval($_G['gp_page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$index = DB::fetch_first("SELECT searchstring, keywords, num, ids FROM ".DB::table('common_searchindex')." WHERE searchid='$searchid' AND srchmod='$srchmod'");
		if(!$index) {
			showmessage('search_id_invalid');
		}

		$keyword = htmlspecialchars($index['keywords']);
		$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

		$index['keywords'] = rawurlencode($index['keywords']);
		$index['ids'] = unserialize($index['ids']);
		$searchstring = explode('|', $index['searchstring']);
		$srchfid = $searchstring[2];
		$threadlist = $grouplist = $posttables = array();
		if($index['ids']['thread'] && ($searchstring[2] || empty($viewgroup))) {
			require_once libfile('function/misc');
			$query = DB::query("SELECT t.*, f.name AS forumname FROM ".DB::table('forum_thread')." t LEFT JOIN ".DB::table('forum_forum')." f ON t.fid=f.fid WHERE t.tid IN ({$index[ids][thread]}) AND t.displayorder>='0' ORDER BY $orderby $ascdesc LIMIT $start_limit, $_G[tpp]");
			while($thread = DB::fetch($query)) {
				$thread['subject'] = bat_highlight($thread['subject'], $keyword);
				$thread['realtid'] = $thread['tid'];
				$threadlist[$thread['tid']] = procthread($thread);
				$posttables[$thread['posttableid']][] = $thread['tid'];
			}
			if($threadlist) {
				foreach($posttables as $tableid => $tids) {
					$query = DB::query("SELECT tid, message FROM ".DB::table(getposttable($tableid))." WHERE tid IN (".dimplode($tids).") AND first='1'");
					while($post = DB::fetch($query)) {
						$threadlist[$post['tid']]['message'] = bat_highlight(messagecutstr($post['message'], 200), $keyword);
					}
				}
			}
		}
		$groupnum = !empty($index['ids']['group']) ? count(explode(',', $index['ids']['group'])) - 1 : 0;
		if($index['ids']['group'] && ($viewgroup || empty($searchstring[2]))) {
			if(empty($viewgroup)) {
				$index['ids']['group'] = implode(',', array_slice(explode(',', $index['ids']['group']), 0, 9));
			}
			$query = DB::query("SELECT f.*, ff.description, ff.membernum, ff.icon, ff.gviewperm, ff.jointype, ff.dateline FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON f.fid=ff.fid WHERE f.fid IN ({$index[ids][group]}) AND f.status='3' AND `type`='sub'".($viewgroup ? " LIMIT $start_limit, $_G[tpp]" : ''));
			while($group = DB::fetch($query)) {
				$group['icon'] = get_groupimg($group['icon'], 'icon');
				$group['name'] = bat_highlight($group['name'], $keyword);
				$group['description'] = bat_highlight($group['description'], $keyword);
				$group['dateline'] = dgmdate($group['dateline'], 'u');
				$grouplist[] = $group;
			}
		}
		if(empty($viewgroup)) {
			$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=group&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes".($viewgroup ? '&viewgroup=1' : ''));
		} else {
			$multipage = multi($groupnum, $_G['tpp'], $page, "search.php?mod=group&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes".($viewgroup ? '&viewgroup=1' : ''));
		}

		$url_forward = 'search.php?mod=group&'.$_SERVER['QUERY_STRING'];

		include template('search/group');

	} else {

		$srchuname = isset($_G['gp_srchuname']) ? trim($_G['gp_srchuname']) : '';

		$searchstring = 'group|title|'.$srchfid.'|'.addslashes($srchtxt);
		$searchindex = array('id' => 0, 'dateline' => '0');

		$query = DB::query("SELECT searchid, dateline,
			('".$_G['setting']['search']['group']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<'".$_G['setting']['search']['group']['searchctrl']."') AS flood,
			(searchstring='$searchstring' AND expiration>'$_G[timestamp]') AS indexvalid
			FROM ".DB::table('common_searchindex')."
			WHERE srchmod='$srchmod' AND ('".$_G['setting']['search']['group']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<".$_G['setting']['search']['group']['searchctrl'].") OR (searchstring='$searchstring' AND expiration>'$_G[timestamp]')
			ORDER BY flood");

		while($index = DB::fetch($query)) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=group', array('searchctrl' => $_G['setting']['search']['group']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname) {
				dheader('Location: search.php?mod=group');
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['group']['maxspm']) {
				if((DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_searchindex')." WHERE srchmod='$srchmod' AND dateline>'$_G[timestamp]'-60")) >= $_G['setting']['search']['group']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=group', array('maxspm' => $_G['setting']['search']['group']['maxspm']));
				}
			}

			$num = $ids = $tnum = $tids = 0;
			$_G['setting']['search']['group']['maxsearchresults'] = $_G['setting']['search']['group']['maxsearchresults'] ? intval($_G['setting']['search']['group']['maxsearchresults']) : 500;
			list($srchtxt, $srchtxtsql) = searchkey($keyword, "subject LIKE '%{text}%'", true);

			$query = DB::query("SELECT t.tid, f.status FROM ".DB::table('forum_thread')." t LEFT JOIN ".DB::table('forum_forum')." f ON t.fid=f.fid WHERE ".($srchfid ? "t.fid='$srchfid' AND ": '')."t.isgroup='1' $srchtxtsql ORDER BY tid DESC LIMIT ".$_G['setting']['search']['group']['maxsearchresults']);
			while($thread = DB::fetch($query)) {
				if($thread['status'] == 3) {
					$tids .= ','.$thread['tid'];
					$tnum++;
				}
			}
			DB::free_result($query);

			if(!$srchfid) {
				$srchtxtsql = str_replace('subject LIKE', 'name LIKE', $srchtxtsql);
				$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE `type`='sub' AND status='3' $srchtxtsql LIMIT ".$_G['setting']['search']['group']['maxsearchresults']);
				while($group = DB::fetch($query)) {
					$ids .= ','.$group['fid'];
					$num++;
				}
			}
			$allids = array('thread' => $tids, 'group' => $ids);
			$keywords = str_replace('%', '+', $srchtxt);
			$expiration = TIMESTAMP + $cachelife_text;

			DB::query("INSERT INTO ".DB::table('common_searchindex')." (srchmod, keywords, searchstring, useip, uid, dateline, expiration, num, ids)
					VALUES ('$srchmod', '$keywords', '$searchstring', '$_G[clientip]', '$_G[uid]', '$_G[timestamp]', '$expiration', '$tnum', '".serialize($allids)."')");
			$searchid = DB::insert_id();

			!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=group&searchid=$searchid&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>