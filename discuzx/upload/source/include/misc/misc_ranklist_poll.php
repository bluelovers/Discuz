<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$polllist = '';
$orderby = in_array($_G['gp_orderby'], array('thisweek', 'thismonth', 'today', 'all')) ? $_G['gp_orderby'] : '';
$navname = $_G['setting']['navs'][8]['navname'];
switch($_G['gp_view']) {
	case 'heats':
		$gettype = 'heat';
		break;
	case 'sharetimes':
		$gettype = 'share';
		break;
	case 'favtimes':
		$gettype = 'favorite';
		break;
	default:
		$_G['gp_view'] = 'heats';
}
$view = $_G['gp_view'];

$polllist = getranklistdata($type, $view, $orderby);
$lastupdate = $_G['lastupdate'];
$nextupdate = $_G['nextupdate'];

$navtitle = lang('ranklist/navtitle', 'ranklist_title_poll_'.$gettype).' - '.$navname;
$metakeywords = lang('ranklist/navtitle', 'ranklist_title_poll_'.$gettype);
$metadescription = lang('ranklist/navtitle', 'ranklist_title_poll_'.$gettype);

include template('diy:ranklist/poll');

?>