<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_forum.php 17321 2010-10-08 08:02:36Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

if(!$_G['setting']['search']['forum']['status']) {
	showmessage('search_forum_closed');
}

if(!$_G['adminid'] && !($_G['group']['allowsearch'] & 2)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['forum']['searchctrl'] = intval($_G['setting']['search']['forum']['searchctrl']);

require_once libfile('function/forumlist');
require_once libfile('function/forum');
require_once libfile('function/post');
loadcache(array('forums', 'icons'));

$srchmod = 2;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_G['gp_srchtype']) ? '' : trim($_G['gp_srchtype']);
$checkarray = array('posts' => '');

$searchid = isset($_G['gp_searchid']) ? intval($_G['gp_searchid']) : 0;

if($srchtype == 'title' || $srchtype == 'fulltext') {
	$checkarray['posts'] = 'checked';
} else {
	$srchtype = '';
	$checkarray['posts'] = 'checked';
}

$srchtxt = $_G['gp_srchtxt'];
$srchuid = intval($_G['gp_srchuid']);
$srchuname = $_G['gp_srchuname'];
$srchfrom = intval($_G['gp_srchfrom']);
$before = intval($_G['gp_before']);
$srchfid = $_G['gp_srchfid'];
$srhfid = intval($_G['gp_srhfid']);

$keyword = isset($srchtxt) ? htmlspecialchars(trim($srchtxt)) : '';

$forumselect = forumselect('', '', '', TRUE);
if(!empty($srchfid) && !is_numeric($srchfid)) {
	$forumselect = str_replace('<option value="'.$srchfid.'">', '<option value="'.$srchfid.'" selected="selected">', $forumselect);
}

if($_G['setting']['my_search_status'] && $_G['setting']['my_search_progress'] && !$srchfrom && !$searchid) {
	header("Location: search.php?mod=my&q=".urlencode($keyword).(intval($srhfid) ? "&fId=$srhfid" : '')."&module=forum".($_G['gp_adv'] ? "&isAdv=1" : ''));
	die;
}

if(!submitcheck('searchsubmit', 1)) {

	include template('search/forum');

} else {
	$orderby = in_array($_G['gp_orderby'], array('dateline', 'replies', 'views')) ? $_G['gp_orderby'] : 'lastpost';
	$ascdesc = isset($_G['gp_ascdesc']) && $_G['gp_ascdesc'] == 'asc' ? 'asc' : 'desc';

	if(!empty($searchid)) {

		require_once libfile('function/misc');

		$page = max(1, intval($_G['gp_page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$index = DB::fetch_first("SELECT searchstring, keywords, num, ids FROM ".DB::table('common_searchindex')." WHERE searchid='$searchid' AND srchmod='$srchmod'");
		if(!$index) {
			showmessage('search_id_invalid');
		}

		$keyword = htmlspecialchars($index['keywords']);
		$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

		$index['keywords'] = rawurlencode($index['keywords']);
		$searchstring = explode('|', $index['searchstring']);
		$index['searchtype'] = $searchstring[0];//preg_replace("/^([a-z]+)\|.*/", "\\1", $index['searchstring']);
		$srchuname = $searchstring[3];

		$threadlist = array();
		$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE tid IN ($index[ids]) AND displayorder>='0' ORDER BY $orderby $ascdesc LIMIT $start_limit, $_G[tpp]");
		while($thread = DB::fetch($query)) {
			$thread['subject'] = bat_highlight($thread['subject'], $keyword);
			$threadlist[$thread['tid']] = procthread($thread, 'dt');
		}
		if($threadlist) {
			$tids = implode(',', array_keys($threadlist));
			$query = DB::query("SELECT tid, message FROM ".DB::table('forum_post')." WHERE tid IN ($tids) AND first='1'");
			while($post = DB::fetch($query)) {
				$threadlist[$post['tid']]['message'] = bat_highlight(messagecutstr($post['message'], 200), $keyword);
			}
		}
		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=forum&'.$_SERVER['QUERY_STRING'];

		$fulltextchecked = $searchstring[1] == 'fulltext' ? 'checked="checked"' : '';

		include template('search/forum');

	} else {

		$srchuname = isset($_G['gp_srchuname']) ? trim($_G['gp_srchuname']) : '';

		if($_G['group']['allowsearch'] & 32 && $srchtype == 'fulltext') {
			periodscheck('searchbanperiods');
		} elseif($srchtype != 'title') {
			$srchtype = 'title';
		}

		$forumsarray = array();
		if(!empty($srchfid)) {
			foreach((is_array($srchfid) ? $srchfid : explode('_', $srchfid)) as $forum) {
				if($forum = intval(trim($forum))) {
					$forumsarray[] = $forum;
				}
			}
		}

		$fids = $comma = '';
		foreach($_G['cache']['forums'] as $fid => $forum) {
			if($forum['type'] != 'group' && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
				if(!$forumsarray || in_array($fid, $forumsarray)) {
					$fids .= "$comma'$fid'";
					$comma = ',';
				}
			}
		}

		if($_G['setting']['threadplugins'] && $specialplugin) {
			$specialpluginstr = implode("','", $specialplugin);
			$special[] = 127;
		} else {
			$specialpluginstr = '';
		}
		$special = $_G['gp_special'];
		$specials = $special ? implode(',', $special) : '';
		$srchfilter = in_array($_G['gp_srchfilter'], array('all', 'digest', 'top')) ? $_G['gp_srchfilter'] : 'all';

		$searchstring = 'forum|'.$srchtype.'|'.addslashes($srchtxt).'|'.intval($srchuid).'|'.$srchuname.'|'.addslashes($fids).'|'.intval($srchfrom).'|'.intval($before).'|'.$srchfilter.'|'.$specials.'|'.$specialpluginstr;
		$searchindex = array('id' => 0, 'dateline' => '0');

		$query = DB::query("SELECT searchid, dateline,
			('".$_G['setting']['search']['forum']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<'".$_G['setting']['search']['forum']['searchctrl']."') AS flood,
			(searchstring='$searchstring' AND expiration>'$_G[timestamp]') AS indexvalid
			FROM ".DB::table('common_searchindex')."
			WHERE srchmod='$srchmod' AND ('".$_G['setting']['search']['forum']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<".$_G['setting']['search']['forum']['searchctrl'].") OR (searchstring='$searchstring' AND expiration>'$_G[timestamp]')
			ORDER BY flood");

		while($index = DB::fetch($query)) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=forum', array('searchctrl' => $_G['setting']['search']['forum']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname && !$srchfrom && !in_array($srchfilter, array('digest', 'top')) && !is_array($special)) {
				dheader('Location: search.php?mod=forum');
			} elseif(isset($srchfid) && !empty($srchfid) && $srchfid != 'all' && !(is_array($srchfid) && in_array('all', $srchfid)) && empty($forumsarray)) {
				showmessage('search_forum_invalid', 'search.php?mod=forum');
			} elseif(!$fids) {
				showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['forum']['maxspm']) {
				if((DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_searchindex')." WHERE srchmod='$srchmod' AND dateline>'$_G[timestamp]'-60")) >= $_G['setting']['search']['forum']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=forum', array('maxspm' => $_G['setting']['search']['forum']['maxspm']));
				}
			}

			if($srchtype == 'fulltext' && $_G['setting']['sphinxon']) {
				require_once libfile('class/sphinx');

				$s = new SphinxClient();
				$s->setServer($_G['setting']['sphinxhost'], intval($_G['setting']['sphinxport']));
				$s->setMaxQueryTime(intval($_G['setting']['sphinxmaxquerytime']));
				$s->SetRankingMode($_G['setting']['sphinxrank']);
				$s->setLimits(0, intval($_G['setting']['sphinxlimit']), intval($_G['setting']['sphinxlimit']));
				$s->setGroupBy('tid', SPH_GROUPBY_ATTR);

				if($srchfilter == 'digest') {
					$s->setFilterRange('digest', 1, 3, false);
				}
				if($srchfilter == 'top') {
					$s->setFilterRange('displayorder', 1, 2, false);
				} else {
					$s->setFilterRange('displayorder', 0, 2, false);
				}

				if(!empty($srchfrom) && empty($srchtxt) && empty($srchuid) && empty($srchuname)) {
					$expiration = TIMESTAMP + $cachelife_time;
					$keywords = '';
					if($before) {
						$spx_timemix = 0;
						$spx_timemax = TIMESTAMP - $srchfrom;
					} else {
						$spx_timemix = TIMESTAMP - $srchfrom;
						$spx_timemax = TIMESTAMP;
					}
				} else {
					$uids = array();
					if($srchuname) {
						$srchuname = str_replace('*', '%', addcslashes($srchuname, '%_'));
						$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username LIKE '".str_replace('_', '\_', $srchuname)."' LIMIT 50");
						while($member = DB::fetch($query)) {
							$uids[] = $member['uid'];
						}
						if(count($uids) == 0) {
							$uids = array(0);
						}
					} elseif($srchuid) {
						$uids = array($srchuid);
					}
					if(is_array($uids) && count($uids) > 0) {
						$s->setFilter('authorid', $uids, false);
					}

					if($srchtxt) {
						if(preg_match("/\".*\"/", $srchtxt)) {
							$spx_matchmode = "PHRASE";
							$s->setMatchMode(SPH_MATCH_PHRASE);
						} elseif(preg_match("(AND|\+|&|\s)", $srchtxt) && !preg_match("(OR|\|)", $srchtxt)) {
							$srchtxt = preg_replace("/( AND |&| )/is", "+", $srchtxt);
							$spx_matchmode = "ALL";
							$s->setMatchMode(SPH_MATCH_ALL);
						} else {
							$srchtxt = preg_replace("/( OR |\|)/is", "+", $srchtxt);
							$spx_matchmode = 'ANY';
							$s->setMatchMode(SPH_MATCH_ANY);
						}
						$srchtxt = str_replace('*', '%', addcslashes($srchtxt, '%_'));
						foreach(explode('+', $srchtxt) as $text) {
							$text = trim($text);
							if($text) {
								$sqltxtsrch .= $andor;
								$sqltxtsrch .= $srchtype == 'fulltext' ? "(p.message LIKE '%".str_replace('_', '\_', $text)."%' OR p.subject LIKE '%$text%')" : "t.subject LIKE '%$text%'";
							}
						}
						$sqlsrch .= " AND ($sqltxtsrch)";
					}

					if(!empty($srchfrom)) {
						if($before) {
							$spx_timemix = 0;
							$spx_timemax = TIMESTAMP - $srchfrom;
						} else {
							$spx_timemix = TIMESTAMP - $srchfrom;
							$spx_timemax = TIMESTAMP;
						}
						$s->setFilterRange('lastpost', $spx_timemix, $spx_timemax, false);
					}
					if(!empty($specials)) {
						$s->setFilter('special', explode(",", $special), false);
					}

					$keywords = str_replace('%', '+', $srchtxt).(trim($srchuname) ? '+'.str_replace('%', '+', $srchuname) : '');
					$expiration = TIMESTAMP + $cachelife_text;

				}
				if($srchtype == "fulltext") {
					$result = $s->query("'".$srchtxt."'", $_G['setting']['sphinxmsgindex']);
				} else {
					$result = $s->query($srchtxt, $_G['setting']['sphinxsubindex']);
				}
				$tids = array();
				if($result) {
					if(is_array($result['matches'])) {
						foreach($result['matches'] as $value) {
							if($value['attrs']['tid']) {
								$tids[$value['attrs']['tid']] = $value['attrs']['tid'];
							}
						}
					}
				}
				if(count($tids) == 0) {
					$ids = 0;
					$num = 0;
				} else {
					$ids = implode(",", $tids);
					$num = $result['total_found'];
				}
			} else {
				$digestltd = $srchfilter == 'digest' ? "t.digest>'0' AND" : '';
				$topltd = $srchfilter == 'top' ? "AND t.displayorder>'0'" : "AND t.displayorder>='0'";

				if(!empty($srchfrom) && empty($srchtxt) && empty($srchuid) && empty($srchuname)) {

					$searchfrom = $before ? '<=' : '>=';
					$searchfrom .= TIMESTAMP - $srchfrom;
					$sqlsrch = "FROM ".DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd AND t.lastpost$searchfrom";
					$expiration = TIMESTAMP + $cachelife_time;
					$keywords = '';

				} else {

					$sqlsrch = $srchtype == 'fulltext' ?
					"FROM ".DB::table('forum_post')." p, ".DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd AND p.tid=t.tid AND p.invisible='0'" :
					"FROM ".DB::table('forum_thread')." t WHERE isgroup='0' AND $digestltd t.fid IN ($fids) $topltd";

					if($srchuname) {
						$srchuid = $comma = '';
						$srchuname = str_replace('*', '%', addcslashes($srchuname, '%_'));
						$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username LIKE '".str_replace('_', '\_', $srchuname)."' LIMIT 50");
						while($member = DB::fetch($query)) {
							$srchuid .= "$comma'$member[uid]'";
							$comma = ', ';
						}
						if(!$srchuid) {
							$sqlsrch .= ' AND 0';
						}
					} elseif($srchuid) {
						$srchuid = "'$srchuid'";
					}

					if($srchtxt) {
						if(preg_match("(AND|\+|&|\s)", $srchtxt) && !preg_match("(OR|\|)", $srchtxt)) {
							$andor = ' AND ';
							$sqltxtsrch = '1';
							$srchtxt = preg_replace("/( AND |&| )/is", "+", $srchtxt);
						} else {
							$andor = ' OR ';
							$sqltxtsrch = '0';
							$srchtxt = preg_replace("/( OR |\|)/is", "+", $srchtxt);
						}
						$srchtxt = str_replace('*', '%', addcslashes($srchtxt, '%_'));
						foreach(explode('+', $srchtxt) as $text) {
							$text = trim($text);
							if($text) {
								$sqltxtsrch .= $andor;
								$sqltxtsrch .= $srchtype == 'fulltext' ? "(p.message LIKE '%".str_replace('_', '\_', $text)."%' OR p.subject LIKE '%$text%')" : "t.subject LIKE '%$text%'";
							}
						}
						$sqlsrch .= " AND ($sqltxtsrch)";
					}

					if($srchuid) {
						$sqlsrch .= ' AND '.($srchtype == 'fulltext' ? 'p' : 't').".authorid IN ($srchuid)";
					}

					if(!empty($srchfrom)) {
						$searchfrom = ($before ? '<=' : '>=').(TIMESTAMP - $srchfrom);
						$sqlsrch .= " AND t.lastpost$searchfrom";
					}

					if(!empty($specials)) {
						$sqlsrch .=  " AND special IN (".dimplode($special).")";
					}

					$keywords = str_replace('%', '+', $srchtxt);
					$expiration = TIMESTAMP + $cachelife_text;

				}

				$num = $ids = 0;
				$_G['setting']['search']['forum']['maxsearchresults'] = $_G['setting']['search']['forum']['maxsearchresults'] ? intval($_G['setting']['search']['forum']['maxsearchresults']) : 500;
				$query = DB::query("SELECT ".($srchtype == 'fulltext' ? 'DISTINCT' : '')." t.tid, t.closed, t.author, t.authorid $sqlsrch ORDER BY tid DESC LIMIT ".$_G['setting']['search']['forum']['maxsearchresults']);
				while($thread = DB::fetch($query)) {
					if($thread['closed'] <= 1) {
						$ids .= ','.$thread['tid'];
						$num++;
					}
				}
				DB::free_result($query);
			}

			DB::query("INSERT INTO ".DB::table('common_searchindex')." (srchmod, keywords, searchstring, useip, uid, dateline, expiration, num, ids)
					VALUES ('$srchmod', '$keywords', '$searchstring', '$_G[clientip]', '$_G[uid]', '$_G[timestamp]', '$expiration', '$num', '$ids')");
			$searchid = DB::insert_id();

			!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

	}

}

?>