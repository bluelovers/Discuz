<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleanup_daily.php 23607 2011-07-27 08:09:08Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/cache');
updatecache('forumrecommend');

DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE endtime>'0' AND endtime<='$_G[timestamp]'", 'UNBUFFERED');
if(DB::affected_rows()) {
	updatecache(array('setting', 'advs'));
}
DB::query("TRUNCATE ".DB::table('common_searchindex'));
DB::query("DELETE FROM ".DB::table('forum_threadmod')." WHERE tid>0 AND dateline<'$_G[timestamp]'-31536000", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('forum_forumrecommend')." WHERE expiration>0 AND expiration<'$_G[timestamp]'", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('home_visitor')." WHERE uid>0 AND dateline<'$_G[timestamp]'-7776000", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('home_notification')." WHERE uid>0 AND new=0 AND dateline<'$_G[timestamp]'-172800", 'UNBUFFERED');
if($settingnew['heatthread']['type'] == 2 && $settingnew['heatthread']['period']) {
	$partakeperoid = 86400 * $settingnew['heatthread']['period'];
	DB::query("DELETE FROM ".DB::table('forum_threadpartake')." WHERE dateline<'$_G[timestamp]'-$partakeperoid", 'UNBUFFERED');
}

DB::query("UPDATE ".DB::table('common_member_count')." SET todayattachs='0',todayattachsize='0'");

DB::query("UPDATE ".DB::table('forum_trade')." SET closed='1' WHERE expiration>0 AND expiration<'$_G[timestamp]'", 'UNBUFFERED');
DB::query("DELETE FROM ".DB::table('forum_tradelog')." WHERE buyerid>0 AND status=0 AND lastupdate<'$_G[timestamp]'-432000", 'UNBUFFERED');

if($_G['setting']['cachethreadon']) {
	removedir($_G['setting']['cachethreaddir'], TRUE);
}
removedir($_G['setting']['attachdir'].'image', TRUE);
@touch($_G['setting']['attachdir'].'image/index.htm');

require_once libfile('function/forum');
$delaids = array();
$query = DB::query("SELECT aid, attachment, thumb FROM ".DB::table('forum_attachment_unused')." WHERE dateline<'$_G[timestamp]'-86400");
while($attach = DB::fetch($query)) {
	dunlink($attach);
	$delaids[] = $attach['aid'];
}
if($delaids) {
	DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE aid IN (".dimplode($delaids).")", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('forum_attachment_unused')." WHERE dateline<'$_G[timestamp]'-86400", 'UNBUFFERED');
}

$uids = $members = array();
$query = DB::query("SELECT uid, groupid, credits FROM ".DB::table('common_member')." WHERE groupid IN ('4', '5') AND groupexpiry>'0' AND groupexpiry<'$_G[timestamp]'");
while($row = DB::fetch($query)) {
	$uids[] = $row['uid'];
	$members[$row['uid']] = $row;
}
if($uids) {
	$query = DB::query("SELECT uid, groupterms FROM ".DB::table('common_member_field_forum')." WHERE uid IN (".dimplode($uids).")");
	while($member = DB::fetch($query)) {
		$sql = 'uid=uid';
		$member['groupterms'] = unserialize($member['groupterms']);
		$member['groupid'] = $members[$member['uid']]['groupid'];
		$member['credits'] = $members[$member['uid']]['credits'];

		if(!empty($member['groupterms']['main']['groupid'])) {
			$groupidnew = $member['groupterms']['main']['groupid'];
			$adminidnew = $member['groupterms']['main']['adminid'];
			unset($member['groupterms']['main']);
			unset($member['groupterms']['ext'][$member['groupid']]);
			$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
		} else {
			$query = DB::query("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE type='member' AND creditshigher<='$member[credits]' AND creditslower>'$member[credits]'");
			$groupidnew = DB::result($query, 0);
			$adminidnew = 0;
		}
		$sql .= ", adminid='$adminidnew', groupid='$groupidnew'";
		DB::query("UPDATE ".DB::table('common_member')." SET $sql WHERE uid='$member[uid]'");
		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groupterms='".($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '')."' WHERE uid='$member[uid]'");
	}
}

if(!empty($_G['setting']['advexpiration']['allow'])) {
	$endtimenotice = mktime(0, 0, 0, date('m', TIMESTAMP), date('d', TIMESTAMP), date('Y', TIMESTAMP)) + $_G['setting']['advexpiration']['day'] * 86400;
	$query = DB::query("SELECT advid, title FROM ".DB::table('common_advertisement')." WHERE endtime='$endtimenotice'");
	$advs = array();
	while($adv = DB::fetch($query)) {
		$advs[] = '<a href="admin.php?action=adv&operation=edit&advid='.$adv['advid'].'" target="_blank">'.$adv['title'].'</a>';
	}
	if($advs) {
		$users = explode("\n", $_G['setting']['advexpiration']['users']);
		$users = array_map('trim', $users);
		if($users) {
			$query = DB::query("SELECT username, uid, email FROM ".DB::table("common_member")." WHERE username IN (".dimplode($users).")");
			while($member = DB::fetch($query)) {
				$noticelang = array('day' => $_G['setting']['advexpiration']['day'], 'advs' => implode("<br />", $advs));
				if(in_array('notice', $_G['setting']['advexpiration']['method'])) {
					notification_add($member['uid'], 'system', 'system_adv_expiration', $noticelang, 1);
				}
				if(in_array('mail', $_G['setting']['advexpiration']['method'])) {
					sendmail("$member[username] <$member[email]>", lang('email', 'adv_expiration_subject', $noticelang), lang('email', 'adv_expiration_message', $noticelang));
				}
			}
		}
	}
}


$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_card')." WHERE status = '1' AND cleardateline <= '{$_G['timestamp']}'");
if($count) {
	DB::query("UPDATE ".DB::table('common_card')." SET status = 9 WHERE status = '1' AND cleardateline <= '{$_G['timestamp']}'");
	$card_info = serialize(array('num' => $count));
	DB::query("INSERT INTO ".DB::table('common_card_log')." (info, dateline, operation)VALUES('{$card_info}', '{$_G['timestamp']}', 9)");
}

DB::delete('common_member_action_log', 'dateline < '.($_G['timestamp'] - 86400));

function removedir($dirname, $keepdir = FALSE) {
	$dirname = str_replace(array( "\n", "\r", '..'), array('', '', ''), $dirname);

	if(!is_dir($dirname)) {
		return FALSE;
	}
	$handle = opendir($dirname);
	while(($file = readdir($handle)) !== FALSE) {
		if($file != '.' && $file != '..') {
			$dir = $dirname . DIRECTORY_SEPARATOR . $file;
			is_dir($dir) ? removedir($dir) : unlink($dir);
		}
	}
	closedir($handle);
	return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
}

?>