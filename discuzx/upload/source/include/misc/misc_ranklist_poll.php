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

$polllist = '';
$before = '604800';
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
	default: $_G['gp_before'] = 'thisweek';
}
$orderby = 'heats DESC';
$navname = $_G['setting']['navs'][8]['navname'];
switch($_G['gp_orderby']) {
	case 'heats':
		$orderby = 'heats DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_poll_heat').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_poll_heat');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_poll_heat');
		break;
	case 'sharetimes':
		$orderby = 'sharetimes DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_poll_share').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_poll_share');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_poll_share');
		break;
	case 'favtimes':
		$orderby = 'favtimes DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_poll_favorite').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_poll_favorite');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_poll_favorite');
		break;
	default:
		$_G['gp_orderby'] = 'heats';
}

$dateline = !empty($before) ? TIMESTAMP - $before : 0;

$polllist = getranklistcache_polls();
$lastupdate = $polllist['lastupdate'];
$nextupdate = $polllist['nextupdate'];
unset($polllist['lastupdated'], $polllist['lastupdate'], $polllist['nextupdate']);

$urladd = '';
if($_G['gp_orderby']) {
	$urladd .= "&orderby={$_G['gp_orderby']}";
}
if($_G['gp_before']) {
	$urladd .= "&before={$_G['gp_before']}";
}

include template('diy:ranklist/poll');

function getranklistcache_polls() {
	global $_G, $cache_time, $cache_num, $dateline, $orderby;
	$ranklistvars = array();
	loadcache('ranklist_poll');
	$ranklistvars = & $_G['cache']['ranklist_poll'][$_G['gp_before'].'_'.$_G['gp_orderby']];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_polls($cache_num, $dateline, $orderby);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_poll'][$_G['gp_before'].'_'.$_G['gp_orderby']] = $ranklistvars;
	save_syscache('ranklist_poll', $_G['cache']['ranklist_poll']);
	return $ranklistvars;
}

?>