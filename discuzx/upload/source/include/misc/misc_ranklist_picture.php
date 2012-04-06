<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('click');
$clicks = empty($_G['cache']['click']['picid'])?array():$_G['cache']['click']['picid'];

$picturelist = '';
$orderby = in_array($_G['gp_orderby'], array('thisweek', 'thismonth', 'today', 'all')) ? $_G['gp_orderby'] : '';

$navname = $_G['setting']['navs'][8]['navname'];
switch($_G['gp_view']) {
	case 'hot':
		$view = 'hot';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_picture_heat').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_picture_heat');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_picture_heat');
		break;
	case 'sharetimes':
		$view = 'sharetimes';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_picture_share'). ' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_picture_share');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_picture_share');
		break;
	default:
		if($clicks[$_G['gp_view']]) {
			$view = 'click'.$_G['gp_view'];
			$navtitle = lang('ranklist/navtitle', 'ranklist_title_picture_'.$_G['gp_view']).' - '.$navname;
			$metakeywords = lang('ranklist/navtitle', 'ranklist_title_picture_'.$_G['gp_view']);
			$metadescription = lang('ranklist/navtitle', 'ranklist_title_picture_'.$_G['gp_view']);
		} else {
			$_G['gp_view'] = 'hot';
			$view = 'hot';
		}
}

$picturelist = getranklistdata($type, $view, $orderby);
$lastupdate = $_G['lastupdate'];
$nextupdate = $_G['nextupdate'];

include template('diy:ranklist/picture');

?>