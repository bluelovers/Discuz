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

// 允许匿名发信息
/*
if(empty($_G['uid'])) {
	showmessage('请先登录。', '', '', array('login' => 1));
}
*/

if(!empty($_G['uid']) && empty($category_usergroup['allowpost'])) {
	showmessage(lang('category/template', 'house_class_nothing'));
}

if($category_usergroup['postdayper'] && $_G['category_member']['todaythreads'] >= $category_usergroup['postdayper']) {
	showmessage(lang('category/template', 'house_post_thread_max'));
}

require_once libfile('function/category');

$actionarray = array('newthread', 'edit', 'nav');
$action = in_array($_G['gp_action'], $actionarray) ? $_G['gp_action'] : '';

$sortarray = $cityarray = $districtarray = $streetarray = array();
$cityid = intval($_G['gp_cityid']);
$districtid = intval($_G['gp_districtid']);
$streetid = intval($_G['gp_streetid']);

$avatar = category_uc_avatar($_G['uid']);
$usergrouplist[$usergroupid]['icon'] = $usergrouplist[$usergroupid]['icon'] ? $_G['setting']['attachurl'].'common/'.$usergrouplist[$usergroupid]['icon'] : '';
$usergrouplist[$usergroupid]['postdayper'] = $usergrouplist[$usergroupid]['postdayper'] ? intval($usergrouplist[$usergroupid]['postdayper']) : '';
$_G['category_member']['todaythreads'] = intval($_G['category_member']['todaythreads']);

$subject = isset($_G['gp_subject']) ? dhtmlspecialchars(censor(trim($_G['gp_subject']))) : '';
$subject = !empty($subject) ? str_replace("\t", ' ', $subject) : $subject;
$message = isset($_G['gp_message']) ? censor(trim($_G['gp_message'])) : '';

if($action == 'nav') {

	foreach($sortlist as $id => $sort) {
		$sortarray[$id]= $sort['name'];
	}

	foreach($arealist['district'][$cityid] as $aid => $area) {
		$districtarray[$aid]['title'] = $area;
	}

	if(!empty($districtid) && $arealist['street'][$districtid]) {
		foreach($arealist['street'][$districtid] as $aid => $area) {
			$streetarray[$aid]['title'] = $area;
		}
	}

} elseif($action == 'newthread') {

	if(empty($sortid)) {
		showmessage(lang('category/template', 'house_no_option'));
	}

	if(empty($cityid) && $arealist['city']) {
		showmessage(lang('category/template', 'house_no_one_option'));
	} elseif(empty($districtid) && $arealist['district'][$cityid]) {
		showmessage(lang('category/template', 'house_no_two_option'));
	} elseif(empty($streetid) && $arealist['street'][$districtid]) {
		showmessage(lang('category/template', 'house_no_three_option'));
	}
	loadcache(array('category_option_'.$sortid, 'category_template_'.$sortid));
	$_G['category_optionlist'] = $_G['cache']['category_option_'.$sortid];
	threadsort_checkoption($sortid);
	$mapcenter = $arealist['city'][$cityid].' '.$arealist['district'][$cityid][$districtid].' '.$arealist['street'][$districtid][$streetid];
	$category_sort = $sortlist[$sortid];
	$imgnum = array();
	$imgnum = array_pad($imgnum, $category_sort['imgnum'], 0);

	$lastpost = $_G['category_member']['lastpost'];

	if($_G['timestamp']-$lastpost < 30) {
		showmessage(lang('category/template', 'house_post_30second'));
	}

	if(!submitcheck('topicsubmit')) {
		threadsort_optiondata($sortid, $_G['cache']['category_option_'.$sortid], $_G['cache']['category_template_'.$sortid], 0, $usergroupid);
	} else {
		
		if(empty($subject)) {
			showmessage(lang('category/template', 'house_no_subject'));
		} else if($channel['mapkey'] && empty($_G['gp_mapposition'])) {
			showmessage(lang('category/template', 'house_no_mapmark'));
		}
	
		$today = DB::fetch_first("SELECT * FROM ".DB::table('category_'.$modidentifier.'_member')." WHERE uid='$_G[uid]'");

		$_G['category_optiondata'] = threadsort_validator($_G['gp_typeoption']);
		$_G['gp_expiration'] = $_G['gp_expiration'] ? $_G['timestamp'] + intval($_G['gp_expiration']) : 0;
		$cate_groupid = $_G['category_member']['groupid'];
		$message = isset($_G['gp_message']) ? censor(trim($_G['gp_message'])) : '';

		DB::query("INSERT INTO ".DB::table('category_'.$modidentifier.'_thread')." (sortid, author, authorid, subject, message, ip)
		VALUES ('$sortid', ' $_G[username]', '$_G[uid]', '$subject', '$message', '$_G[clientip]')");
		$tid = DB::insert_id();

		foreach($_G['category_optiondata'] as $optionid => $value) {
			$filedname .= $separator.$_G['category_optionlist'][$optionid]['identifier'];
			$valuelist .= $separator."'$value'";
			$separator = ' ,';

			DB::query("INSERT INTO ".DB::table('category_sortoptionvar')." (sortid, tid, optionid, value, expiration)
				VALUES ('$sortid', '$tid', '$optionid', '$value', '$_G[gp_expiration]')");
		}

		if($filedname && $valuelist) {
			DB::query("INSERT INTO ".DB::table('category_sortvalue').
				"$sortid ($filedname, tid, dateline, expiration, city, district, street, groupid,  mapposition) VALUES ($valuelist, '$tid', '$_G[timestamp]', '$_G[gp_expiration]', '$cityid', '$districtid', '$streetid', '$cate_groupid', '$_G[gp_mapposition]')");
		}

		threadsort_insertfile($tid, $_FILES, $sortid, '', $modidentifier, $channel);
		DB::query("UPDATE ".DB::table('category_sort')." SET threads=threads+1, todaythreads=todaythreads+1 WHERE sortid='$sortid'");
		DB::query("UPDATE ".DB::table('category_'.$modidentifier.'_member')." SET threads=threads+1, todaythreads=todaythreads+1, lastpost='$_G[timestamp]' WHERE uid='$_G[uid]'");

		showmessage(lang('category/template', 'house_post_success'), $modurl.'?mod=view&tid='.$tid);
	}

} elseif($action == 'edit') {

	$thread = DB::fetch_first("SELECT * FROM ".DB::table('category_'.$modidentifier.'_thread')." WHERE tid='$tid'");

	if($_G['adminid'] != 1 && $thread['authorid'] != $_G['uid']) {
		showmessage(lang('category/template', 'house_no_edit'));
	}

	$tid = $thread['tid'];
	$sortid = $thread['sortid'];
	$message = $thread['message'];

	$sortdata = DB::fetch_first("SELECT tid, attachid, dateline, expiration, displayorder, recommend, groupid, city, district, street, mapposition FROM ".DB::table('category_sortvalue')."$sortid WHERE tid='$tid'");
	$expiration = $sortdata['expiration'] ? dgmdate($sortdata['expiration'], 'd') : '';

	$districtid = intval($sortdata['district']);
	$streetid = intval($sortdata['street']);
	$cityid = intval($sortdata['city']);

	$mapcenter = $arealist['city'][$cityid].' '.$arealist['district'][$cityid][$districtid].' '.$arealist['street'][$districtid][$streetid];
	$mapposition = empty($sortdata['mapposition']) ? '' : explode(',', $sortdata['mapposition']);

	loadcache(array('category_option_'.$sortid, 'category_template_'.$sortid));
	$_G['category_optionlist'] = $_G['cache']['category_option_'.$sortid];
	threadsort_checkoption($sortid);
	$category_sort = $sortlist[$sortid];

	if(!submitcheck('editsubmit')) {
		threadsort_optiondata($sortid, $_G['cache']['category_option_'.$sortid], $_G['cache']['category_template_'.$sortid], $tid);

		$attachs = array();
		if($sortdata['attachid']) {
			$query = DB::query("SELECT * FROM ".DB::table('category_'.$modidentifier.'_pic')." WHERE tid='$tid'");
			while($attach = DB::fetch($query)) {
				$attachs[] = $attach;
			}
		}

		if(count($attachs) < $category_sort['imgnum']) {
			$imgnum = array();
			$uploadnum = $category_sort['imgnum'] - count($attachs);
			$imgnum = array_pad($imgnum, $uploadnum, 0);
		}
	} else {
		$_G['category_optiondata'] = threadsort_validator($_G['gp_typeoption'], $pid);
		$_G['gp_expiration'] = $_G['gp_expiration'] ? ($sortdata['expiration'] ? $sortdata['expiration'] + intval($_G['gp_expiration']) : $_G['timestamp'] + intval($_G['gp_expiration'])) : $sortdata['expiration'];

		$sql = $separator = $newaidadd = '';
		foreach($_G['category_optiondata'] as $optionid => $value) {
			if(($_G['category_optionlist'][$optionid]['search'] || in_array($_G['category_optionlist'][$optionid]['type'], array('radio', 'select', 'number'))) && $value) {
				$sql .= $separator.$_G['category_optionlist'][$optionid]['identifier']."='$value'";
				$separator = ' ,';
			}
			DB::query("UPDATE ".DB::table('category_sortoptionvar')." SET value='$value', sortid='$sortid', expiration='$_G[gp_expiration]' WHERE tid='$tid' AND optionid='$optionid'");
		}
		
		if($sql) { 
			DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET $sql WHERE tid='$tid'");
		}

		if(!empty($subject) || !empty($message)) {
			$message = censor(trim($_G['gp_message']));
			DB::query("UPDATE ".DB::table('category_'.$modidentifier.'_thread')." SET subject='$subject', message='$message' WHERE tid='$tid'");
		}

		if($_G['gp_mapposition']) {
			DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET mapposition='$_G[gp_mapposition]' WHERE tid='$tid'");
		}

		if($_G['gp_expiration'] !=  $thread['expiration']) {
			DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET expiration='$_G[gp_expiration]' WHERE tid='$tid'");
		}

		if($_FILES) {
			threadsort_insertfile($tid, $_FILES, $sortid, 1, $modidentifier, $channel);
		} else {
			$newaid = substr($_G['gp_coverpic'], 4);
			if($newaid != $sortdata['attachid']) {
				DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET attachid='$newaid' WHERE tid='$tid'");
			}
		}

		if($_G['gp_deleteaids']) {
			$deleteaids = explode(',', $_G['gp_deleteaids']);
			$query = DB::query("SELECT * FROM ".DB::table('category_'.$modidentifier.'_pic')." WHERE tid='$tid' AND aid IN(".dimplode($deleteaids).")");
			while($row = DB::fetch($query)) {
				@unlink($_G['setting']['attachdir'].'/category/'.$row['url']);
				@unlink(DISCUZ_ROOT.'./data/attachment/image/'.$row['aid'].'_140_140_house.jpg');
				@unlink(DISCUZ_ROOT.'./data/attachment/image/'.$row['aid'].'_48_48_house.jpg');
			}
			DB::query("DELETE FROM ".DB::table('category_'.$modidentifier.'_pic')." WHERE tid='$tid' AND aid IN(".dimplode($deleteaids).")");
			if(in_array($sortdata['attachid'], $deleteaids)) {
				$newaid = DB::result_first("SELECT aid FROM ".DB::table('category_'.$modidentifier.'_pic')." WHERE tid='$tid' LIMIT 1");
				$newaidadd = empty($newaid) ?  ",attachid='".intval($newaid)."'" : '';
			}
			$attachnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('category_'.$modidentifier.'_pic')." WHERE tid='$tid'");
			DB::query("UPDATE ".DB::table('category_sortvalue')."$sortid SET attachnum='".intval($attachnum)."' $newaidadd WHERE tid='$tid'");
		}

		showmessage(lang('category/template', 'house_update_success'), $modurl.'?mod=view&tid='.$tid.'');
	}
}

include template('diy:category/category_post');