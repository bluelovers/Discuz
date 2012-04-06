<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_forum.php 23830 2011-08-11 05:34:27Z zhouguoqiang $
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
loadcache(array('forums', 'posttable_info'));
$posttableselect = '';
if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
	$posttableselect = '<select name="seltableid" id="seltableid" class="ps" style="display:none">';
	foreach($_G['cache']['posttable_info'] as $posttableid => $data) {
		$posttableselect .= '<option value="'.$posttableid.'"'.($_G['gp_posttableid'] == $posttableid ? ' selected="selected"' : '').'>'.($data['memo'] ? $data['memo'] : 'post_'.$posttableid).'</option>';
	}
	$posttableselect .= '</select>';
}

$srchmod = 2;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_G['gp_srchtype']) ? '' : trim($_G['gp_srchtype']);
$searchid = isset($_G['gp_searchid']) ? intval($_G['gp_searchid']) : 0;
$seltableid = intval($_G['gp_seltableid']);

if($srchtype != 'title' && $srchtype != 'fulltext') {
	$srchtype = '';
}

$srchtxt = trim($_G['gp_srchtxt']);
$srchuid = intval($_G['gp_srchuid']);
$srchuname = isset($_G['gp_srchuname']) ? trim(str_replace('|', '', $_G['gp_srchuname'])) : '';;
$srchfrom = intval($_G['gp_srchfrom']);
$before = intval($_G['gp_before']);
$srchfid = $_G['gp_srchfid'];
$srhfid = intval($_G['gp_srhfid']);

$keyword = isset($srchtxt) ? htmlspecialchars(trim($srchtxt)) : '';

$forumselect = forumselect();
if(!empty($srchfid) && !is_numeric($srchfid)) {
	$forumselect = str_replace('<option value="'.$srchfid.'">', '<option value="'.$srchfid.'" selected="selected">', $forumselect);
}

$my_search_data = unserialize($_G['setting']['my_search_data']);
require_once libfile('function/cloud');
if($my_search_data['status'] && getcloudappstatus('search') && !$srchfrom && !$searchid) {
	if (!$_G['setting']['my_siteid']) {
		dheader('Location: index.php');
	}
	$source = '';
	if(!empty($_G['gp_srhlocality'])) {
		$sourcetype = explode('::', $_G['gp_srhlocality']);
		if($sourcetype[0] == 'forum') {
			$source = $sourcetype[1] == 'index' ? 'forum' : ($sourcetype[1] == 'forumdisplay' ? 'forum_forum' : 'forum_thread');
		} elseif($sourcetype[0] == 'portal') {
			$source = $sourcetype[1] == 'view' ? 'article' : ($sourcetype[1] == 'list' ? 'portal_list': 'portal');
		} elseif($sourcetype[0] == 'group') {
			$source = $sourcetype[1] == 'viewthread' ? 'group_thread' : ($sourcetype[1] == 'group' ? 'group_forum' : 'group');
		} elseif($sourcetype[0] == 'home') {
			$source = 'home'.(empty($sourcetype[1]) ? '' : '_'.$sourcetype[1]);
		} elseif($sourcetype[0] == 'misc' && $sourcetype[1] == 'ranklist') {
			$source = 'toplist';
		}
	} elseif($_G['gp_source'] == 'hotsearch') {
		$source = 'hotsearch';
	}

	require_once libfile('function/search');
	$forum_hash = search_get_forum_hash();

	$my_extgroupids = array();
	$_extgroupids = explode("\t", $_G['member']['extgroupids']);
	foreach($_extgroupids as $v) {
		if ($v) {
			$my_extgroupids[] = $v;
		}
	}
	$my_extgroupids_str = implode(',', $my_extgroupids);

	$groupIds = explode(',', $_G['groupid']);
	if ($_G['adminid']) {
		$groupIds[] = $_G['adminid'];
	}
	if ($my_extgroupids) {
		$groupIds = array_merge($groupIds, $my_extgroupids);
	}
	$groupIds = array_unique($groupIds);
	$userGroups = search_get_usergroups($groupIds);

	$params = array(
					'cuName' => $_G['username'],
					'gId' => $_G['groupid'],
					'agId' => $_G['adminid'],
					'egIds' => $my_extgroupids_str,
					'fmSign' => substr($forum_hash, -4),
				   );

	foreach($groupIds as $k => $v) {
		$value =  substr($userGroups[$v]['sign'], -4);
		if ($value) {
			$params['ugSign' . $v] = $value;
		}
	}
	$params['charset'] = $_G['charset'];

	$params['q'] = $keyword;
	$params['module'] = 'forum';
	if($srhfid) {
		$params['fId'] = $srhfid;
	}
	if($_G['gp_adv']) {
		$params['isAdv'] = 1;
	}
	if(!empty($source)) {
		$params['source']=$source;
	}
	if(!empty($_G['gp_author'])) {
		$params['author']=$_G['gp_author'];
	}
	if(!empty($_G['gp_scope'])) {
		$params['scope']=$_G['gp_scope'];
	}
	$mySearchData = unserialize($_G['setting']['my_search_data']);
	if ($mySearchData['domain']) {
		$domain = $mySearchData['domain'];
	} else {
		$domain = 'search.discuz.qq.com';
	}
	$url = 'http://' . $domain . '/f/discuz?' . generateSiteSignUrl($params, true, true);
	dheader('Location: ' . $url);
	die;
}

if(!submitcheck('searchsubmit', 1)) {

	if($_G['gp_adv']) {
		include template('search/forum_adv');
	} else {
		include template('search/forum');
	}

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
		$searchstring[2] = base64_decode($searchstring[2]);
		$srchuname = $searchstring[3];
		$modfid = 0;
		if($keyword) {
			$modkeyword = str_replace(' ', ',', $keyword);
			$fids = explode(',', str_replace('\'', '', $searchstring[5]));
			if(count($fids) == 1 && in_array($_G['adminid'], array(1,2,3))) {
				$modfid = $fids[0];
				if($_G['adminid'] == 3 && !DB::result_first("SELECT uid FROM ".DB::table('forum_moderator')." WHERE uid='$_G[uid]' AND fid='$modfid'")) {
					$modfid = 0;
				}
			}
		}
		$threadlist = $posttables = array();
		$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE tid IN ($index[ids]) AND displayorder>='0' ORDER BY $orderby $ascdesc LIMIT $start_limit, $_G[tpp]");
		while($thread = DB::fetch($query)) {
			$thread['subject'] = bat_highlight($thread['subject'], $keyword);
			$thread['realtid'] = $thread['isgroup'] == 1 ? $thread['closed'] : $thread['tid'];
			$threadlist[$thread['tid']] = procthread($thread, 'dt');
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
		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=forum&'.$_SERVER['QUERY_STRING'];

		$fulltextchecked = $searchstring[1] == 'fulltext' ? 'checked="checked"' : '';

		include template('search/forum');

	} else {


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

		$searchstring = 'forum|'.$srchtype.'|'.base64_encode($srchtxt).'|'.intval($srchuid).'|'.$srchuname.'|'.addslashes($fids).'|'.intval($srchfrom).'|'.intval($before).'|'.$srchfilter.'|'.$specials.'|'.$specialpluginstr.'|'.$seltableid;
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
					"FROM ".DB::table(getposttable($seltableid))." p, ".DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd AND p.tid=t.tid AND p.invisible='0'" :
					"FROM ".DB::table('forum_thread')." t WHERE $digestltd t.fid IN ($fids) $topltd";
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
						$srcharr = $srchtype == 'fulltext' ? searchkey($keyword, "(p.message LIKE '%{text}%' OR p.subject LIKE '%{text}%')", true) : searchkey($keyword,"t.subject LIKE '%{text}%'", true);
						$srchtxt = $srcharr[0];
						$sqlsrch .= $srcharr[1];
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
					$ids .= ','.$thread['tid'];
					$num++;
				}
				DB::free_result($query);
			}

			DB::query("INSERT INTO ".DB::table('common_searchindex')." (srchmod, keywords, searchstring, useip, uid, dateline, expiration, num, ids)
					VALUES ('$srchmod', '$keywords', '$searchstring', '$_G[clientip]', '$_G[uid]', '$_G[timestamp]', '$expiration', '$num', '$ids')");
			$searchid = DB::insert_id();

			!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=forum&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>