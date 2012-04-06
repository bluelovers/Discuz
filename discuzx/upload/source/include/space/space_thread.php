<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_thread.php 23183 2011-06-23 06:03:32Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$opactives['thread'] = 'class="a"';

if(empty($_G['gp_view'])) $_G['gp_view'] = 'me';
$_G['gp_order'] = empty($_G['gp_order']) ? 'dateline' : $_G['gp_order'];

$allowviewuserthread = $_G['setting']['allowviewuserthread'];

$perpage = 20;
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$hiddennum = $count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'thread',
	'view' => $_G['gp_view'],
	'type' => $_GET['type'],
	'order' => $_G['gp_order'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey'],
	'from' => $_GET['from']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

require_once libfile('function/misc');
require_once libfile('function/forum');
loadcache(array('forums'));
$fids = $comma = '';
$wheresql = $_G['gp_view'] == 'me' || !$allowviewuserthread ? '1' : " t.fid IN(".$allowviewuserthread.")";
$wheresql .= $_G['gp_view'] != 'me' ? " AND t.displayorder>='0'" : '';
$f_index = '';
$ordersql = 't.dateline DESC';
$need_count = true;
$viewuserthread = false;
if($_G['gp_view'] == 'all') {
	$start = 0;
	$perpage = 100;
	$alltype = 'dateline';
	loadcache('space_thread');
	if($_G['gp_order'] == 'hot') {
		$wheresql .= " AND t.replies>='$minhot'";
		$alltype = 'hot';
	} else {
		$pruneperm = 0;
		if(($_G['adminid'] == 1 || $_G['adminid'] == 2)) {
			if(in_array($_G['uid'], explode(',', $_G['config']['admincp']['founder'])) || in_array($_G['username'], explode(',', $_G['config']['admincp']['founder']))) {
				$pruneperm = 1;
			} elseif(DB::result_first("SELECT ap.perm FROM ".DB::table('common_admincp_member')." am LEFT JOIN ".DB::table('common_admincp_perm')." ap ON ap.cpgroupid=am.cpgroupid WHERE am.uid='$_G[uid]' AND ap.perm='prune'")) {
				$pruneperm = 1;
			}
		}
		if(submitcheck('delthread') && $pruneperm) {
			require_once libfile('function/post');
			$moderate = $_G['gp_moderate'];
			$tidsadd = 'tid IN ('.dimplode($moderate).')';
			$tuidarray = $ruidarray = $fids = $posttablearr = array();
			$query = DB::query('SELECT tid, posttableid FROM '.DB::table('forum_thread').' WHERE '.$tidsadd);
			while($value = DB::fetch($query)) {
				$posttablearr[$value['posttableid'] ? $value['posttableid'] : 0][] = $value['tid'];
			}
			foreach($posttablearr as $posttableid => $ids) {
				$query = DB::query('SELECT fid, first, authorid FROM '.DB::table(getposttable($posttableid)).' WHERE tid IN ('.dimplode($ids).')');
				while($post = DB::fetch($query)) {
					if($post['first']) {
						$tuidarray[$post['fid']][] = $post['authorid'];
					} else {
						$ruidarray[$post['fid']][] = $post['authorid'];
					}
					$fids[$post['fid']] = $post['fid'];
				}
			}
			if($tuidarray) {
				foreach($tuidarray as $fid => $uids) {
					$_G['fid'] = $fid;
					updatepostcredits('-', $uids, 'post');
				}
			}
			if($ruidarray) {
				foreach($ruidarray as $fid => $uids) {
					$_G['fid'] = $fid;
					updatepostcredits('-', $uids, 'reply');
				}
			}

			require_once libfile('function/delete');
			deletethread($moderate);
			DB::query("DELETE FROM ".DB::table('forum_postcomment')." WHERE $tidsadd AND authorid='$space[uid]'");

			foreach($fids as $fid) {
				updateforumcount(intval($fid));
			}

			foreach($moderate as $tid) {
				my_thread_log('delete', array('tid' => $tid));
			}
			$_G['cache']['space_thread'][$alltype] = array();
			save_syscache('space_thread', $_G['cache']['space_thread']);

			showmessage('thread_delete_succeed', 'home.php?mod=space&uid='.$space['uid'].'&do=thread&view=all');
		}
	}
	$orderactives = array($_G['gp_order'] => ' class="a"');

} elseif($_G['gp_view'] == 'me') {

	if($_GET['from'] == 'space') $diymode = 1;

	$viewtype = in_array($_G['gp_type'], array('reply', 'thread', 'postcomment')) ? $_G['gp_type'] : 'thread';
	$filter = in_array($_G['gp_filter'], array('recyclebin', 'ignored', 'save', 'aduit', 'close', 'common')) ? $_G['gp_filter'] : '';
	if($viewtype == 'thread') {
		$statusfield = 'displayorder';
		$wheresql .= " AND t.authorid = '$space[uid]'";
		if($filter == 'recyclebin') {
			$wheresql .= " AND t.displayorder='-1'";
		} elseif($filter == 'aduit') {
			$wheresql .= " AND t.displayorder='-2'";
		} elseif($filter == 'ignored') {
			$wheresql .= " AND t.displayorder='-3'";
		} elseif($filter == 'save') {
			$wheresql .= " AND t.displayorder='-4'";
		} elseif($filter == 'close') {
			$wheresql .= " AND t.closed='1'";
		} elseif($filter == 'common') {
			$wheresql .= " AND t.displayorder>='0' AND t.closed='0'";
		} elseif($space['uid'] != $_G['uid']) {
			if($allowviewuserthread === false && $_G['adminid'] != 1) {
				showmessage('ban_view_other_thead');
			}
			$viewuserthread = true;
			$viewfids = str_replace("'", '', $allowviewuserthread);
			if(!empty($viewfids)) {
				$viewfids = explode(',', $viewfids);
			}
		}
		$ordersql = 't.tid DESC';
	} elseif($viewtype == 'postcomment') {
		$posttable = getposttable();
		require_once libfile('function/post');
		$query = DB::query("SELECT c.*, p.authorid, p.tid, p.pid, p.fid, p.invisible, p.dateline, p.message, t.special, t.status, t.subject, t.digest,t.attachment, t.replies, t.views, t.lastposter, t.lastpost
			FROM ".DB::table('forum_postcomment')." c
			LEFT JOIN ".DB::table($posttable)." p ON p.pid = c.pid
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid = c.tid
			WHERE c.authorid = '$space[uid]' ORDER BY c.dateline DESC LIMIT $start, $perpage");

		$list = $fids = array();
		while($value = DB::fetch($query)) {
			$fids[] = $value['fid'];
			$value['comment'] = messagecutstr($value['comment'], 100);
			$list[] = procthread($value);
		}
		if($fids) {
			$fids = array_unique($fids);
			$query = DB::query("SELECT fid, name FROM ".DB::table('forum_forum')." WHERE fid IN (".dimplode($fids).")");
			while($forum = DB::fetch($query)) {
				$forums[$forum['fid']] = $forum['name'];
			}
		}

		$multi = simplepage(count($list), $perpage, $page, $theurl);
		$need_count = false;

	} else {
		$statusfield = 'invisible';
		$postsql = $threadsql = '';
		if($filter == 'recyclebin') {
			$postsql .= " AND p.invisible='-5'";
		} elseif($filter == 'aduit') {
			$postsql .= " AND p.invisible='-2'";
		} elseif($filter == 'save') {
			$postsql .= " AND p.invisible='-3' AND t.displayorder='-4'";
		} elseif($filter == 'ignored') {
			$postsql .= " AND p.invisible='-3' AND t.displayorder!='-4'";
		} elseif($filter == 'close') {
			$threadsql .= " AND t.closed='1'";
		} elseif($filter == 'common') {
			$postsql .= " AND p.invisible='0'";
			$threadsql .= " AND t.displayorder>='0' AND t.closed='0'";
		} elseif($space['uid'] != $_G['uid']) {
			if($allowviewuserthread === false && $_G['adminid'] != 1) {
				showmessage('ban_view_other_thead');
			}
			$threadsql .= empty($allowviewuserthread) ? '' : " AND t.fid IN($allowviewuserthread) ";
		}
		$postsql .= " AND p.first='0'";
		$posttable = getposttable();

		require_once libfile('function/post');
		$query = DB::query("SELECT p.authorid, p.tid, p.pid, p.fid, p.invisible, p.dateline, p.message, t.special, t.status, t.subject, t.digest,t.attachment, t.replies, t.views, t.lastposter, t.lastpost, t.displayorder FROM ".DB::table($posttable)." p
		INNER JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid $threadsql
		WHERE p.authorid='$space[uid]' $postsql ORDER BY p.dateline DESC LIMIT $start,$perpage");

		$list = $fids = array();
		while($value = DB::fetch($query)) {
			$fids[] = $value['fid'];
			$value['message'] = !getstatus($value['status'], 2) || $value['authorid'] == $_G['uid'] ? messagecutstr($value['message'], 100) : '';
			$list[] = procthread($value) ;
			$tids[$value['tid']] = $value['tid'];
		}
		if($fids) {
			$fids = array_unique($fids);
			$query = DB::query("SELECT fid, name, status FROM ".DB::table('forum_forum')." WHERE fid IN (".dimplode($fids).")");
			while($forum = DB::fetch($query)) {
				if(!$_G['setting']['groupstatus'] && $forum['status'] == 3) {
				} else {
					$forums[$forum['fid']] = $forum['name'];
				}
			}
			foreach($list as $key => $val) {
				if(!$forums[$val['fid']]) {
					unset($list[$key]);
				}
			}
		}

		$multi = simplepage(count($list), $perpage, $page, $theurl);

		$need_count = false;
	}
	$orderactives = array($viewtype => ' class="a"');

} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {

		$fuid_actives = array();

		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$wheresql .= " AND t.authorid='$fuid'";
			$fuid_actives = array($fuid=>' selected');
		} else {
			$wheresql .= " AND t.authorid IN ($space[feedfriend])";
			$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&view=we";
		}

		$query = DB::query("SELECT * FROM ".DB::table('home_friend')." WHERE uid='$_G[uid]' ORDER BY num DESC LIMIT 0,100");
		while ($value = DB::fetch($query)) {
			$userlist[] = $value;
		}
	} else {
		$need_count = false;
	}
}

$actives = array($_G['gp_view'] =>' class="a"');

if($need_count) {

	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wheresql .= " AND t.subject LIKE '%$searchkey%'";
		$searchkey = dhtmlspecialchars($searchkey);
	}

	$havecache = false;
	if($_G['gp_view'] == 'all') {

		$cachetime = $_G['gp_order'] == 'hot' ? 43200 : 3000;
		if(!empty($_G['cache']['space_thread'][$alltype]) && is_array($_G['cache']['space_thread'][$alltype])) {
			$threadarr = $_G['cache']['space_thread'][$alltype];
			if(!empty($threadarr['dateline']) && $threadarr['dateline'] > $_G['timestamp'] - $cachetime) {
				$list = $threadarr['data'];
				$forums = $threadarr['forums'];
				$hiddennum = $threadarr['hiddennum'];
				$havecache = true;
			}
		}
	}
	if(!$havecache) {
		$query = DB::query("SELECT t.* FROM ".DB::table('forum_thread')." t WHERE $wheresql ORDER BY $ordersql LIMIT $start,$perpage");
		$fids = $forums = array();
		while($value = DB::fetch($query)) {
			if(empty($value['author']) && $value['authorid'] != $_G['uid']) {
				$hiddennum++;
				continue;
			}
			if($viewuserthread && $value['authorid'] != $_G['uid']) {
				if(($_G['adminid'] != 1 && !empty($viewfids) && !in_array($value['fid'], $viewfids)) || $value['displayorder'] < 0) {
					$hiddennum++;
					continue;
				}
			}

			$fids[] = $value['fid'];
			$list[] = procthread($value);
		}
		if($fids) {
			$fids = array_unique($fids);
			$query = DB::query("SELECT fid, name, status FROM ".DB::table('forum_forum')." WHERE fid IN (".dimplode($fids).")");
			while($forum = DB::fetch($query)) {
				if(!$_G['setting']['groupstatus'] && $forum['status'] == 3) {
				} else {
					$forums[$forum['fid']] = $forum['name'];
				}
			}
		}
		foreach($list as $key => $val) {
			if(!$forums[$val['fid']] || $val['closed'] > 1) {
				unset($list[$key]);
				$hiddennum++;
			}
		}
		if($_G['gp_view'] == 'all') {
			$_G['cache']['space_thread'][$alltype] = array(
				'dateline' => $_G['timestamp'],
				'hiddennum' => $hiddennum,
				'forums' => $forums,
				'data' => $list
			);
			save_syscache('space_thread', $_G['cache']['space_thread']);
		}
	}

	if($_G['gp_view'] != 'all') {
		$multi = simplepage(count($list)+$hiddennum, $perpage, $page, $theurl);
	}
}

dsetcookie('home_diymode', $diymode);

if($_G['uid']) {
	$_G['gp_view'] = !$_G['gp_view'] ? 'we' : $_G['gp_view'];
	$navtitle = lang('core', 'title_'.$_G['gp_view'].'_thread');
} else {
	$navtitle = lang('core', 'title_thread');
}

if($space['username']) {
	$navtitle = lang('space', 'sb_thread', array('who' => $space['username']));
}
$metakeywords = $navtitle;
$metadescription = $navtitle;

include_once template("diy:home/space_thread");

?>