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
class Tools
{
	public function toollist()
	{
		$stockdb = array();
		$query = DB::query("SELECT sid,stockname FROM ".DB::table('kfsm_stock'));
		while ( $rs = DB::fetch($query) )
		{
			$stockdb[] = $rs;
		}
		$stampduty = DB::query("SELECT stampduty FROM ".DB::table('kfsm_sminfo'));
		$stampduty = number_format($stampduty,2);
		return array($stockdb,$stampduty);
	}
	public function kfsmreset()
	{
		global $baseScript;
		$kfsclass = new kfsclass;
		$kfsclass->kfsm_reset();
		$baseScript .= '&mod=tools';
		cpmsg('股市重新启动成功', $baseScript, 'succeed');
	}
	public function divide($num=1)
	{
		global $baseScript;
		if ( in_array( $num, array(10,100,1000,10000,100000,1000000) ) )
		{
			DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava/{$num} WHERE fund_ava>{$num}");
			DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('资金转换', '{$_G[username]}', '所有股民资金按 {$num}：1 整除', '$_G[timestamp]', '$_G[clientip]')");
			$baseScript .= '&mod=tools';
			cpmsg('资金转换成功', $baseScript, 'succeed');
		}
		else
		{
			cpmsg('资金转换失败！请选择正确的比例！', '', 'error');
		}
	}
	public function forcesell($stock_id=0,$confirm='')
	{
		global $baseScript;
		if ( $confirm == '1' )
		{
			$baseScript .= '&mod=tools';
			if ( $stock_id == '-1' )
			{
				$query = DB::query("SELECT * FROM ".DB::table('kfsm_customer'));
				while ( $rs = DB::fetch($query) )
				{
					$stockvalue = $rs['stocknum_ava'] * $rs['averageprice'];
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$stockvalue}, asset=fund_ava, stocksort='0', stockcost='0.00', stockvalue='0.00', todaysell=todaysell+{$rs['stocknum']} WHERE uid='{$rs['cid']}'");
				}
				DB::query("DELETE FROM ".DB::table('kfsm_customer'));
				DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='0', holder_name='-'");
				DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('强制卖股', '{$_G[username]}', '强制卖出所有股民全部股票', '$_G[timestamp]', '$_G[clientip]')");
				cpmsg('所有股民的全部股票已被强制卖出', $baseScript, 'succeed');
			}
			else
			{
				$query = DB::query("SELECT * FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id'");
				while ( $rs = DB::fetch($query) )
				{
					$stockvalue = $rs['stocknum_ava'] * $rs['averageprice'];
					DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$stockvalue}, todaysell=todaysell+{$rs['stocknum_ava']} WHERE uid='{$rs['cid']}'");
					DB::query("DELETE FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND cid={$rs['cid']}");
					$kfsclass = new kfsclass;
					$kfsclass->calculatefund($rs['cid'],$stock_id);
				}
				DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id=0, holder_name='-' WHERE sid='$stock_id'");
				DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('强制卖股', '{$_G[username]}', '强制卖出代码为 $stock_id 的股票', '$_G[timestamp]', '$_G[clientip]')");
				cpmsg("代码为 $stock_id 的股票已被强制卖出", $baseScript, 'succeed');
			}
		}
		else
		{
			cpmsg('请选择正确的股票对象', '', 'error');
		}
	}
	public function distribute()
	{
		global $_G, $baseScript;
		$sendto	= $_G['sendto'];
		$perval	= $_G['perval'];
		!$sendto && cpmsg('请选择发放对象会员组', '', 'error');
		if ( !$perval || !is_numeric($perval) )
			cpmsg('请正确填写发放金额', '', 'error');
		is_array($sendto) && $sendto = implode(",",$sendto);
		$usernum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_user')." u LEFT JOIN pw_members m ON u.forumuid=m.uid WHERE m.groupid IN('" . str_replace(",","','",$sendto) . "')");
		if ( $usernum )
		{
			$totalfunds = DB::result_first("SELECT stampduty FROM ".DB::table('kfsm_sminfo'));
			$totalval = $perval * $usernum;
			if ( $totalval > $totalfunds )
			{
				cpmsg("税收资金不足，无法发放。现有税收资金 {$totalfunds} 元，发放对象总数为 {$usernum} 人。", '', 'error');
			}
			else
			{
				$touids = array();
				$query = DB::query("SELECT u.uid FROM ".DB::table('kfsm_user')." u LEFT JOIN pw_members m ON u.forumuid=m.uid WHERE m.groupid IN('" . str_replace(",","','",$sendto) . "')");
				while ( $rs = DB::fetch($query) )
				{
					$touids[] = $rs['uid'];
				}
				DB::query("UPDATE ".DB::table('kfsm_user')." SET fund_ava=fund_ava+{$perval} WHERE uid IN(" . pwImplode($touids) . ")");
				DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET stampduty=stampduty-$totalval");
				DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('税收发放', '{$_G[username]}', '共发放税金 $totalval 元，受益股民 $usernum 人，每人收入 $perval 元', '$_G[timestamp]', '$_G[clientip]')");
				$baseScript .= '&mod=tools';
				cpmsg('税收资金发放完成', $baseScript, 'succeed');
			}
		}
		else
		{
			cpmsg('所选会员组没有股民', '', 'error');
		}
	}
}
?>
