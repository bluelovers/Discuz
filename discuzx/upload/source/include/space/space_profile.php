<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_profile.php 16680 2010-09-13 03:01:08Z wangjinbo $
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

if($space['videophoto'] && ckvideophoto('viewphoto', $space, 1)) {
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
if($space['lastsendmail']) $space['groupexpiry'] = dgmdate($space['groupexpiry']);

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

require_once libfile('function/friend');
$isfriend = friend_check($space['uid']);

loadcache('profilesetting');
include_once libfile('function/profile');
$profiles = array();
$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();

foreach($_G['cache']['profilesetting'] as $fieldid=>$field) {
	if($field['available'] && $field['invisible'] != '1'
		&& ($space['self'] || empty($privacy[$fieldid]) || ($isfriend && $privacy[$fieldid] == 1))
		&& strlen($space[$fieldid]) > 0
		&& (!$_G['inajax'] || $field['showincard'])) {

		$val = profile_show($fieldid, $space);
		if($val !== false) {
			if ($val == '')  $val = '&nbsp;';
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

if($space['medals']) {
        loadcache('medals');
        foreach($space['medals'] = explode("\t", $space['medals']) as $key => $medalid) {
                list($medalid, $medalexpiration) = explode("|", $medalid);
                if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > $timestamp)) {
                        $space['medals'][$key] = $_G['cache']['medals'][$medalid];
                } else {
                        unset($space['medals'][$key]);
                }
        }
}

$upgradecredit = $space['uid'] && $space['group']['type'] == 'member' && $space['group']['creditslower'] != 999999999 ? $space['group']['creditslower'] - $space['credits'] : false;
$allowupdatedoing = $space['uid'] == $_G['uid'] && checkperm('allowdoing');

if($_G['setting']['verify']['enabled']) {
	space_merge($space, 'verify');
	$attachurl = getglobal('setting/attachurl');
}
dsetcookie('home_diymode', 1);

$navtitle = lang('space', 'sb_profile', array('who' => $space['username']));
$metakeywords = lang('space', 'sb_profile', array('who' => $space['username']));
$metadescription = lang('space', 'sb_profile', array('who' => $space['username']));

if(!$_G['privacy']) {
	if(!$_G['inajax']) {
		include_once template("home/space_profile");
	} else {
		include_once template("home/space_card");
	}
}

?>