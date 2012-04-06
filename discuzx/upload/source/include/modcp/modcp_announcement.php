<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: modcp_announcement.php 20630 2011-03-01 02:45:58Z congyushuai $
 */

if(!defined('IN_DISCUZ') || !defined('IN_MODCP')) {
	exit('Access Denied');
}

$annlist = null;
$add_successed = $edit_successed = false;
$op = empty($_G['gp_op']) ? 'add' : $_G['gp_op'];

$announce = array('subject' => '', 'message' => '', 'starttime' => '', 'endtime' => '');
$announce['checked'] = array('selected="selected"', '');

switch($op) {

	case 'add':

		if(!submitcheck('submit')) {
			$announce['starttime'] = dgmdate(TIMESTAMP, 'd');
			$announce['endtime'] = dgmdate(TIMESTAMP + 86400 * 30, 'd');
		} else {
			$message = is_array($_G['gp_message']) ? $_G['gp_message'][$_G['gp_type']] : '';
			save_announce(0, $_G['gp_starttime'], $_G['gp_endtime'], $_G['gp_subject'], $_G['gp_type'], $message, 0);
			$add_successed = true;
		}
		break;

	case 'manage':

		$annlist = get_annlist();

		if(submitcheck('submit')) {
			$delids = array();
			if(!empty($_G['gp_delete']) && is_array($_G['gp_delete'])) {
				foreach($_G['gp_delete'] as $id) {
					$id = intval($id);
					if(isset($annlist[$id])) {
						unset($annlist[$id]);
						$delids[] = $id;
					}
				}
				if($delids) {
					DB::query("DELETE FROM ".DB::table('forum_announcement')." WHERE id IN(".dimplode($delids).") AND author='$_G[username]'", 'UNBUFFERED');
				}
			}

			$updateorder = false;
			if(!empty($_G['gp_order']) && is_array($_G['gp_order'])) {
				foreach ($_G['gp_order'] as $id => $val) {
					$val = intval($val);
					if(isset($annlist[$id]) && $annlist[$id]['displayorder'] != $val) {
						$annlist[$id]['displayorder'] = $val;
						DB::query("UPDATE ".DB::table('forum_announcement')." SET displayorder='$val' WHERE id='$id'", "UNBUFFERED");
						$updateorder = true;
					}
				}
			}

			if($delids || $updateorder) {
				update_announcecache();
			}
		}

		break;

	case 'edit':
		$id = intval($_G['gp_id']);
		$query = DB::query("SELECT * FROM ".DB::table('forum_announcement')." WHERE id='$id' AND author='$_G[username]'");
		if(!$announce = DB::fetch($query)) {
			showmessage('modcp_ann_nofound');
		}

		if(!submitcheck('submit')) {
			$announce['starttime'] = $announce['starttime'] ? dgmdate($announce['starttime'], 'd') : '';
			$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'd') : '';
			$announce['message'] = $announce['type'] != 1 ? dhtmlspecialchars($announce['message']) : $announce['message'];
			$announce['checked'] = $announce['type'] != 1 ? array('selected="selected"', '') : array('', 'selected="selected"');
		} else {
			$announce['starttime'] = $_G['gp_starttime'];
			$announce['endtime'] = $_G['gp_endtime'];
			$announce['checked'] = $_G['gp_type'] != 1 ? array('selected="selected"', '') : array('', 'selected="selected"');
			$message = $_G['gp_message'][$_G['gp_type']];
			save_announce($id, $_G['gp_starttime'], $_G['gp_endtime'], $_G['gp_subject'], $_G['gp_type'], $message, $_G['gp_displayorder']);
			$edit_successed = true;
		}

		break;

}

$annlist = get_annlist();

function get_annlist() {
	global $_G;
	$annlist =  array();
	$query = DB::query("SELECT * FROM ".DB::table('forum_announcement')." ORDER BY displayorder, starttime DESC, id DESC");
	while($announce = DB::fetch($query)) {
		$announce['disabled'] = $announce['author'] != $_G['member']['username'] ? 'disabled' : '';
		$announce['starttime'] = $announce['starttime'] ? dgmdate($announce['starttime'], 'd') : '-';
		$announce['endtime'] = $announce['endtime'] ? dgmdate($announce['endtime'], 'd') : '-';
		$annlist[$announce['id']] = $announce;
	}
	return $annlist;
}

function update_announcecache() {
	require_once libfile('function/cache');
	updatecache(array('announcements', 'announcements_forum'));
}

function save_announce($id = 0, $starttime, $endtime, $subject, $type, $message, $displayorder = 0) {
	global $_G;

	$displayorder = intval($displayorder);
	$type = intval($type);

	$starttime = empty($starttime) || strtotime($starttime) < TIMESTAMP ? TIMESTAMP : strtotime($starttime);
	$endtime = empty($endtime) ? 0 : (strtotime($endtime) < $starttime ? ($starttime + 86400 * 30) : strtotime($endtime));

	$subject = htmlspecialchars(trim($subject));

	if($type == 1) {
		list($message) = explode("\n", trim($message));
		$message = dhtmlspecialchars($message);
	} else {
		$type = 0;
		$message = trim($message);
	}

	if(empty($subject) || empty($message)) {
		acpmsg('modcp_ann_empty');
	} elseif($type == 1 && substr(strtolower($message), 0, 7) != 'http://') {
		acpmsg('modcp_ann_urlerror');
	} else {
		$sql = "author='$_G[username]', subject='$subject', type='$type', starttime='$starttime', endtime='$endtime',
			message='$message', displayorder='$displayorder'";

		if(empty($id)) {
			DB::query("INSERT INTO ".DB::table('forum_announcement')." SET $sql");
		} else {
			DB::query("UPDATE ".DB::table('forum_announcement')." SET $sql WHERE id='$id'", 'UNBUFFERED');
		}
		update_announcecache();
		return true;
	}
}

?>