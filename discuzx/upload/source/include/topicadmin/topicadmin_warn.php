<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_warn.php 22857 2011-05-26 08:50:06Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowwarnpost']) {
	showmessage('no_privilege_warnpost');
}

$topiclist = $_G['gp_topiclist'];
if(!($warnpids = dimplode($topiclist))) {
	showmessage('admin_warn_invalid');
} elseif(!$_G['group']['allowbanpost'] || !$_G['tid']) {
	showmessage('admin_nopermission', NULL);
}

$posts = $authors = array();
$authorwarnings = $warningauthor = $warnstatus = '';
$posttable = getposttablebytid($_G['tid']);
$query = DB::query("SELECT p.pid, p.authorid, p.author, p.status, p.dateline, p.message, m.adminid FROM ".DB::table($posttable)." p
	LEFT JOIN ".DB::table('common_member')." m ON p.authorid=m.uid WHERE p.pid IN ($warnpids) AND p.tid='$_G[tid]'");
while($post = DB::fetch($query)) {
	if($_G['adminid'] == 1 && $post['adminid'] != 1 ||
		$_G['adminid'] == 2 && !in_array($post['adminid'], array(1, 2)) ||
		$_G['adminid'] == 3 && in_array($post['adminid'], array(0, -1))) {
		$warnstatus = ($post['status'] & 2) || $warnstatus;
		$authors[$post['authorid']] = 1;
		$posts[] = $post;
	}
}

if(!$posts) {
	showmessage('admin_warn_nopermission');
}
$authorcount = count(array_keys($authors));
$modpostsnum = count($posts);

if($modpostsnum == 1 || $authorcount == 1) {
	$authorwarnings = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_warning')." WHERE authorid='{$posts[0][authorid]}'");
	$warningauthor = $posts[0]['author'];
}

if(!submitcheck('modsubmit')) {

	$warnpid = $checkunwarn = $checkwarn = '';
	foreach($topiclist as $id) {
		$warnpid .= '<input type="hidden" name="topiclist[]" value="'.$id.'" />';
	}

	$warnstatus ? $checkunwarn = 'checked="checked"' : $checkwarn = 'checked="checked"';

	include template('forum/topicadmin_action');

} else {

	$warned = intval($_G['gp_warned']);
	$modaction = $warned ? 'WRN' : 'UWN';

	$reason = checkreasonpm();

	$pids = $comma = '';
	foreach($posts as $k => $post) {
		if($warned && !($post['status'] & 2)) {
			my_post_log('warn', array('pid' => $post['pid'], 'uid' => $post['authorid']));
			DB::query("UPDATE ".DB::table($posttable)." SET status=status|2 WHERE pid='$post[pid]'", 'UNBUFFERED');
			$reason = cutstr(dhtmlspecialchars($_G['gp_reason']), 40);
			DB::query("INSERT INTO ".DB::table('forum_warning')." (pid, operatorid, operator, authorid, author, dateline, reason) VALUES ('$post[pid]', '$_G[uid]', '$_G[username]', '$post[authorid]', '".addslashes($post['author'])."', '$_G[timestamp]', '$reason')", 'UNBUFFERED');
			$authorwarnings = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_warning')." WHERE authorid='$post[authorid]' AND dateline>=$_G[timestamp]-".$_G[setting][warningexpiration]*86400);
			if($authorwarnings >= $_G['setting']['warninglimit']) {
				$member = DB::fetch_first("SELECT adminid, groupid, extgroupids FROM ".DB::table('common_member')." WHERE uid='$post[authorid]'");
				$groupterms = unserialize(DB::result_first("SELECT groupterms FROM ".DB::table('common_member_field_forum')." WHERE uid='$post[authorid]'"));
				if($member && $member['groupid'] != 4) {
					$extgroupidsarray = array();
					foreach(array_unique(array_merge($member['extgroupids'], array(4))) as $extgroupid) {
						if($extgroupid) {
							$extgroupidsarray[] = $extgroupid;
						}
					}
					$extgroupidsnew = implode("\t", $extgroupidsarray);
					$banexpiry = TIMESTAMP + $_G['setting']['warningexpiration'] * 86400;
					$groupterms['ext'][4] = $banexpiry;
					DB::query("UPDATE ".DB::table('common_member')." SET groupid='4', groupexpiry='".groupexpiry($groupterms)."' WHERE uid='$post[authorid]'");
					DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groupterms='".addslashes(serialize($groupterms))."' WHERE uid='$post[authorid]'");
				}
			}
			$pids .= $comma.$post['pid'];
			$comma = ',';
		} elseif(!$warned && ($post['status'] & 2)) {
			my_post_log('unwarn', array('pid' => $post['pid'], 'uid' => $post['authorid']));
			DB::query("UPDATE ".DB::table($posttable)." SET status=status^2 WHERE pid='$post[pid]' AND status=status|2", 'UNBUFFERED');
			DB::query("DELETE FROM ".DB::table('forum_warning')." WHERE pid='$post[pid]'", 'UNBUFFERED');
			$pids .= $comma.$post['pid'];
			$comma = ',';
		}
	}

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
	'reasonpm'	=> ($sendreasonpm ? array('data' => $posts, 'var' => 'post', 'item' => 'reason_warn_post') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => stripslashes($reason),
			'warningexpiration' => $_G['setting']['warningexpiration'], 'warninglimit' => $_G['setting']['warninglimit'], 'warningexpiration' => $_G['setting']['warningexpiration'],
			'authorwarnings' => $authorwarnings),
	'modtids'	=> 0,
	'modlog'	=> $thread
	);

}

?>