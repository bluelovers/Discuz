<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_prune.php 24433 2011-09-20 01:30:33Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$searchsubmit = $_G['gp_searchsubmit'];
$fromumanage = $_G['gp_fromumanage'] ? 1 : 0;

require_once libfile('function/misc');
loadcache('forums');

if(!submitcheck('prunesubmit')) {

	require_once libfile('function/forumlist');

	if($_G['adminid'] == 1 || $_G['adminid'] == 2) {
		$forumselect = '<select name="forums"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
			'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';

		if($_G['gp_forums']) {
			$forumselect = preg_replace("/(\<option value=\"$_G[gp_forums]\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
		}
	} else {
		$forumselect = $comma = '';
		$query = DB::query("SELECT f.name FROM ".DB::table('forum_moderator')." m, ".DB::table('forum_forum')." f WHERE m.uid='$_G[uid]' AND m.fid=f.fid");
		while($forum = DB::fetch($query)) {
			$forumselect .= $comma.$forum['name'];
			$comma = ', ';
		}
		$forumselect = $forumselect ? $forumselect : $lang['none'];
	}

	if($fromumanage) {
		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_G['gp_starttime']) ? '' : $_G['gp_starttime'];
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_G['gp_endtime']) ? '' : $_G['gp_endtime'];
	} else {
		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_G['gp_starttime']) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $_G['gp_starttime'];
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $_G['gp_endtime']) ? dgmdate(TIMESTAMP, 'Y-n-j') : $_G['gp_endtime'];
	}

	shownav('topic', 'nav_prune'.($operation ? '_'.$operation : ''));
	showsubmenusteps('nav_prune'.($operation ? '_'.$operation : ''), array(
		array('prune_search', !$searchsubmit),
		array('nav_prune', $searchsubmit)
	));
	showtips('prune_tips');
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('pruneforum').page.value=number;
	$('pruneforum').searchsubmit.click();
}
</script>
EOT;

	$posttableselect = getposttableselect();
	showtagheader('div', 'searchposts', !$searchsubmit);
	showformheader("prune".($operation ? '&operation='.$operation : ''), '', 'pruneforum');
	showhiddenfields(array('page' => $page, 'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']));
	showtableheader();
	showsetting('prune_search_detail', 'detail', $_G['gp_detail'], 'radio');
	if($posttableselect) {
		showsetting('prune_search_select_postsplit', '', '', $posttableselect);
	}
	if($operation != 'group') {
		showsetting('prune_search_forum', '', '', $forumselect);
	}
	showsetting('prune_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	if(!$fromumanage) {
		empty($_G['gp_starttime']) && $_G['gp_starttime'] = dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j');
	}
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsetting('prune_search_time', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
	showsetting('prune_search_user', 'users', $_G['gp_users'], 'text');
	showsetting('prune_search_ip', 'useip', $_G['gp_useip'], 'text');
	showsetting('prune_search_keyword', 'keywords', $_G['gp_keywords'], 'text');
	showsetting('prune_search_lengthlimit', 'lengthlimit', $_G['gp_lengthlimit'], 'text');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {

	$pidsdelete = $tidsdelete = array();
	$pids = authcode($_G['gp_pids'], 'DECODE');
	$pidsadd = $pids ? 'pid IN ('.$pids.')' : 'pid IN ('.dimplode($_G['gp_pidarray']).')';

	loadcache('posttableids');
	$posttable = in_array($_G['gp_posttableid'], $_G['cache']['posttableids']) ? $_G['gp_posttableid'] : 0;
	$query = DB::query("SELECT fid, tid, pid, first, authorid FROM ".DB::table(getposttable($posttable))." WHERE $pidsadd");

	while($post = DB::fetch($query)) {
		$prune['forums'][] = $post['fid'];
		$prune['thread'][$post['tid']]++;

		$pidsdelete[] = $post['pid'];
		if($post['first']) {
			$tidsdelete[] = $post['tid'];
		}
		if($post['first']) {
			my_thread_log('delete', array('tid' => $post['tid']));
		} else {
			my_post_log('delete', array('pid' => $post['pid']));
		}
	}

	if($pidsdelete) {
		require_once libfile('function/post');
		require_once libfile('function/delete');
		$deletedposts = deletepost($pidsdelete, 'pid', !$_G['gp_donotupdatemember'], $posttable);
		$deletedthreads = deletethread($tidsdelete, !$_G['gp_donotupdatemember'], !$_G['gp_donotupdatemember']);

		if(count($prune['thread']) < 50) {
			foreach($prune['thread'] as $tid => $decrease) {
				updatethreadcount($tid);
			}
		} else {
			$repliesarray = array();
			foreach($prune['thread'] as $tid => $decrease) {
				$repliesarray[$decrease][] = $tid;
			}
			foreach($repliesarray as $decrease => $tidarray) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies-'$decrease' WHERE tid IN (".implode(',', $tidarray).")");
			}
		}

		if($_G['setting']['globalstick']) {
			updatecache('globalstick');
		}

		foreach(array_unique($prune['forums']) as $fid) {
			updateforumcount($fid);
		}

	}

	$deletedthreads = intval($deletedthreads);
	$deletedposts = intval($deletedposts);
	updatemodworks('DLP', $deletedposts);
	$cpmsg = cplang('prune_succeed', array('deletedthreads' => $deletedthreads, 'deletedposts' => $deletedposts));

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('pruneforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1)) {

	loadcache('posttableids');
	$posttable = in_array($_G['gp_posttableid'], $_G['cache']['posttableids']) ? $_G['gp_posttableid'] : 0;

	$_G['gp_detail'] = !empty($_GET['users']) ? true : $_G['gp_detail'];
	$pids = $postcount = '0';
	$sql = $error = '';
	$operation == 'group' && $_G['gp_forums'] = 'isgroup';
	$_G['gp_keywords'] = trim($_G['gp_keywords']);
	$_G['gp_users'] = trim($_G['gp_users']);
	if(($_G['gp_starttime'] == '' && $_G['gp_endtime'] == '' && !$fromumanage) || ($_G['gp_keywords'] == '' && $_G['gp_useip'] == '' && $_G['gp_users'] == '')) {
		$error = 'prune_condition_invalid';
	}

	if($_G['adminid'] == 1 || $_G['adminid'] == 2) {
		if($_G['gp_forums'] && $_G['gp_forums'] != 'isgroup') {
			$sql .= " AND p.fid='{$_G['gp_forums']}'";
		}
		if($_G['gp_forums'] == 'isgroup') {
			$sql .= " AND t.isgroup='1'";
		} else {
			$sql .= " AND t.isgroup='0'";
		}
	} else {
		$forums = '0';
		$query = DB::query("SELECT fid FROM ".DB::table('forum_moderator')." WHERE uid='$_G[uid]'");
		while($forum = DB::fetch($query)) {
			$forums .= ','.$forum['fid'];
		}
		$sql .= " AND p.fid IN ($forums)";
	}

	if($_G['gp_users'] != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $_G['gp_users']))."')");
		while($member = DB::fetch($query)) {
			$uids .= ",$member[uid]";
		}
		$sql .= " AND p.authorid IN ($uids)";
	}
	if($_G['gp_useip'] != '') {
		$sql .= " AND p.useip LIKE '".str_replace('*', '%', $_G['gp_useip'])."'";
	}
	if($_G['gp_keywords'] != '') {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $_G['gp_keywords']));
		for($i = 0; $i < count($keywords); $i++) {
			if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
				$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
				$sqlkeywords .= " $or p.subject REGEXP '".$keywords[$i]."' OR p.message REGEXP '".$keywords[$i]."'";
			} else {
				$sqlkeywords .= " $or p.subject LIKE '%".$keywords[$i]."%' OR p.message LIKE '%".$keywords[$i]."%'";
			}
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	if($_G['gp_lengthlimit'] != '') {
		$lengthlimit = intval($_G['gp_lengthlimit']);
		$sql .= " AND LENGTH(p.message) < $lengthlimit";
	}

	if(!empty($_G['gp_starttime'])) {
		$starttime = strtotime($_G['gp_starttime']);
		$sql .= " AND p.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $_G['gp_endtime'] != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if(!empty($_G['gp_endtime'])) {
			$endtime = strtotime($_G['gp_endtime']);
			$sql .= " AND p.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}
	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'prune_mod_range_illegal';
	}

	if(!$error) {
		if($_G['gp_detail']) {
			$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
			$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
			$query = DB::query("SELECT p.fid, p.tid, p.pid, p.author, p.authorid, p.dateline, t.subject, p.message, t.isgroup
				FROM ".DB::table(getposttable($posttable))." p LEFT JOIN ".DB::table('forum_thread')." t USING(tid)
				WHERE 1 $sql
				LIMIT ".($page - 1) * $perpage.", {$perpage}");
			$posts = '';
			$groupsname = $groupsfid = $postlist = array();
			while($post = DB::fetch($query)) {
				if($post['isgroup']) {
					$groupsfid[$post[fid]] = $post['fid'];
				}
				$post['dateline'] = dgmdate($post['dateline']);
				$post['subject'] = cutstr($post['subject'], 30);
				$post['message'] = dhtmlspecialchars(cutstr($post['message'], 50));
				$postlist[] = $post;
			}
			if($groupsfid) {
				$query = DB::query("SELECT fid, name FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($groupsfid).")");
				while($row = DB::fetch($query)) {
					$groupsname[$row[fid]] = $row['name'];
				}
			}
			if($postlist) {
				foreach($postlist as $post) {
					$posts .= showtablerow('', '', array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"pidarray[]\" value=\"$post[pid]\" checked />",
						"<a href=\"forum.php?mod=redirect&goto=findpost&pid=$post[pid]&ptid=$post[tid]\" target=\"_blank\">$post[subject]</a>",
						$post['message'],
					"<a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\" target=\"_blank\">".(empty($post['isgroup']) ? $_G['cache']['forums'][$post[fid]]['name'] : $groupsname[$post[fid]])."</a>",
						"<a href=\"home.php?mod=space&uid=$post[authorid]\" target=\"_blank\">$post[author]</a>",
						$post['dateline']
					), TRUE);
				}
			}
			$postcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable($posttable))." p LEFT JOIN ".DB::table('forum_thread')." t USING(tid) WHERE 1 $sql");
			$multi = multi($postcount, $perpage, $page, ADMINSCRIPT."?action=prune");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=prune&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=prune&amp;page='+this.value", "page(this.value)", $multi);
		} else {
			$postcount = 0;
			$query = DB::query('SELECT pid FROM '.DB::table(getposttable($posttable))." p LEFT JOIN ".DB::table('forum_thread')." t USING(tid) WHERE 1 $sql");
			while($post = DB::fetch($query)) {
				$pids .= ','.$post['pid'];
				$postcount++;
			}
			$multi = '';
		}

		if(!$postcount) {
			$error = 'prune_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit);
	showformheader('prune&frame=no'.($operation ? '&operation='.$operation : ''), 'target="pruneframe"');
	showhiddenfields(array('pids' => authcode($pids, 'ENCODE'), 'posttableid' => $posttable));
	showtableheader(cplang('prune_result').' '.$postcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'pruneforum\').pp.value=\'\';$(\'pruneforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

	if($error) {
		cpmsg($error);
	} else {
		if($_G['gp_detail']) {
			showsubtitle(array('', 'subject', 'message', 'forum', 'author', 'time'));
			echo $posts;
		}
	}

	showsubmit('prunesubmit', 'submit', $_G['gp_detail'] ? '<input type="checkbox" name="chkall" id="chkall" class="checkbox" checked onclick="checkAll(\'prefix\', this.form, \'pidarray\')" /><label for="chkall">'.cplang('del').'</label>' : '',
		'<input class="checkbox" type="checkbox" name="donotupdatemember" id="donotupdatemember" value="1" checked="checked" /><label for="donotupdatemember"> '.cplang('prune_no_update_member').'</label>', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="pruneframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>