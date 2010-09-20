<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$ranklist_setting[$type]['available']) {
	showmessage('ranklist_this_status_off');
}
$cache_time = $ranklist_setting[$type]['cache_time'];
$cache_num =  $ranklist_setting[$type]['show_num'];
if($cache_time <= 0 ) $cache_time = 5;
$cache_time = $cache_time * 3600;
if($cache_num <= 0 ) $cache_num = 20;

loadcache('click');
$clicks = empty($_G['cache']['click']['picid'])?array():$_G['cache']['click']['picid'];

$picturelist = '';
$before = '2592000';
switch($_G['gp_before']) {
	case 'all':
		$before = '0';
		break;
	case 'today':
		$before = '86400';
		break;
	case 'thisweek':
		$before = '604800';
		break;
	case 'thismonth':
		$before = '2592000';
		break;
	default: $_G['gp_before'] = 'thismonth';
}

$orderby = 'hot DESC';
$navname = $_G['setting']['navs'][8]['navname'];
switch($_G['gp_orderby']) {
	case 'heats':
		$orderby = 'hot DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_picture_heat').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_picture_heat');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_picture_heat');
		break;
	case 'sharetimes':
		$orderby = 'sharetimes DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_picture_share'). ' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_picture_share');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_picture_share');
		break;
	default:
		if($clicks[$_G['gp_orderby']]) {
			$orderby = 'click'.$_G['gp_orderby'].' DESC';
			$navtitle = lang('ranklist/navtitle', 'ranklist_title_picture_'.$_G['gp_orderby']).' - '.$navname;
			$metakeywords = lang('ranklist/navtitle', 'ranklist_title_picture_'.$_G['gp_orderby']);
			$metadescription = lang('ranklist/navtitle', 'ranklist_title_picture_'.$_G['gp_orderby']);
		} else {
			$_G['gp_orderby'] = 'heats';
		}
}

$dateline = !empty($before) ? TIMESTAMP - $before : 0;

$picturelist = getranklistcache_pictures();
$lastupdate = $picturelist['lastupdate'];
$nextupdate = $picturelist['nextupdate'];
unset($picturelist['lastupdated'], $picturelist['lastupdate'], $picturelist['nextupdate']);

$urladd = '';
if($_G['gp_orderby']) {
	$urladd .= "&orderby={$_G['gp_orderby']}";
}
if($_G['gp_before']) {
	$urladd .= "&before={$_G['gp_before']}";
}

include template('diy:ranklist/picture');

function getranklistcache_pictures() {
	global $_G, $cache_time, $cache_num, $dateline, $orderby;
	$ranklistvars = array();
	loadcache('ranklist_picture');
	$ranklistvars = & $_G['cache']['ranklist_picture'][$_G['gp_before'].'_'.$_G['gp_orderby']];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_pictures($cache_num, $dateline, $orderby);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_picture'][$_G['gp_before'].'_'.$_G['gp_orderby']] = $ranklistvars;
	save_syscache('ranklist_picture', $_G['cache']['ranklist_picture']);
	return $ranklistvars;
}

?>