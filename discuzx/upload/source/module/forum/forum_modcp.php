<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_modcp.php 22329 2011-05-03 01:43:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('IN_MODCP', true);

$cpscript = basename($_G['PHP_SELF']);
if(!empty($_G['forum']) && $_G['forum']['status'] == 3) {
	showmessage('group_admin_enter_panel', 'forum.php?mod=group&action=manage&fid='.$_G['fid']);
}

require_once libfile('class/panel');
$modsession = new discuz_panel(MODCP_PANEL);
if(getgpc('login_panel') && getgpc('cppwd') && submitcheck('submit')) {
	$modsession->dologin($_G[uid], getgpc('cppwd'), true);
}

if(!$modsession->islogin) {
	$_G['gp_action'] = 'login';
}

if($_G['gp_action'] == 'logout') {
	$modsession->dologout();
	showmessage('modcp_logout_succeed', 'forum.php');
}

$modforums = $modsession->get('modforums');
$_G['gp_action'] = empty($_G['gp_action']) && $_G['fid'] ? 'thread' : $_G['gp_action'];
$op = getgpc('op');
if($modforums === null) {
	$modforums = array('fids' => '', 'list' => array(), 'recyclebins' => array());
	$comma = '';
	if($_G['adminid'] == 3) {
		$query = DB::query("SELECT m.fid, f.name, f.recyclebin
			FROM ".DB::table('forum_moderator')." m, ".DB::table('forum_forum')." f
			WHERE m.uid='$_G[uid]' AND f.fid=m.fid AND f.status='1' AND f.type<>'group'");
		while($tforum = DB::fetch($query)) {
			$modforums['fids'] .= $comma.$tforum['fid']; $comma = ',';
			$modforums['recyclebins'][$tforum['fid']] = $tforum['recyclebin'];
			$modforums['list'][$tforum['fid']] = strip_tags($tforum['name']);
		}
	} else {
		$sql = $_G['member']['accessmasks'] ?
			"SELECT f.fid, f.name, f.threads, f.recyclebin, ff.viewperm, a.allowview FROM ".DB::table('forum_forum')." f
				LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid
				LEFT JOIN ".DB::table('forum_access')." a ON a.uid='$_G[uid]' AND a.fid=f.fid
				WHERE f.status='1' AND ff.redirect=''"
			: "SELECT f.fid, f.name, f.threads, f.recyclebin, ff.viewperm, ff.redirect FROM ".DB::table('forum_forum')." f
				LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid)
				WHERE f.status='1' AND f.type<>'group' AND ff.redirect=''";
		$query = DB::query($sql);
		while ($tforum = DB::fetch($query)) {
			$tforum['allowview'] = !isset($tforum['allowview']) ? '' : $tforum['allowview'];
			if($tforum['allowview'] == 1 || ($tforum['allowview'] == 0 && ((!$tforum['viewperm'] && $_G['group']['readaccess']) || ($tforum['viewperm'] && forumperm($tforum['viewperm']))))) {
				$modforums['fids'] .= $comma.$tforum['fid']; $comma = ',';
				$modforums['recyclebins'][$tforum['fid']] = $tforum['recyclebin'];
				$modforums['list'][$tforum['fid']] = strip_tags($tforum['name']);
			}
		}
	}

	$modsession->set('modforums', $modforums, true);
}

if($_G['fid'] && $_G['forum']['ismoderator']) {
	dsetcookie('modcpfid', $_G['fid']);
	$forcefid = "&amp;fid=$_G[fid]";
} elseif(!empty($modforums) && count($modforums['list']) == 1) {
	$forcefid = "&amp;fid=$modforums[fids]";
} else {
	$forcefid = '';
}

$script = $modtpl = '';
switch ($_G['gp_action']) {

	case 'announcement':
		$_G['group']['allowpostannounce'] && $script = 'announcement';
		break;

	case 'member':
		$op == 'edit' && $_G['group']['allowedituser'] && $script = 'member';
		$op == 'ban' && ($_G['group']['allowbanuser'] || $_G['group']['allowbanvisituser']) && $script = 'member';
		$op == 'ipban' && $_G['group']['allowbanip'] && $script = 'member';
		break;

	case 'moderate':
		($op == 'threads' || $op == 'replies') && $_G['group']['allowmodpost'] && $script = 'moderate';
		$op == 'members' && $_G['group']['allowmoduser'] && $script = 'moderate';
		break;

	case 'forum':
		$op == 'editforum' && $_G['group']['alloweditforum'] && $script = 'forum';
		$op == 'recommend' && $_G['group']['allowrecommendthread'] && $script = 'forum';
		break;

	case 'forumaccess':
		$_G['group']['allowedituser'] && $script = 'forumaccess';
		break;

	case 'log':
		$_G['group']['allowviewlog'] && $script = 'log';
		break;

	case 'login':
		$script = $modsession->islogin ? 'home' : 'login';
		break;

	case 'thread':
		$script = 'thread';
		break;

	case 'recyclebin':
		$script = 'recyclebin';
		break;

	case 'recyclebinpost':
		$script = 'recyclebinpost';
		break;

	case 'plugin':
		$script = 'plugin';
		break;

	case 'report':
		$script = 'report';
		break;

	default:
		$_G['gp_action'] = $script = 'home';
		$modtpl = 'modcp_home';
}

$script = empty($script) ? 'noperm' : $script;
$modtpl = empty($modtpl) ? (!empty($script) ? 'modcp_'.$script : '') : $modtpl;
$modtpl = 'forum/' . $modtpl;
$op = isset($op) ? trim($op) : '';

if($script != 'log') {
	include libfile('function/misc');
	$extra = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('cppwd', 'formhash', 'submit', 'addsubmit'));
	$modcplog = array(TIMESTAMP, $_G['username'], $_G['adminid'], $_G['clientip'], $_G['gp_action'], $op, $_G['fid'], $extra);
	writelog('modcp', implode("\t", clearlogstring($modcplog)));
}

require DISCUZ_ROOT.'./source/include/modcp/modcp_'.$script.'.php';

$reportnum = $modpostnum = $modthreadnum = $modforumnum = 0;
$modforumnum = count($modforums['list']);
$modnum = '';
if($modforumnum) {
	if($_G['group']['allowmodpost']) {
		$modnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_moderate')." m
			INNER JOIN ".DB::table('forum_thread')." t ON t.tid=m.id AND t.fid IN($modforums[fids])
			WHERE m.idtype='tid' AND m.status='0'");
		$modnum += DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_moderate')." m
			INNER JOIN ".DB::table('forum_post')." p ON p.pid=m.id AND p.fid IN($modforums[fids])
			WHERE m.idtype='pid' AND m.status='0'");
	}
	if($_G['group']['allowmoduser']) {
		$modnum += DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_validate')." WHERE status='0'");
	}
}

switch($_G['adminid']) {
	case 1: $access = '1,2,3,4,5,6,7'; break;
	case 2: $access = '2,3,6,7'; break;
	default: $access = '1,3,5,7'; break;
}
$notenum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_adminnote')." WHERE access IN ($access)");

include template('forum/modcp');

function getposttableselect() {
	global $_G;

	loadcache('posttable_info');
	if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
		$posttableselect = '<select name="posttableid" id="posttableid" class="ps">';
		foreach($_G['cache']['posttable_info'] as $posttableid => $data) {
			$posttableselect .= '<option value="'.$posttableid.'"'.($_G['gp_posttableid'] == $posttableid ? ' selected="selected"' : '').'>'.($data['memo'] ? $data['memo'] : 'post_'.$posttableid).'</option>';
		}
		$posttableselect .= '</select>';
	} else {
		$posttableselect = '';
	}
	return $posttableselect;
}

?>