<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_profile.php 26640 2011-12-19 02:31:59Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/spacecp');

space_merge($space, 'count');
space_merge($space, 'field_home');
space_merge($space, 'field_forum');
space_merge($space, 'profile');
space_merge($space, 'status');
getonlinemember(array($space['uid']));

if($space['videophoto'] && ckvideophoto($space, 1)) {
	$space['videophoto'] = getvideophoto($space['videophoto']);
} else {
	$space['videophoto'] = '';
}

$space['admingroup'] = $_G['cache']['usergroups'][$space['adminid']];
$space['admingroup']['icon'] = g_icon($space['adminid'], 1);

$space['group'] = $_G['cache']['usergroups'][$space['groupid']];
$space['group']['icon'] = g_icon($space['groupid'], 1);

if($space['extgroupids']) {
	$newgroup = array();
	$e_ids = explode(',', $space['extgroupids']);
	foreach ($e_ids as $e_id) {
		$newgroup[] = $_G['usergroups'][$e_id]['grouptitle'];
	}
	$space['extgroupids'] = implode(',', $newgroup);
}

$space['regdate'] = dgmdate($space['regdate']);
if($space['lastvisit']) $space['lastvisit'] = dgmdate($space['lastvisit']);
if($space['lastactivity']) {
	$space['lastactivitydb'] = $space['lastactivity'];
	$space['lastactivity'] = dgmdate($space['lastactivity']);
}
if($space['lastpost']) $space['lastpost'] = dgmdate($space['lastpost']);
if($space['lastsendmail']) $space['lastsendmail'] = dgmdate($space['lastsendmail']);


if($_G['uid'] == $space['uid'] || $_G['group']['allowviewip']) {
	require_once libfile('function/misc');
	$space['regip_loc'] = convertip($space['regip']);
	$space['lastip_loc'] = convertip($space['lastip']);
}

$space['buyerrank'] = 0;
if($space['buyercredit']){
	foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
		if($space['buyercredit'] <= $credit) {
			$space['buyerrank'] = $level;
			break;
		}
	}
}

$space['sellerrank'] = 0;
if($space['sellercredit']){
	foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
		if($space['sellercredit'] <= $credit) {
			$space['sellerrank'] = $level;
			break;
		}
	}
}

$space['attachsize'] = formatsize($space['attachsize']);

$space['timeoffset'] = empty($space['timeoffset']) ? '9999' : $space['timeoffset'];
if(strtotime($space['regdate']) + $space['oltime'] * 3600 > TIMESTAMP) {
	$space['oltime'] = 0;
}
require_once libfile('function/friend');
$isfriend = friend_check($space['uid'], 1);

loadcache('profilesetting');
include_once libfile('function/profile');
$profiles = array();
$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();

if($_G['setting']['verify']['enabled']) {
	space_merge($space, 'verify');
}
foreach($_G['cache']['profilesetting'] as $fieldid => $field) {
	if(!$field['available'] || in_array($fieldid, array('birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
			continue;
	}
	if(
		$field['available'] && strlen($space[$fieldid]) > 0 &&
		(
			$field['showinthread'] ||
			$field['showincard'] ||
			(
				$space['self'] || empty($privacy[$fieldid]) || ($isfriend && $privacy[$fieldid] == 1)
			)
		) &&
		(!$_G['inajax'] && $field['invisible'] != '1' || $_G['inajax'] && $field['showincard'])
	) {
		$val = profile_show($fieldid, $space);
		if($val !== false) {
			if($fieldid == 'realname' && $_G['uid'] != $space['uid'] && !ckrealname(1)) {
				continue;
			}
			if($field['formtype'] == 'file' && $val) {
				$imgurl = getglobal('setting/attachurl').'./profile/'.$val;
				$val = '<span><a href="'.$imgurl.'" target="_blank"><img src="'.$imgurl.'"  style="max-width: 500px;" /></a></span>';
			}
			if($val == '')  $val = '-';
			$profiles[$fieldid] = array('title'=>$field['title'], 'value'=>$val);
		}
	}
}

$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_moderator')." WHERE uid = '$space[uid]'"), 0);
if($count) {
	$query = DB::query("SELECT f.name,f.fid AS fid FROM ".DB::table('forum_moderator').
		" m LEFT JOIN ".DB::table('forum_forum')." f USING(fid) WHERE  uid = '$space[uid]'");
	while($result = DB::fetch($query)) {
		$manage_forum[$result['fid']] = $result['name'];
	}
}

if(!$_G['inajax'] && $_G['setting']['groupstatus']) {
	$groupcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_groupuser')." WHERE uid = '{$space['uid']}'");
	if($groupcount > 0) {
		$query = DB::query("SELECT fg.fid, ff.name FROM ".DB::table('forum_groupuser')." fg LEFT JOIN ".DB::table('forum_forum')." ff USING(fid) WHERE fg.uid = '{$space['uid']}' LIMIT $groupcount");
		while ($result = DB::fetch($query)) {
			$usergrouplist[] = $result;
		}
	}
}

if($space['medals']) {
        loadcache('medals');
        foreach($space['medals'] = explode("\t", $space['medals']) as $key => $medalid) {
                list($medalid, $medalexpiration) = explode("|", $medalid);
                if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
                        $space['medals'][$key] = $_G['cache']['medals'][$medalid];
                        $space['medals'][$key]['medalid'] = $medalid;
                } else {
                        unset($space['medals'][$key]);
                }
        }
}
$upgradecredit = $space['uid'] && $space['group']['type'] == 'member' && $space['group']['creditslower'] != 9999999 ? $space['group']['creditslower'] - $space['credits'] : false;
$allowupdatedoing = $space['uid'] == $_G['uid'] && checkperm('allowdoing');

dsetcookie('home_diymode', 1);

$navtitle = lang('space', 'sb_profile', array('who' => $space['username']));
$metakeywords = lang('space', 'sb_profile', array('who' => $space['username']));
$metadescription = lang('space', 'sb_profile', array('who' => $space['username']));

$showvideophoto = true;
if($space['videophotostatus'] > 0 && $_G['uid'] != $space['uid'] && !ckvideophoto($space, 1)) {
	$showvideophoto = false;
}

if(!$_G['privacy']) {
	if(!$_G['inajax']) {
		include_once template("home/space_profile");
	} else {
		$_G['gp_do'] = 'card';
		include_once template("home/space_card");
	}
}
?>