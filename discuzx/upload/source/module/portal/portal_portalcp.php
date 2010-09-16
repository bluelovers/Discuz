<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_portalcp.php 14676 2010-08-13 05:53:20Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$ac = in_array($_GET['ac'], array('comment', 'article', 'related', 'block', 'portalblock', 'blockdata', 'topic', 'diy', 'upload', 'category', 'plugin', 'logout'))?$_GET['ac']:'index';

if (!$_G['inajax'] && in_array($ac, array('index', 'portalblock', 'blockdata', 'category')) && ($_G['group']['allowmanagearticle'] || $_G['group']['allowauthorizedarticle'] || $_G['group']['allowauthorizedblock'] || $_G['group']['allowdiy'])) {
	require_once libfile('class/panel');
	$modsession = new discuz_panel(PORTALCP_PANEL);
	if(getgpc('login_panel') && getgpc('cppwd') && submitcheck('submit')) {
		$modsession->dologin($_G[uid], getgpc('cppwd'), true);
	}

	if(!$modsession->islogin) {
		include template('portal/portalcp_login');
		dexit();
	}
}

if($ac == 'logout') {
	require_once libfile('class/panel');
	$modsession = new discuz_panel(PORTALCP_PANEL);
	$modsession->dologout();
	showmessage('modcp_logout_succeed', 'portal.php');
}

$navtitle = lang('core', 'title_'.$ac.'_management').' - '.lang('core', 'title_portal_management');

require_once libfile('function/portalcp');
require_once libfile('portalcp/'.$ac, 'include');
?>