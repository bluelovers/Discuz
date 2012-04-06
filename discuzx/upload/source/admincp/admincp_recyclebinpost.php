<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: admincp_recyclebinpost.php 28159 2012-02-23 07:08:33Z songlixin $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

require_once libfile('function/post');
require_once libfile('function/discuzcode');

$posttableid = intval($_G['gp_posttableid']);

cpheader();

if(submitcheck('rbsubmit')) {
	$moderate = $_G['gp_moderate'];
	$moderation = array('delete' => array(), 'undelete' => array(), 'ignore' => array());
	if(is_array($moderate)) {
		foreach($moderate as $pid => $action) {
			$moderation[$action][] = intval($pid);
		}
	}

	$postsdel = $postsundel = 0;
	if($moderation['delete']) {
		$postsdel = recyclebinpostdelete($moderation['delete'], $posttableid);
	}
	if($moderation['undelete']) {
		$postsundel = recyclebinpostundelete($moderation['undelete'], $posttableid);
	}

	if($operation == 'search') {
		$cpmsg = cplang('recyclebinpost_succeed', array('postsdel' => $postsdel, 'postsundel' => $postsundel));
?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('rbsearchform').searchsubmit.click();</script>
<?php
	} else {
		cpmsg('recyclebinpost_succeed', 'action=recyclebinpost&operation='.$operation, 'succeed', array('postsdel' => $postsdel, 'postsundel' => $postsundel));
	}
}

$lpp = empty($_G['gp_lpp']) ? 20 : $_G['gp_lpp'];
$start = ($page - 1) * $lpp;
$start_limit = ($page - 1) * $lpp;
$multi = '';
$innersql = '';

if(!$operation) {
	shownav('topic', 'nav_recyclebinpost');
	showsubmenu('nav_recyclebinpost', array(
		array('recyclebinpost_list', 'recyclebinpost', 1),
		array('search', 'recyclebinpost&operation=search', 0),
		array('clean', 'recyclebinpost&operation=clean', 0)
	));
	showtagheader('div', 'postlist', 1);
	showformheader('recyclebinpost', '', 'rbform');
	showhiddenfields(array('posttableid' => $posttableid));
	showtableheader('recyclebinpost');

	$postlistcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable($posttableid))." WHERE invisible='-5'");

	if($postlistcount && recyclebinpostshowpostlist('', $start_limit, $lpp)) {
		$multi = multi($postlistcount, $lpp, $page, ADMINSCRIPT."?action=recyclebinpost");
	}
	showsubmit('rbsubmit', 'submit', '', '<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'delete\')">'.cplang('recyclebin_all_delete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'undelete\')">'.cplang('recyclebin_all_undelete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'ignore\')">'.cplang('recyclebin_all_ignore').'</a> &nbsp;', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="rbframe" style="display:none"></iframe>';
	showtagfooter('div');

} elseif($operation == 'search') {

	$inforum = $_G['gp_inforum'];
	$authors = $_G['gp_authors'];
	$keywords = $_G['gp_keywords'];
	$pstarttime = $_G['gp_pstarttime'];
	$pendtime = $_G['gp_pendtime'];
	$searchsubmit = $_G['gp_searchsubmit'];
	require_once libfile('function/cloud');
	$secStatus = getcloudappstatus('security', 0);
	if($secStatus){
		$security = $_G['gp_security'];
	}

	require_once libfile('function/forumlist');

	$forumselect = '<select name="inforum"><option value="">&nbsp;&nbsp;> '.$lang['allthread'].'</option>'.
		'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';

	if($inforum) {
		$forumselect = preg_replace("/(\<option value=\"$inforum\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}

	shownav('topic', 'nav_recyclebinpost');
	showsubmenu('nav_recyclebinpost', array(
		array('recyclebinpost_list', 'recyclebinpost', 0),
		array('search', 'recyclebinpost&operation=search', 1),
		array('clean', 'recyclebinpost&operation=clean', 0)
	));
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('rbsearchform').page.value=number;
	$('rbsearchform').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'postsearch', !$searchsubmit);
	showformheader('recyclebinpost&operation=search', '', 'rbsearchform');
	showhiddenfields(array('page' => $page));
	showtableheader('recyclebinpost_search');
	showsetting('recyclebinpost_search_forum', '', '', $forumselect);
	showsetting('recyclebinpost_search_author', 'authors', $authors, 'text');
	showsetting('recyclebinpost_search_keyword', 'keywords', $keywords, 'text');
	showsetting('recyclebin_search_post_time', array('pstarttime', 'pendtime'), array($pstarttime, $pendtime), 'daterange');
	showsetting('postsplit', '', '', getposttableselect());
	if($secStatus){
		showsetting('recyclebin_search_security_post','security', $security, 'radio');
	}
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

	if(submitcheck('searchsubmit', 1)) {

		$sql = '';
		$sql .= $inforum			? " AND fid='$inforum'" : '';
		$sql .= $authors != ''		? " AND author IN ('".str_replace(',', '\',\'', str_replace(' ', '', $authors))."')" : '';
		$sql .= $pstarttime != ''	? " AND dateline>='".strtotime($pstarttime)."'" : '';
		$sql .= $pendtime != ''		? " AND dateline<'".strtotime($pendtime)."'" : '';

		if(trim($keywords)) {
			$sqlkeywords = $or = '';
			foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
				$sqlkeywords .= " $or message LIKE '%$keyword%'";
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";
		}

		if($secStatus && $security){
			$innersql = " INNER JOIN ".DB::table('security_evilpost')." s ON t.pid = s.pid ";
			$sql .= " AND s.type = '0'";
		}

		$postlistcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable($posttableid))." t $innersql WHERE invisible='-5' $sql");

		showtagheader('div', 'postlist', $searchsubmit);
		showformheader('recyclebinpost&operation=search&frame=no', 'target="rbframe"', 'rbform');
		showtableheader(cplang('recyclebinpost_result').' '.$postlistcount.' <a href="#" onclick="$(\'postlist\').style.display=\'none\';$(\'postsearch\').style.display=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

		if($postlistcount && recyclebinpostshowpostlist($sql, $start_limit, $lpp)) {
			$multi = multi($postlistcount, $lpp, $page, ADMINSCRIPT."?action=recyclebinpost");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=recyclebinpost&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=recyclebinpost&amp;page='+this.value", "page(this.value)", $multi);
		}

		showsubmit('rbsubmit', 'submit', '', '<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'delete\')">'.cplang('recyclebin_all_delete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'undelete\')">'.cplang('recyclebin_all_undelete').'</a> &nbsp;<a href="#rb" onclick="checkAll(\'option\', $(\'rbform\'), \'ignore\')">'.cplang('recyclebin_all_ignore').'</a> &nbsp;', $multi);
		showtablefooter();
		showformfooter();
		echo '<iframe name="rbframe" style="display:none"></iframe>';
		showtagfooter('div');
	}

} elseif($operation == 'clean') {

	if(!submitcheck('cleanrbsubmit')) {

		shownav('topic', 'nav_recyclebinpost');
		showsubmenu('nav_recyclebinpost', array(
			array('recyclebinpost_list', 'recyclebinpost', 0),
			array('search', 'recyclebinpost&operation=search', 0),
			array('clean', 'recyclebinpost&operation=clean', 1)
		));
		showformheader('recyclebinpost&operation=clean');
		showtableheader('recyclebinpost_clean');
		showsetting('recyclebinpost_clean_days', 'days', '30', 'text');
		showsubmit('cleanrbsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$deletetids = array();
		$timestamp = TIMESTAMP - max(0, intval($_G['gp_days'])*86400);

		$postlist = array();
		loadcache('posttableids');
		$posttables = !empty($_G['cache']['posttableids']) ? $_G['cache']['posttableids'] : array(0);
		foreach($posttables as $ptid) {
			$query = DB::query('SELECT pid FROM '.DB::table(getposttable($ptid))." WHERE invisible='-5' AND dateline<$timestamp");
			while($post = DB::fetch($query)) {
				$postlist[$ptid][] = $post['pid'];
			}
		}
		if(empty($postlist)) {
			cpmsg('recyclebinpost_none', 'action=recyclebinpost', 'error');
		}
		$postsdel = $postsundel = 0;
		foreach($postlist as $ptid => $deletepids) {
			$postsdel += recyclebinpostdelete($deletepids, $ptid);
		}
		cpmsg('recyclebinpost_succeed', 'action=recyclebinpost&operation=clean', 'succeed', array('postsdel' => $postsdel, 'postsundel' => $postsundel));
	}
}

function recyclebinpostshowpostlist($sql, $start_limit, $lpp) {
	global $_G, $lang, $posttableid, $innersql;

	$tids = $fids = array();

	$query = DB::query("SELECT t.message, t.useip, t.attachment, t.htmlon, t.smileyoff, t.bbcodeoff, t.pid, t.tid, t.fid, t.author, t.dateline, t.subject, t.authorid, t.anonymous FROM ".DB::table(getposttable($posttableid))." t $innersql
		WHERE invisible='-5' $sql ORDER BY dateline DESC LIMIT $start_limit, $lpp");
	while($post = DB::fetch($query)) {
		$postlist[] = $post;
	}

	if(empty($postlist)) return false;

	foreach($postlist as $key => $post) {
		$tids[$post['tid']] = $post['tid'];
		$fids[$post['fid']] = $post['fid'];
	}
	$query = DB::query("SELECT tid, subject as tsubject FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($tids).")");
	while($thread = DB::fetch($query)) {
		$threadlist[$thread['tid']] = $thread;
	}
	$query = DB::query("SELECT fid, name AS forumname, allowsmilies, allowhtml, allowbbcode, allowimgcode FROM ".DB::table('forum_forum')." WHERE fid IN (".dimplode($fids).")");
	while($forum = DB::fetch($query)) {
		$forumlist[$forum['fid']] = $forum;
	}

	foreach($postlist as $key => $post) {
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $forumlist[$post['fid']]['allowsmilies'], $forumlist[$post['fid']]['allowbbcode'], $forumlist[$post['fid']]['allowimgcode'], $forumlist[$post['fid']]['allowhtml']);
		$post['dateline'] = dgmdate($post['dateline']);
		if($post['attachment']) {
			require_once libfile('function/attachment');
			$queryattach = DB::query("SELECT aid, filename, filesize, attachment, isimage, remote FROM ".DB::table(getattachtablebytid($post['tid']))." WHERE pid='$post[pid]'");
			while($attach = DB::fetch($queryattach)) {
				$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['url'] = $attach['isimage']
					? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
					 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
				$post['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])."\t").$attach['url'];
			}
		}

		showtablerow("id=\"mod_$post[pid]_row1\"", array('rowspan="3" class="rowform threadopt" style="width:80px;"', 'class="threadtitle"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_1\" value=\"delete\" checked=\"checked\" /><label for=\"mod_$post[pid]_1\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_2\" value=\"undelete\" /><label for=\"mod_$post[pid]_2\">$lang[undelete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_3\" value=\"ignore\" /><label for=\"mod_$post[pid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\" target=\"_blank\">".$forumlist[$post['fid']]['forumname']."</a> &raquo; <a href=\"forum.php?mod=viewthread&tid=$post[tid]\" target=\"_blank\">".$threadlist[$post['tid']]['tsubject']."</a>".($post['subject'] ? ' &raquo; '.$post['subject'] : '')."</h3><p><span class=\"bold\">$lang[author]:</span> <a href=\"home.php?mod=space&uid=$post[authorid]\" target=\"_blank\">$post[author]</a> &nbsp;&nbsp; <span class=\"bold\">$lang[time]:</span> $post[dateline] &nbsp;&nbsp; IP: $post[useip]</p>"
		));
		showtablerow("id=\"mod_$post[pid]_row2\"", 'colspan="2" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$post['message'].'</div>');
		showtablerow("id=\"mod_$post[pid]_row3\"", 'class="threadopt threadtitle" colspan="2"', "$lang[isanonymous]: ".($post['anonymous'] ? $lang['yes'] : $lang['no'])." &nbsp;&nbsp; $lang[ishtmlon]: ".($post['htmlon'] ? $lang['yes'] : $lang['no']));
	}
	return true;
}
?>