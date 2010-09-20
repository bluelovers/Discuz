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
$clicks = empty($_G['cache']['click']['blogid'])?array():$_G['cache']['click']['blogid'];

$bloglist = '';
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
$orderby = 'hot DESC';
$navname = $_G['setting']['navs'][8]['navname'] . ' - ' . $_G['setting']['bbname'];
switch($_G['gp_orderby']) {
	case 'heats':
		$orderby = 'hot DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_blog_heat').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_blog_heat');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_blog_heat');
		break;
	case 'replies':
		$orderby = 'replynum DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_blog_reply').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_blog_reply');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_blog_reply');
		break;
	case 'views':
		$orderby = 'viewnum DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_blog_view').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_blog_view');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_blog_view');
		break;
	case 'sharetimes':
		$orderby = 'sharetimes DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_blog_share').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_blog_share');
		$metadescription = lang('ranklist/navtitle', 'ranklist_title_blog_share');
		break;
	case 'favtimes':
		$orderby = 'favtimes DESC';
		$navtitle = lang('ranklist/navtitle', 'ranklist_title_blog_favorite').' - '.$navname;
		$metakeywords = lang('ranklist/navtitle', 'ranklist_title_blog_favorite');
		$metadescription = lang('rankilist/template', 'ranklist_title_blog_favorite');
		break;
	default:
		if($clicks[$_G['gp_orderby']]) {
			$orderby = 'click'.$_G['gp_orderby'].' DESC';
			$navtitle = lang('ranklist/navtitle', 'ranklist_title_blog_'.$_G['gp_orderby']).' - '.$navname;
			$metakeywords = lang('ranklist/navtitle', 'ranklist_title_blog_'.$_G['gp_orderby']);
			$metadescription = lang('ranklist/navtitle', 'ranklist_title_blog_'.$_G['gp_orderby']);
		} else {
			$_G['gp_orderby'] = 'heats';
		}
}

$dateline = !empty($before) ? TIMESTAMP - $before : 0;

$bloglist = getranklistcache_blogs();
$lastupdate = $bloglist['lastupdate'];
$nextupdate = $bloglist['nextupdate'];
unset($bloglist['lastupdated'], $bloglist['lastupdate'], $bloglist['nextupdate']);

$urladd = '';
if($_G['gp_orderby']) {
	$urladd .= "&orderby={$_G['gp_orderby']}";
}
if($_G['gp_before']) {
	$urladd .= "&before={$_G['gp_before']}";
}

include template('diy:ranklist/blog');

function getranklistcache_blogs() {
	global $_G, $cache_time, $cache_num, $dateline, $orderby;
	$ranklistvars = array();
	loadcache('ranklist_blog');
	$ranklistvars = & $_G['cache']['ranklist_blog'][$_G['gp_before'].'_'.$_G['gp_orderby']];

	if(!empty($ranklistvars['lastupdated']) && TIMESTAMP - $ranklistvars['lastupdated'] < $cache_time) {
		return $ranklistvars;
	}

	$ranklistvars = getranklist_blogs($cache_num, $dateline, $orderby);

	$ranklistvars['lastupdated'] = TIMESTAMP;
	$ranklistvars['lastupdate'] = dgmdate(TIMESTAMP);
	$ranklistvars['nextupdate'] = dgmdate(TIMESTAMP + $cache_time);
	$_G['cache']['ranklist_blog'][$_G['gp_before'].'_'.$_G['gp_orderby']] = $ranklistvars;
	save_syscache('ranklist_blog', $_G['cache']['ranklist_blog']);
	return $ranklistvars;
}

?>