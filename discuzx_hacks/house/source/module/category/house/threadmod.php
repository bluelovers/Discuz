<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: house_index.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/category');

if(empty($_G['uid'])) {
	showmessage(lang('category/template', 'house_thread_not_exist'), '', '', array('login' => 1));
}

$moderate = $_G['gp_moderate'];
$_G['gp_handlekey'] = 'mods';
$_G['gp_gid'] = intval($_G['gp_gid']);

if(!empty($moderate) && $_G['gp_action'] == 'delthread' && $sortid) {
	if(empty($channel['managegid'][$_G['groupid']])) {
		showmessage(lang('category/template', 'house_usergroup_nopur_manage'));
	}

	$tidsadd = !empty($moderate) ? 'WHERE tid IN ('.dimplode($moderate).')' : '';
	if($tidsadd) {
		DB::query("DELETE FROM ".DB::table('category_'.$modidentifier.'_thread')." $tidsadd");
		DB::query("DELETE FROM ".DB::table('category_sortoptionvar')." $tidsadd");
		DB::query("DELETE FROM ".DB::table('category_sortvalue')."$sortid $tidsadd");
		$query = DB::query("SELECT * FROM ".DB::table('category_'.$modidentifier.'_pic')." $tidsadd");
		while($row = DB::fetch($query)) {
			@unlink($_G['setting']['attachdir'].'/category/'.$row['url']);
			@unlink(DISCUZ_ROOT.'./data/attachment/image/'.$row['aid'].'_140_140_house.jpg');
			@unlink(DISCUZ_ROOT.'./data/attachment/image/'.$row['aid'].'_48_48_house.jpg');
		}
		DB::query("DELETE FROM ".DB::table('category_'.$modidentifier.'_pic')." $tidsadd");
		showmessage(lang('category/template', 'house_delete_success'), $modurl.'?mod=list&sortid='.$sortid);
	}
	showmessage(lang('category/template', 'house_lack_args'));
	exit();
} else {

	if(!in_array($_G['gp_operation'], array('recommend', 'push', 'highlight', 'stick'))) {
		showmessage(lang('category/template', 'house_lack_args'));
	}

	if($_G['gp_operation'] == 'stick' && empty($channel['managegid'][$_G['groupid']])) {
		showmessage(lang('category/template', 'house_usergroup_nopur_totop'));
	}

	$thread = DB::fetch_first("SELECT * FROM ".DB::table('category_'.$modidentifier.'_thread')." WHERE tid IN(".dimplode($moderate).")");
	if(empty($channel['managegid'][$_G['groupid']]) && $_G['uid'] != $thread['authorid']) {
		showmessage(lang('category/template', 'house_usergroup_nopur_manage'));
	
	}

	$remainnum = array();
	$isgroupadmin = 0;
	$usergroup = $category_usergroup;
	if($_G['uid'] == $thread['authorid'] && empty($channel['managegid'][$_G['groupid']])) {
		if($_G['gp_operation'] == 'recommend' && empty($usergroup['allowrecommend'])) {
			showmessage(lang('category/template', 'house_usergroup_nopur_stick'));
		} elseif($_G['gp_operation'] == 'push' && empty($usergroup['allowpush'])) {
			showmessage(lang('category/template', 'house_usergroup_nopur_promote'));
		} elseif($_G['gp_operation'] == 'highlight' && empty($usergroup['allowhighlight'])) {
			showmessage(lang('category/template', 'house_usergroup_nopur_highlight'));
		} else {
			$today = DB::fetch_first("SELECT * FROM ".DB::table('category_'.$modidentifier.'_member')." WHERE uid='$_G[uid]'");
			if($_G['gp_operation'] == 'recommend' && $today['todayrecommend'] >= $usergroup['recommenddayper'] && !empty($usergroup['recommenddayper'])) {
				showmessage(lang('category/template', 'house_today_stick_count1').$today[todayrecommend].lang('category/template', 'house_today_stick_count2'));
			} elseif($_G['gp_operation'] == 'push' && $today['todaypush'] >= $usergroup['pushdayper'] && !empty($usergroup['pushdayper'])) {
				showmessage(lang('category/template', 'house_today_promote_count1').$today[todaypush].lang('category/template', 'house_today_promote_count2'));
			} elseif($_G['gp_operation'] == 'highlight' && $today['todayhighlight'] >= $usergroup['highlightdayper'] && !empty($usergroup['highlightdayper'])) {
				showmessage(lang('category/template', 'house_today_highlight_count1').$today[todayhighlight].lang('category/template', 'house_today_highlight_count2'));
			}
		}

		$isgroupadmin = 1;
		$remainnum['recommend'] = $usergroup['recommenddayper'] - $today['recommendnum'];
		$remainnum['push'] = $usergroup['pushdayper'] - $today['pushnum'];
		$remainnum['highlight'] = $usergroup['highlightdayper'] - $today['highlightnum'];
	}

	$threadlist = array();
	if(!submitcheck('modsubmit')) {
		$query = DB::query("SELECT * FROM ".DB::table('category_sortvalue')."$sortid WHERE tid IN(".dimplode($moderate).")");
		while($row = DB::fetch($query)) {
			$threadlist[$row['tid']] = $row;
			$checkdigest[$row['digest']] = ' checked="checked"';
			$checkrecommend[$row['recommend']] = ' checked="checked"';
			$checkstick[$row['displayorder']] = ' checked="checked"';
			$string = sprintf('%02d', $row['highlight']);
			$stylestr = sprintf('%03b', $string[0]);
			for($i = 1; $i <= 3; $i++) {
				$stylecheck[$i] = $stylestr[$i - 1] ? 1 : 0;
			}
			$colorcheck = $string[1];
		}
	} else {
		$statussql = $addnumsql = '';
		if($_G['gp_operation'] == 'recommend') {
			$isrecommend = intval($_G['gp_isrecommend']);
			$statussql = "recommend='".intval($_G['gp_isrecommend'])."'";
			$addnumsql = 'todayrecommend=todayrecommend+1';
			$expiration = TIMESTAMP + 86400 * 3;
		} elseif($_G['gp_operation'] == 'push') {
			$statussql = "dateline='$_G[timestamp]'";
			$addnumsql = 'todaypush=todaypush+1';
		} elseif($_G['gp_operation'] == 'highlight') {
			$highlight_style = $_G['gp_highlight_style'];
			$highlight_color = $_G['gp_highlight_color'];
			$stylebin = '';
			for($i = 1; $i <= 3; $i++) {
				$stylebin .= empty($highlight_style[$i]) ? '0' : '1';
			}

			$highlight_style = bindec($stylebin);
			if($highlight_style < 0 || $highlight_style > 7 || $highlight_color < 0 || $highlight_color > 8) {
				showmessage('undefined_action', NULL);
			}
			$statussql = "highlight='$highlight_style$highlight_color'";
			$addnumsql = 'todayhighlight=todayhighlight+1';
			$expiration = TIMESTAMP + 86400;
		} else {
			$sticklevel = intval($_G['gp_sticklevel']);
			$statussql = "displayorder='$sticklevel'";
		}

		if($statussql) {
			DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET $statussql WHERE tid IN (".dimplode($moderate).")", 'UNBUFFERED');
			foreach($moderate as $tid) {
				DB::query("INSERT INTO ".DB::table('category_threadmod')." (tid, expiration, action) VALUES ('$tid', '$expiration', '$_G[gp_operation]')");
			}
		}

		if($addnumsql) {
			DB::query("UPDATE ".DB::table('category_'.$modidentifier.'_member')." SET $addnumsql WHERE uid='$_G[uid]'", 'UNBUFFERED');
		}

		$url = $_G['gp_gid'] ? $modurl.'?mod=my&gid='.$_G['gp_gid'].'&sortid='.$sortid : $modurl.'?mod=list&sortid='.$sortid;

		showmessage(lang('category/template', 'house_manage_success'), $url);
	}

	include template('category/category_threadmod');
}

?>