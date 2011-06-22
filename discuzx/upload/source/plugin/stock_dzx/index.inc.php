<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-12
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') )
{
	exit('Access Denied');
}
$baseScript = 'plugin.php?id=stock_dzx:index';
$db_smname			= $_G['cache']['plugin']['stock_dzx']['smname'];
$db_smifopen		= $_G['cache']['plugin']['stock_dzx']['smifopen'];
$db_whysmclose		= $_G['cache']['plugin']['stock_dzx']['whysmclose'];
$db_smiftime		= $_G['cache']['plugin']['stock_dzx']['smiftime'];
$db_smtimer11		= $_G['cache']['plugin']['stock_dzx']['smtimer11'];
$db_smtimer12		= $_G['cache']['plugin']['stock_dzx']['smtimer12'];
$db_smtimer21		= $_G['cache']['plugin']['stock_dzx']['smtimer21'];
$db_smtimer22		= $_G['cache']['plugin']['stock_dzx']['smtimer22'];
$db_smtimer31		= $_G['cache']['plugin']['stock_dzx']['smtimer31'];
$db_smtimer32		= $_G['cache']['plugin']['stock_dzx']['smtimer32'];
$db_smtimer41		= $_G['cache']['plugin']['stock_dzx']['smtimer41'];
$db_smtimer42		= $_G['cache']['plugin']['stock_dzx']['smtimer42'];
$db_ss				= $_G['cache']['plugin']['stock_dzx']['ss'];
$db_klcolor			= $_G['cache']['plugin']['stock_dzx']['klcolor'];
$db_marketpp		= $_G['cache']['plugin']['stock_dzx']['marketpp'];
$db_otherpp			= $_G['cache']['plugin']['stock_dzx']['otherpp'];
$db_trustlog		= $_G['cache']['plugin']['stock_dzx']['trustlog'];
$db_guestview		= $_G['cache']['plugin']['stock_dzx']['guestview'];
$db_usertrade		= $_G['cache']['plugin']['stock_dzx']['usertrade'];
$db_tradedelay		= $_G['cache']['plugin']['stock_dzx']['tradedelay'];
$db_tradenummin		= $_G['cache']['plugin']['stock_dzx']['tradenummin'];
$db_dutyrate		= $_G['cache']['plugin']['stock_dzx']['dutyrate'];
$db_dutymin			= $_G['cache']['plugin']['stock_dzx']['dutymin'];
$db_wavemax			= $_G['cache']['plugin']['stock_dzx']['wavemax'];
$db_rsm				= $_G['cache']['plugin']['stock_dzx']['rsm'];
$db_fsm				= $_G['cache']['plugin']['stock_dzx']['fsm'];
$db_stop			= $_G['cache']['plugin']['stock_dzx']['stop'];
$db_esifopen		= $_G['cache']['plugin']['stock_dzx']['esifopen'];
$db_esminnum		= $_G['cache']['plugin']['stock_dzx']['esminnum'];
$db_esnamemin		= $_G['cache']['plugin']['stock_dzx']['esnamemin'];
$db_esnamemax		= $_G['cache']['plugin']['stock_dzx']['esnamemax'];
$db_introducemax	= $_G['cache']['plugin']['stock_dzx']['introducemax'];
$db_issuedays		= $_G['cache']['plugin']['stock_dzx']['issuedays'];
$db_allowregister	= $_G['cache']['plugin']['stock_dzx']['allowregister'];
$db_minmoney		= $_G['cache']['plugin']['stock_dzx']['minmoney'];
$db_mincredit		= $_G['cache']['plugin']['stock_dzx']['mincredit'];
$db_minpost			= $_G['cache']['plugin']['stock_dzx']['minpost'];
$db_initialmoney	= $_G['cache']['plugin']['stock_dzx']['initialmoney'];
$db_allowdeposit	= $_G['cache']['plugin']['stock_dzx']['allowdeposit'];
$db_allowadopt		= $_G['cache']['plugin']['stock_dzx']['allowadopt'];
$db_allowtransfer	= $_G['cache']['plugin']['stock_dzx']['allowtransfer'];
$db_depositmin		= $_G['cache']['plugin']['stock_dzx']['depositmin'];
$db_adoptmin		= $_G['cache']['plugin']['stock_dzx']['adoptmin'];
$db_transfermin		= $_G['cache']['plugin']['stock_dzx']['transfermin'];
$db_charge			= $_G['cache']['plugin']['stock_dzx']['charge'];
$db_transfercharge	= $_G['cache']['plugin']['stock_dzx']['transfercharge'];
$db_credittype		= $_G['cache']['plugin']['stock_dzx']['credittype'];
$db_proportion		= $_G['cache']['plugin']['stock_dzx']['proportion'];
$db_meloncutting	= $_G['cache']['plugin']['stock_dzx']['meloncutting'];
require_once 'require/kfsclass.php';
$kfsclass = new kfsclass;
$kfsclass->auto_run();
$hkimg = 'source/plugin/stock_dzx/image/';
$mod = empty($_G['gp_mod']) ? 'index' : $_G['gp_mod'];
$modArray = array('index', 'stock', 'member', 'notice', 'news', 'ajax', 'system');
try
{
	if ( !in_array($mod, $modArray) )
		throw new Exception ('非法操作');
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
		$nsinfo = $index->subscribeCountDown();
		$nsdb = $index->getNewStocks(10);
		$sminfo = $index->getFoxAIN();
		require_once 'require/class_news.php';
		$news = new News;
		$newsList = $news->getLatestNews(10);
		require_once 'require/mod_stock.php';
		$stock = new Stock('call');
		$rtdb = $stock->getRisedTop(5);
		$ftdb = $stock->getFalledTop(5);
		include template('stock_dzx:index');
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
