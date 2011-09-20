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
class kfsclass
{
	public $version	= '1.0.0';
	public $build_date = '2011-09-20';
	public $website = '<a href="http://www.kilofox.net" target="_blank">www.Kilofox.Net</a>';
	public function auto_run()
	{
		global $_G;
		if ( $_G['adminid'] <> '1' )
			$this->checkMarketState();
		$td			= DB::fetch_first("SELECT todaydate, ranktime FROM ".DB::table('kfss_sminfo'));
		$lastDay	= dgmdate($td['todaydate'], 'd');
		$currDay	= dgmdate($_G['timestamp'], 'd');
		if ( $lastDay <> $currDay && dgmdate($_G['timestamp'], 'H') >= 15 )
		{
			self::kfssReset();
		}
		if ( dgmdate($td['ranktime'], 'H') + 1 <> dgmdate($_G['timestamp'], 'H') )
		{
			self::updateRank();
		}
		require_once 'class_ras.php';
		new Ras($_G['gp_section']);
	}
	private function checkMarketState()
	{
		global $_G, $db_smifopen, $db_whysmclose;
		if ( $db_smifopen == '1' )
		{
			showmessage($db_whysmclose);
		}
	}
	public static function kfssReset()
	{
		global $_G;
		loadcache('plugin');
		$db_trustlog	= $_G['cache']['plugin']['simstock']['trustlog'];
		$db_tradecharge	= $_G['cache']['plugin']['simstock']['tradecharge'];
		$db_stampduty	= $_G['cache']['plugin']['simstock']['stampduty'];
		DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+fund_war, fund_war=0");
		DB::query("UPDATE ".DB::table('kfss_deal')." SET ok='4', hide='1' WHERE hide='0'");
		$trustLogNum = is_numeric($db_trustlog) && $db_trustlog > 0 ? $db_trustlog*86400 : 2592000;
		DB::query("DELETE FROM ".DB::table('kfss_deal')." WHERE time_deal < $trustLogNum");
		DB::query("DELETE FROM ".DB::table('kfss_transaction')." WHERE ttime < $trustLogNum");
		DB::query("DELETE FROM ".DB::table('kfss_customer')." WHERE stocknum_ava=0 AND stocknum_war=0");
		DB::query("UPDATE ".DB::table('kfss_sminfo')." SET todaydate='$_G[timestamp]'");
	}
	public static function updateRank()
	{
		global $_G;
		$qu = DB::query("SELECT uid, fund_ini, fund_ava, fund_war FROM ".DB::table('kfss_user')." ORDER BY profit DESC LIMIT 0,200");
		$i = 1;
		while ( $rs = DB::fetch($qu) )
		{
			$stockFund = $stockFundD5 = 0;
			$qc = DB::query("SELECT * FROM ".DB::table('kfss_customer')." WHERE uid='{$rs['uid']}'");
			while ( $rsc = DB::fetch($qc) )
			{
				$stockFund += $rsc['stocknum_ava'] * $rsc['buyprice'];
				if ( $_G['timestamp'] - $rsc['buytime'] < 432000 )
				{
					$stockFundD5 += $rsc['stocknum_ava'] * $rsc['buyprice'];
				}
			}
			$total			= $stockFund + $rs['fund_ava'] + $rs['fund_war'];
			$totalD5		= $stockFundD5 + $rs['fund_ava'] + $rs['fund_war'];
			$profitRatio	= ( $total - $rs['fund_ini'] ) / $rs['fund_ini'] * 100;
			if ( $rs['fund_last'] == 0 )
				$profitD1Ratio	= $profitRatio;
			else
				$profitD1Ratio	= ( $total - $rs['fund_last'] ) / $rs['fund_last'] * 100;
			$profitD5Ratio	= ( $totalD5 - $rs['fund_ini'] ) / $rs['fund_ini'] * 100;
			DB::query("UPDATE ".DB::table('kfss_user')." SET rank='$i', profit='$profitRatio', profit_d1='$profitD1Ratio', profit_d5='$profitD5Ratio' WHERE uid='{$rs['uid']}'");
			$i++;
		}
		DB::query("UPDATE ".DB::table('kfss_sminfo')." SET ranktime='$_G[timestamp]'");
	}
}
function foxpage( $page, $numofpage, $url )
{
	$total = $numofpage;
	if ( $numofpage <= 1 || !is_numeric($page) )
	{
		return;
	}
	else
	{
		$pages = "<div class=\"pg\">";
		$flag = 0;
		for ( $i=$page-3; $i<=$page-1; $i++ )
		{
			if ( $i<1 ) continue;
			$pages.="<a href=\"{$url}page=$i\">$i</a>";
		}
		$pages.="<strong>$page</strong>";
		if ( $page < $numofpage )
		{
			for ( $i=$page+1; $i<=$numofpage; $i++ )
			{
				$pages.="<a href=\"{$url}page=$i\">$i</a>";
				$flag++;
				if ( $flag==4 ) break;
			}
		}
		$pages.="<a href=\"{$url}page=$numofpage\" class=\"nxt\">... {$total}</a></div><span class=\"pgb y\"><a href=\"{$url}page=1\">1 ...</a></span>";
		return $pages;
	}
}
?>
