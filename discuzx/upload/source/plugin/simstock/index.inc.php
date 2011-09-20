<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-09-20
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') )
{
	exit('Access Denied');
}
$baseScript = 'plugin.php?id=simstock:index';
$db_smname			= $_G['cache']['plugin']['simstock']['smname'];
$db_smifopen		= $_G['cache']['plugin']['simstock']['smifopen'];
$db_whysmclose		= $_G['cache']['plugin']['simstock']['whysmclose'];
$db_marketpp		= $_G['cache']['plugin']['simstock']['marketpp'];
$db_otherpp			= $_G['cache']['plugin']['simstock']['otherpp'];
$db_trustlog		= $_G['cache']['plugin']['simstock']['trustlog'];
$db_usertrade		= $_G['cache']['plugin']['simstock']['usertrade'];
$db_allowregister	= $_G['cache']['plugin']['simstock']['allowregister'];
$db_minmoney		= $_G['cache']['plugin']['simstock']['minmoney'];
$db_mincredit		= $_G['cache']['plugin']['simstock']['mincredit'];
$db_minpost			= $_G['cache']['plugin']['simstock']['minpost'];
$db_initialmoney	= $_G['cache']['plugin']['simstock']['initialmoney'];
$db_allowdeposit	= $_G['cache']['plugin']['simstock']['allowdeposit'];
$db_allowadopt		= $_G['cache']['plugin']['simstock']['allowadopt'];
$db_allowtransfer	= $_G['cache']['plugin']['simstock']['allowtransfer'];
$db_depositmin		= $_G['cache']['plugin']['simstock']['depositmin'];
$db_adoptmin		= $_G['cache']['plugin']['simstock']['adoptmin'];
$db_transfermin		= $_G['cache']['plugin']['simstock']['transfermin'];
$db_charge			= $_G['cache']['plugin']['simstock']['charge'];
$db_transfercharge	= $_G['cache']['plugin']['simstock']['transfercharge'];
$db_credittype		= $_G['cache']['plugin']['simstock']['credittype'];
$db_proportion		= $_G['cache']['plugin']['simstock']['proportion'];
require_once 'require/kfsclass.php';
$kfsclass = new kfsclass;
$kfsclass->auto_run();
$hkimg = 'source/plugin/simstock/image/';
$mod = empty($_G['gp_mod']) ? 'index' : $_G['gp_mod'];
$modArray = array('index', 'stock', 'member', 'notice', 'news', 'ajax', 'system', 'ras');
try
{
	if ( !in_array($mod, $modArray) )
		throw new Exception ('·Ç·¨²Ù×÷');
}
catch ( Exception $e )
{
	showmessage('Messages from Kilofox StockIns: '.$e->getMessage());
}
switch ( $mod )
{
	case 'index':
		require_once 'require/class_index.php';
		$index = new Index;
		$sminfo = $index->getFoxAIN();
		require_once 'require/class_news.php';
		$news = new News;
		$newsList = $news->getLatestNews(10);
		require_once 'require/mod_stock.php';
		include template('simstock:index');
	break;
	case 'stock':
		require_once 'require/mod_stock.php';
		new Stock($_G['gp_act']);
	break;
	case 'member':
		require_once 'require/mod_member.php';
		$user = new Member($_G['uid'], $_G['gp_act']);
		$user->processAction($_G['gp_act']);
	break;
	case 'news':
		require_once 'require/class_news.php';
		$news = new News;
		if ( $_G['gp_act'] == 'shownewslist' )
		{
			$news->showNewsList();
		}
		else if ( $_G['gp_act'] == 'shownewsinfo' )
		{
			$news->showNewsInfo($_G['gp_nid']);
		}
	break;
	case 'system':
		if ( $_G['gp_act'] == 'register' )
		{
			require_once 'require/class_register.php';
			$register = new Register;
			$register->create_account();
		}
		else if ( $_G['gp_act'] == 'help' )
		{
			require_once 'require/class_help.php';
			new Help;
		}
		else if ( $_G['gp_act'] == 'topuser' )
		{
			require_once 'require/class_topuser.php';
			$topuser = new Topuser;
			$topuser->showTopUser();
		}
	break;
	case 'ajax':
		if ( $_G['gp_section'] )
		{
			require_once 'require/class_ajax.php';
			$ajax = new Ajax($_G['gp_section']);
		}
	break;
}
?>
