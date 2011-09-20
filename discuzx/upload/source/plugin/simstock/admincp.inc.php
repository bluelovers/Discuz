<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-06-19
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') || !defined('IN_ADMINCP') )
{
	exit('Access Denied');
}
$baseScript = 'action=plugins&operation=config&do='.$pluginid.'&identifier=simstock&pmod=admincp';
require_once 'require/kfsclass.php';
$mod = empty($_G['gp_mod']) ? 'index' : $_G['gp_mod'];
$modArray = array('index', 'about', 'userset', 'trusts', 'news', 'tools', 'logs');
try
{
	if ( !in_array($mod, $modArray) )
		throw new Exception ('Invalid module');
}
catch ( Exception $e )
{
	cpmsg('Messages from Kilofox StockIns£º'.$e->getMessage());
}
switch ( $mod )
{
	case 'index':
	case 'about':
		include template('simstock:m_about');
	break;
	case 'userset':
		require_once 'require/acp_users.php';
		$user = new Users;
		$section	= $_G['gp_section'];
		$step		= $_G['gp_step'];
		if ( empty($section) )
		{
			list($userdb, $cnt, $readperpage, $pages) = $user->getUserList();
			include template('simstock:m_userset');
		}
		else if ( $section == 'edituser' )
		{
			if ( empty($step) )
			{
				list($v,$userlock,$userunlock) = $user->editUser($_G['gp_uid']);
				include template('simstock:m_edituser');
			}
			else if ( $step == '2' )
			{
				$user->updateUser();
			}
		}
		else if ( $section == 'deluser' )
		{
			if ( empty($step) )
			{
				$rs = $user->deleteUser($_G['gp_uid']);
				include template('simstock:m_deluser');
			}
			else if ( $step == '2' )
			{
				$user->exeDeleteUser();
			}
		}
	break;
	case 'trusts':
		require_once 'require/acp_trusts.php';
		$trust = new Trusts;
		$section = $_G['gp_section'];
		if ( empty($section) )
		{
			$ddb = $trust->getDealList();
			include template('simstock:m_trust');
		}
		else if ( $section == 'tran' )
		{
			$tdb = $trust->getTranList();
			include template('simstock:m_trust');
		}
		else if ( $section == 'canc' )
		{
			$tdb = $trust->getCancList();
			include template('simstock:m_trust');
		}
		else if ( $section == 'deldeals' )
		{
			$trust->deleteDeals();
		}
		else if ( $section == 'deltrans' )
		{
			$trust->deleteTrans();
		}
		else if ( $section == 'delcancs' )
		{
			$trust->deleteCancs();
		}
	break;
	case 'news':
		$section	= $_G['gp_section'];
		require_once 'require/acp_news.php';
		$news = new News;
		if ( empty($section) )
		{
			$newsdb = $news->getNewsList();
			include template('simstock:m_news');
		}
		else if ( $section == 'addnews' )
		{
			if ( $_POST['step'] != '2' )
			{
				include template('simstock:m_news');
			}
			else
			{
				$news->saveNewNews();
			}
		}
		else if ( $section == 'editnews' )
		{
			$section	= $_G['gp_section'];
			$step		= $_G['gp_step'];
			if ( !$step )
			{
				$v = $news->getNewsInfo($_G['gp_nid']);
				include template('simstock:m_news');
			}
			else
			{
				$news->updateNews();
			}
		}
		else if ( $section == 'delnews' )
		{
			$news->deleteNews($_G['gp_nid']);
		}
	break;
	case 'tools':
		require_once 'require/acp_tools.php';
		$tool = new Tools;
		$basename .= "&action=tools";
		$section = $_G['gp_section'];
		if ( empty($section) )
		{
			include template('simstock:m_tools');
		}
		else if ( $section == 'reset' )
		{
			$tool->kfsmReset();
		}
		else if ( $section == 'udrank' )
		{
			$tool->udRank();
		}
	break;
	case 'logs':
		require_once 'require/acp_logs.php';
		$logs = new Logs;
		$section = $_G['gp_section'];
		if ( empty($section) )
		{
			list($logdb, $cnt, $readperpage, $pages) = $logs->getLogList();
			include template('simstock:m_logs');
		}
		else if ( $section == 'dellogs' )
		{
			$logs->deleteLogs();
		}
	break;
}
?>
