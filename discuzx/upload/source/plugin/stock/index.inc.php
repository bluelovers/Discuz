<?php
/*
 * Kilofox Services
 * StockIns v9.5
 * Plug-in for Discuz!
 * Last Updated: 2011-08-08
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
if ( !defined('IN_DISCUZ') )
{
	exit('Access Denied');
}
$baseScript = 'plugin.php?id=stock:index';
$db_smname			= $_G['cache']['plugin']['stock']['smname'];
$db_smifopen		= $_G['cache']['plugin']['stock']['smifopen'];
$db_whysmclose		= $_G['cache']['plugin']['stock']['whysmclose'];
$db_smiftime		= $_G['cache']['plugin']['stock']['smiftime'];
$db_smtimer11		= $_G['cache']['plugin']['stock']['smtimer11'];
$db_smtimer12		= $_G['cache']['plugin']['stock']['smtimer12'];
$db_smtimer21		= $_G['cache']['plugin']['stock']['smtimer21'];
$db_smtimer22		= $_G['cache']['plugin']['stock']['smtimer22'];
$db_smtimer31		= $_G['cache']['plugin']['stock']['smtimer31'];
$db_smtimer32		= $_G['cache']['plugin']['stock']['smtimer32'];
$db_smtimer41		= $_G['cache']['plugin']['stock']['smtimer41'];
$db_smtimer42		= $_G['cache']['plugin']['stock']['smtimer42'];
$db_ss				= $_G['cache']['plugin']['stock']['ss'];
$db_klcolor			= $_G['cache']['plugin']['stock']['klcolor'];
$db_marketpp		= $_G['cache']['plugin']['stock']['marketpp'];
$db_otherpp			= $_G['cache']['plugin']['stock']['otherpp'];
$db_trustlog		= $_G['cache']['plugin']['stock']['trustlog'];
$db_guestview		= $_G['cache']['plugin']['stock']['guestview'];
$db_usertrade		= $_G['cache']['plugin']['stock']['usertrade'];
$db_tradedelay		= $_G['cache']['plugin']['stock']['tradedelay'];
$db_tradenummin		= $_G['cache']['plugin']['stock']['tradenummin'];
$db_dutyrate		= $_G['cache']['plugin']['stock']['dutyrate'];
$db_dutymin			= $_G['cache']['plugin']['stock']['dutymin'];
$db_wavemax			= $_G['cache']['plugin']['stock']['wavemax'];
$db_rsm				= $_G['cache']['plugin']['stock']['rsm'];
$db_fsm				= $_G['cache']['plugin']['stock']['fsm'];
$db_stop			= $_G['cache']['plugin']['stock']['stop'];
$db_esifopen		= $_G['cache']['plugin']['stock']['esifopen'];
$db_esminnum		= $_G['cache']['plugin']['stock']['esminnum'];
$db_esnamemin		= $_G['cache']['plugin']['stock']['esnamemin'];
$db_esnamemax		= $_G['cache']['plugin']['stock']['esnamemax'];
$db_introducemax	= $_G['cache']['plugin']['stock']['introducemax'];
$db_issuedays		= $_G['cache']['plugin']['stock']['issuedays'];
$db_allowregister	= $_G['cache']['plugin']['stock']['allowregister'];
$db_minmoney		= $_G['cache']['plugin']['stock']['minmoney'];
$db_mincredit		= $_G['cache']['plugin']['stock']['mincredit'];
$db_minpost			= $_G['cache']['plugin']['stock']['minpost'];
$db_initialmoney	= $_G['cache']['plugin']['stock']['initialmoney'];
$db_allowdeposit	= $_G['cache']['plugin']['stock']['allowdeposit'];
$db_allowadopt		= $_G['cache']['plugin']['stock']['allowadopt'];
$db_allowtransfer	= $_G['cache']['plugin']['stock']['allowtransfer'];
$db_depositmin		= $_G['cache']['plugin']['stock']['depositmin'];
$db_adoptmin		= $_G['cache']['plugin']['stock']['adoptmin'];
$db_transfermin		= $_G['cache']['plugin']['stock']['transfermin'];
$db_charge			= $_G['cache']['plugin']['stock']['charge'];
$db_transfercharge	= $_G['cache']['plugin']['stock']['transfercharge'];
$db_credittype		= $_G['cache']['plugin']['stock']['credittype'];
$db_proportion		= $_G['cache']['plugin']['stock']['proportion'];
$db_meloncutting	= $_G['cache']['plugin']['stock']['meloncutting'];
require_once 'require/kfsclass.php';
$kfsclass = new kfsclass;
$kfsclass->auto_run();
$hkimg = 'source/plugin/stock/image/';
$mod = empty($_G['gp_mod']) ? 'index' : $_G['gp_mod'];
$modArray = array('index', 'stock', 'member', 'notice', 'news', 'ajax', 'system');
!in_array($mod, $modArray) && showmessage('Messages from Kilofox StockIns: 非法操作');
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
		include template('stock:index');
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
