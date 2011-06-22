<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-18
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Subscribe
{
	public function __construct( $member, $section, $stock_id )
	{
		$this->process( $member, $section, $stock_id );
	}
	private function process( $user, $section, $sid )
	{
		if ( empty($section) )
			$this->showBuyForm($user, $sid);
		else if ( $section == 'trade' )
			$this->stockTrade($user, $sid);
	}
	private function stockTrade( $user, $sid )
	{
		global $_G, $db_usertrade;
		if ( $db_usertrade == '1' )
		{
			if ( $_G['gp_sidp'] && $_G['gp_tradetype'] == 'b' )
				$this->buyStock($user, $_G['gp_sidp'], $_G['gp_price_buy'], $_G['gp_num_buy']);
			else
				$this->showBuyForm($user, $sid);
		}
		else
			showmessage('暂时停止交易，请您稍后再来');
	}
	private function showBuyForm( $user, $stock_id=0 )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_tradedelay, $db_tradenummin;
		if ( $rs = $this->checkStock($user, $stock_id) )
		{
			$currprice	= $rs['stockprice'] > 0 ? $rs['stockprice'] : 0;
			$buyMin = $db_tradenummin > 0 ? intval($db_tradenummin) : 0;
			$buyMax = intval( $user['capital_ava'] / $currprice );
			$buyMax = $buyMax > $rs['surplusnum'] ? $rs['surplusnum'] : $buyMax;
			$buyMax = $buyMax > intval($rs['stocknum']*0.1) ? intval($rs['stocknum']*0.1) : $buyMax;
			if ( $buyMin > $buyMax )
			{
				$buyMinInit	= $buyMin;
				$buyMin		= $buyMax;
				$buyMax		= $buyMinInit;
			}
			$dutyRate	= 0;
			$dutyMin	= 0;
			if ( $buyMax <= 0 )
				$btn_buy = 'disabled';
			else
				$btn_buy = '';
			include template('stock_dzx:member_subscribe');
		}
		else
		{
			showmessage('该股票状态异常，暂时无法交易');
		}
	}
	private function checkStock($user, $stock_id=0)
	{
		global $_G, $db_tradedelay, $db_iplimit;
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_apply')." WHERE sid='$stock_id'");
		if ( !$rs )
		{
			showmessage('没有找到指定的股票，可能该上市公司已经倒闭');
		}
		else
		{
			$rs['state'] <> 1 && showmessage('该股票状态异常，暂时无法交易');
			$buytime = DB::result_first("SELECT MAX(buytime) FROM ".DB::table('kfsm_customer')." WHERE cid='$user[id]' AND sid='$stock_id'");
			if ( $_G['timestamp'] - $buytime < $db_tradedelay * 60 )
			{
				$timedelay = ceil( $db_tradedelay - ($_G['timestamp']-$buytime)/60 );
				showmessage("股市限制：离允许再次买入该股票还差 $timedelay 分钟！");
			}
			if ( is_numeric($db_iplimit) && $db_iplimit > 0 && $user['ip'] )
			{
				$ipq = "SELECT buytime, ip FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND buytime>{$_G['timestamp']}-$db_iplimit*60";
				$sameIp = false;
				while( $rsip = DB::query($ipq) )
				{
					$rsip['ip'] == $user['ip'] && $sameIp = true;
				}
				if ( $sameIp )
				{
					$timedelay = ceil( $db_iplimit - ($_G['timestamp']-$rsip['buytime'])/60 );
					Showmsg("股市限制：同一IP用户买入同一股票须间隔 $timedelay 分钟！");
				}
			}
		}
		return $rs;
	}
	private function buyStock( $user, $stock_id, $price_buy, $num_buy )
	{
		global $baseScript, $_G, $kfsclass, $db_tradenummin;
		if ( $rs = $this->checkStock($user, $stock_id) )
		{
			if ( !is_numeric($num_buy) || $num_buy < $db_tradenummin )
				showmessage('请正确输入买入数量！');
			else
			{
				$rs = DB::fetch_first("SELECT userid, stocknum, surplusnum FROM ".DB::table('kfsm_apply')." WHERE sid='$stock_id'");
				if ( $num_buy > $rs['surplusnum'] )
				{
					showmessage('对不起，股票数量不足！');
				}
				else
				{
					if ( $db_tradenummin > 0 && $num_buy < $db_tradenummin )
						showmessage("本股市规定：每笔最少交易量为 $db_tradenummin 股！");
					$needMoney	= $price_buy * $num_buy;
					if ( $user['capital_ava'] < $needMoney )
						showmessage('您的帐户中没有足够的资金用来购买股票');
					else
					{
						$worth		= $price_buy * $num_buy;
						$rsc = DB::fetch_first("SELECT stocknum, averageprice, buytime FROM ".DB::table('kfsm_customer')." WHERE cid='$user[id]' AND sid='$stock_id'");
						if ( !$rsc )
						{
							DB::query("INSERT INTO ".DB::table('kfsm_customer')." (cid, username, sid, buyprice, averageprice, stocknum, buytime, ip) VALUES ('$user[id]', '$user[username]', '$stock_id', '$price_buy', '$price_buy', '$num_buy', '$_G[timestamp]', '$user[ip]')");
							DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital-{$worth}, capital_ava=capital_ava-{$worth}, stocksort=stocksort+1, todaybuy=todaybuy+{$num_buy}, lasttradetime='$_G[timestamp]' WHERE uid='{$user[id]}'");
						}
						else
						{
							$numLtd = (int)$rs['stocknum']*0.1;
							if ( $num_buy + $rsc['stocknum'] > $numLtd )
							{
								showmessage("本股市规定：股票申购数量不能大于 $numLtd 股。您已经拥有该股票 {$rsc['stocknum']} 股。");
							}
							$avgprice = round( ( $price_buy * $num_buy + $rsc['averageprice'] * $rsc['stocknum'] ) / ( $num_buy + $rsc['stocknum'] ), 2 );
							DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum+{$num_buy}, buyprice='{$price_buy}', averageprice='{$avgprice}', buytime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$user[id]}' AND sid='$stock_id'");
							DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital-{$worth}, capital_ava=capital_ava-{$worth}, todaybuy=todaybuy+{$num_buy}, lasttradetime='{$_G['timestamp']}' WHERE uid='{$user[id]}'");
						}
						$kfsclass->calculatefund($user['id'], $stock_id);
						DB::query("UPDATE ".DB::table('kfsm_apply')." SET surplusnum=surplusnum-{$num_buy}, capitalisation=capitalisation+{$worth} WHERE sid='$stock_id'");
						DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaybuy=todaybuy+{$num_buy}, todaytotal=todaytotal+{$worth}");
						showmessage('股票申购成功！', "$baseScript&mod=member&act=stocksmng");
					}
				}
			}
		}
		else
		{
			showmessage($baseScript, '交易系统错误');
		}
	}
}
?>
