<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-11
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class kfsclass
{
	public $version	= '9.4.2';
	public $build_date = '2011-06-10';
	public $website = '<a href="http://www.kilofox.net" target="_blank">www.Kilofox.Net</a>';
	public function auto_run()
	{
		global $_G;
		if ( $_G['adminid'] <> '1' )
			$this->checkMarketState();
		$td			= DB::fetch_first("SELECT todaydate FROM kfsm_sminfo");
		$lastDay	= dgmdate($td['todaydate'], 'd');
		$currDay	= dgmdate($_G['timestamp'], 'd');
		$lastDay <> $currDay && $this->kfsm_reset();
	}
	private function checkMarketState()
	{
		global $_G, $db_smifopen, $db_whysmclose, $db_guestview, $db_smiftime, $db_smtimer11, $db_smtimer11, $db_smtimer12, $db_smtimer21, $db_smtimer22, $db_smtimer31, $db_smtimer32, $db_smtimer41, $db_smtimer42;
		if ( $db_smifopen == '0' )
		{
			if ( $db_guestview<>'1' && !$_G['uid'] )
			{
				showmessage('对不起，股市未对游客开放，请您先登录论坛！');
			}
			else
			{
				if ( $db_smiftime )
				{
					$t_h = dgmdate($_G['timestamp'],'G');
					if ( ( $t_h < $db_smtimer11 || $t_h >= $db_smtimer12 ) && ( $t_h < $db_smtimer21 || $t_h >= $db_smtimer22 ) && ( $t_h< $db_smtimer31 || $t_h >= $db_smtimer32 ) && ( $t_h < $db_smtimer41 || $t_h >= $db_smtimer42 ) )
						showmessage("亲爱的股民，您好！本股市的交易时间为：<br />{$db_smtimer11}:00 - {$db_smtimer12}:00<br />{$db_smtimer21}:00 - {$db_smtimer22}:00<br />{$db_smtimer31}:00 - {$db_smtimer32}:00<br />{$db_smtimer41}:00 - {$db_smtimer42}:00<br />请于上述时段光临！谢谢合作！");
					else
						return true;
				}
				else
				{
					return true;
				}
			}
		}
		else
		{
			showmessage($db_whysmclose);
		}
	}
	public function calculatefund($user_id,$stock_id)
	{
		if ( is_numeric($user_id) )
		{
			if ( $user_id > 0 )
				$query = DB::query("SELECT uid FROM kfsm_user WHERE uid='$user_id' ORDER BY uid");
			else
				$query = DB::query("SELECT uid FROM kfsm_user ORDER BY uid");
			while ( $rsuser = DB::fetch($query) )
			{
				$mystnum	= 0;
				$mystcost	= 0;
				$mystvalue	= 0;
				$totalfund	= 0;
				$stockkinds	= 0;
				$query = DB::query("SELECT c.*,s.currprice FROM kfsm_customer c INNER JOIN kfsm_stock s ON c.sid=s.sid WHERE c.cid='$rsuser[uid]'");
				while ( $rsst = DB::fetch($query) )
				{
					$mystnum	= $mystnum + $rsst['stocknum'];
					$mystcost	= $mystcost + $rsst['stocknum'] * $rsst['averageprice'];
					$mystvalue	= $mystvalue + $rsst['stocknum'] * $rsst['currprice'];
					$totalfund	= $totalfund + $rsst['stocknum'] * $rsst['currprice'];
					$stockkinds++;
				}
				DB::query("UPDATE kfsm_user SET asset=capital+{$totalfund}, stocksort='{$stockkinds}', stocknum='{$mystnum}', stockcost='{$mystcost}', stockvalue='{$mystvalue}' WHERE uid='$rsuser[uid]'");
			}
		}
		if ( $stock_id )
		{
			$rsst = DB::fetch_first("SELECT currprice, lowprice, highprice FROM kfsm_stock WHERE sid='$stock_id'");
			if ( $rsst )
			{
				if ( $rsst['currprice'] > $rsst['highprice'] )
					$newstockpricea = 'highprice=currprice';
				else
					$newstockpricea = 'highprice=highprice';
				if ( $rsst['currprice'] < $rsst['lowprice'] )
					$newstockpriceb = 'lowprice=currprice';
				else
					$newstockpriceb = 'lowprice=lowprice';
				DB::query("UPDATE kfsm_stock SET $newstockpricea, $newstockpriceb WHERE sid='$stock_id'");
			}
		}
	}
	public function kfsm_reset()
	{
		global $_G;
		loadcache('plugin');
		$db_trustlog	= $_G['cache']['plugin']['stock_dzx']['trustlog'];
		$db_tradecharge	= $_G['cache']['plugin']['stock_dzx']['tradecharge'];
		$db_stampduty	= $_G['cache']['plugin']['stock_dzx']['stampduty'];
		DB::query("UPDATE kfsm_user SET todaybuy='0', todaysell='0'");
		$query = DB::query("SELECT * FROM kfsm_deal WHERE ok='0' OR ok='2'");
		while ( $tbrs = DB::fetch($query) )
		{
			if ( $tbrs['direction'] == 1 )
			{
				if ( $tbrs['ok'] == 0 )
				{
					DB::query("UPDATE kfsm_user SET capital_ava=capital WHERE uid='$tbrs[uid]'");
				}
				else if ( $tbrs['ok'] == 2 )
				{
					$refundNum = $tbrs['price_deal'] * $tbrs['quant_deal'];
					DB::query("UPDATE kfsm_user SET capital_ava=capital_ava+{$refundNum} WHERE uid='$tbrs[uid]'");
				}
			}
		}
		DB::query("UPDATE kfsm_deal SET ok='4', hide='1' WHERE hide='0'");
		$trustLogNum = is_numeric($db_trustlog) && $db_trustlog > 0 ? $db_trustlog*86400 : 2592000;
		DB::query("DELETE FROM kfsm_deal WHERE time_deal < $trustLogNum");
		DB::query("DELETE FROM kfsm_transaction WHERE ttime < $trustLogNum");
		DB::query("DELETE FROM kfsm_customer WHERE stocknum='0'");
		DB::query("UPDATE kfsm_sminfo SET todaybuy='0', todaysell='0', todaytotal='0', todaydate='$_G[timestamp]', ain_y=ain_t");
		DB::query("UPDATE kfsm_stock SET state='1' WHERE openprice<=1");
		DB::query("UPDATE kfsm_stock SET state='0' WHERE state='2' OR state='3'");
		DB::query("UPDATE kfsm_stock SET openprice=currprice, todaytradenum='0', todaybuynum='0', todaysellnum='0', todaywave='0' WHERE todaytradenum>0");
		$this->checkNewStock();
	}
	private function checkNewStock()
	{
		global $baseScript, $_G;
		loadcache('plugin');
		$db_issuedays	= $_G['cache']['plugin']['stock_dzx']['issuedays'];
		$query = DB::query("SELECT aid, sid, stockname, userid, stockprice, stocknum, surplusnum, capitalisation, issuetime FROM kfsm_apply WHERE state='1'");
		while ( $aprs = DB::fetch($query) )
		{
			if ( $_G['timestamp'] > $aprs['issuetime'] )
			{
				$pricedata = "$aprs[stockprice]";
				$i = 0;
				do
				{
					$pricedata .= "|$aprs[stockprice]";
					$i++;
				}
				while ( $i < 23 );
				$issue_price = round($aprs['capitalisation'] / $aprs['stocknum'], 2);
				DB::query("UPDATE kfsm_stock SET openprice='$issue_price', currprice='$issue_price', lowprice='$issue_price', highprice='$issue_price', issueprice='$issue_price', issuetime='$_G[timestamp]', pricedata='$pricedata', state='0' WHERE sid='{$aprs[sid]}'");
				DB::query("UPDATE kfsm_customer SET stocknum=stocknum+{$aprs[surplusnum]}, buytime='{$_G[timestamp]}' WHERE cid='{$aprs[userid]}' AND sid='{$aprs[sid]}'");
				$subject = "新股 $aprs[stockname] 今日正式上市";
				$content = "今天是 [url=plugin.php?id=stock:index&mod=stock&act=showinfo&sid=$aprs[sid]]{$aprs['stockname']}[/url] 正式上市的第一天，所有股民均可按照股市规则自由交易。";
				DB::query("INSERT INTO kfsm_news (subject, content, color, author, addtime) VALUES('$subject', '$content', '', 'StockIns', '{$_G[timestamp]}')");
				DB::query("UPDATE kfsm_apply SET state='3' WHERE aid='$aprs[aid]'");
				$s = explode('|', $pricedata);
				$stock_id = $aprs['sid'];
				include_once 'chart.php';
				$this->calculatefund($aprs['userid'],0);
				$this->resetcid();
			}
		}
	}
	public function resetcid()
	{
		$query = DB::query("SELECT sid FROM kfsm_stock ORDER BY sid");
		$cid = 1;
		while ( $rs = DB::fetch($query) )
		{
			DB::query("UPDATE kfsm_stock SET cid='$cid' WHERE sid='$rs[sid]'");
			$cid++;
		}
	}
}
function foxpage( $page, $numofpage, $url )
{
	$total = $numofpage;
	if ( $numofpage <= 1 || !is_numeric($page) )
	{
		return ;
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
