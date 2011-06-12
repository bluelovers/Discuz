<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-10
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') || !defined('IN_ADMINCP') )
{
	exit('Access Denied');
}
$baseScript = 'action=plugins&operation=config&do='.$pluginid.'&identifier=stock_dzx&pmod=admincp';
require_once 'require/kfsclass.php';
$mod = empty($_G['gp_mod']) ? 'index' : $_G['gp_mod'];
$modArray = array('index', 'about', 'stockset', 'userset', 'trusts', 'esset', 'usmng', 'news', 'tools', 'logs');
try
{
	if ( !in_array($mod, $modArray) )
		throw new Exception ('Invalid module');
}
catch ( Exception $e )
{
	cpmsg('Messages from Kilofox StockIns：'.$e->getMessage());
}
switch ( $mod )
{
	case 'index':
	case 'about':
		$kfsclass = new kfsclass;
		require_once 'require/acp_apply.php';
		$apply = new Apply;
		$newApplyNum	= $apply->getNewApplyNum();
		$rs = DB::query("SELECT * FROM kfsm_sminfo");
		$sminfo['bargainmoney']	= number_format($rs['todaytotal'],2);
		$sminfo['bargainnum']	= $rs['todaybuy'] + $rs['todaysell'];
		$sminfo['stampduty']	= number_format($rs['stampduty'],2);
		include template('stock_dzx:m_about');
	break;
	case 'stockset':
		require_once 'require/acp_stocks.php';
		$stock = new Stocks;
		$section	= $_G['gp_section'];
		$step		= $_G['gp_step'];
		if ( empty($section) )
		{
			list($stockdb,$cnt,$readperpage,$pages) = $stock->getStockList();
			include template('stock_dzx:m_stockset');
		}
		else if ( $section == 'editstock' )
		{
			if ( empty($step) )
			{
				list($v,$cas,$introducemax) = $stock->getStockInfo($_G['gp_sid']);
				include template('stock_dzx:m_editstock');
			}
			else if ( $step == '2' )
			{
				$stock->updateStock();
			}
		}
		else if ( $section == 'delstock' )
		{
			if ( empty($step) )
			{
				$v = $stock->deleteStock($_G['gp_sid']);
				include template('stock_dzx:m_delstock');
			}
			else if ( $step == '2' )
			{
				$stock->exeDeleteStock();
			}
		}
	break;
	case 'userset':
		require_once 'require/acp_users.php';
		$user = new Users;
		$section	= $_G['gp_section'];
		$step		= $_G['gp_step'];
		if ( empty($section) )
		{
			list($userdb, $cnt, $readperpage, $pages) = $user->getUserList();
			include template('stock_dzx:m_userset');
		}
		else if ( $section == 'edituser' )
		{
			if ( empty($step) )
			{
				list($v,$userlock,$userunlock) = $user->editUser($_G['gp_uid']);
				include template('stock_dzx:m_edituser');
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
				include template('stock_dzx:m_deluser');
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
			include template('stock_dzx:m_trust');
		}
		else if ( $section == 'tran' )
		{
			$tdb = $trust->getTranList();
			include template('stock_dzx:m_trust');
		}
		else if ( $section == 'canc' )
		{
			$tdb = $trust->getCancList();
			include template('stock_dzx:m_trust');
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
	case 'esset':
		require_once 'require/acp_apply.php';
		$apply = new Apply;
		$applyList = $apply->getApplyList();
		include template('stock_dzx:m_applyset');
	break;
	case 'usmng':
		require_once 'require/acp_apply.php';
		$apply = new Apply;
		$apply->userStockManage($_G['gp_aid']);
	break;
	case 'news':
		$section	= $_G['gp_section'];
		require_once 'require/acp_news.php';
		$news = new News;
		if ( empty($section) )
		{
			$newsdb = $news->getNewsList();
			include template('stock_dzx:m_news');
		}
		else if ( $section == 'addnews' )
		{
			if ( $_POST['step'] != '2' )
			{
				include template('stock_dzx:m_news');
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
				include template('stock_dzx:m_news');
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
			list($stockdb,$stampduty) = $tool->toollist();
			include template('stock_dzx:m_tools');
		}
		else if ( $section == 'reset' )
		{
			$tool->kfsmreset();
		}
		else if ( $section == 'divide' )
		{
			$tool->divide($_G['gp_divide_number']);
		}
		else if ( $section == 'forcesell' )
		{
			$tool->forcesell($_G['gp_stockId'],$_G['gp_confirm']);
		}
		else if ( $section == 'distribute' )
		{
			$tool->distribute();
		}
	break;
	case 'logs':
		require_once 'require/acp_logs.php';
		$logs = new Logs;
		$section = $_G['gp_section'];
		if ( empty($section) )
		{
			list($logdb, $cnt, $readperpage, $pages) = $logs->getLogList();
			include template('stock_dzx:m_logs');
		}
		else if ( $section == 'dellogs' )
		{
			$logs->deleteLogs();
		}
	break;
}
?>
