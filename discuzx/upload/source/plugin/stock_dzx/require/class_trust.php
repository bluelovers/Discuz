<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-26
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Trust
{
	public function __construct( $member, $section )
	{
		$this->process( $member, $section );
	}
	private function process( $user, $section )
	{
		if ( empty($section) )
			$this->showMyDeals( $user );
		else if ( $section == 'tran' )
			$this->showMyTrans( $user );
		else if ( $section == 'trade' )
			$this->stockTrade( $user );
		else if ( $section == 'canceltt' )
		{
			global $_G;
			$this->cancelDeal( $user, $_G['gp_did'] );
		}
	}
	private function stockTrade( $user )
	{
		global $kfsclass, $_G, $db_usertrade;
		if ( $db_usertrade == '1' )
		{
			if ( $_G['gp_sid'] && $_G['gp_tradetype'] == 'b' )
				$this->buyStock($user, $_G['gp_sid'], $_G['gp_price_buy'], $_G['gp_num_buy']);
			else if ( $_G['gp_sid'] && $_G['gp_tradetype'] == 's' )
				$this->sellStock($user, $_G['gp_sid'], $_G['gp_price_sell'], $_G['gp_num_sell']);
			else
				$this->showTradeForm($user, $_G['gp_sid']);
		}
		else
			showmessage('暂时停止交易，请您稍后再来');
	}
	private function showTradeForm( $user, $stock_id=0 )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_wavemax, $db_dutyrate, $db_dutymin, $db_tradenummin;
		if ( $rs = $this->checkStock($stock_id) )
		{
			$openprice	= $rs['openprice'] > 0 ? $rs['openprice'] : 0;
			$currprice	= $rs['currprice'] > 0 ? $rs['currprice'] : 0;
			$dutyRate	= $db_dutyrate > 0 ? $db_dutyrate : 0;
			$dutyMin	= $db_dutymin > 0 ? $db_dutymin : 0;
			$buyMin = $sellMin = is_numeric($db_tradenummin) && $db_tradenummin > 0 ? $db_tradenummin : 0;
			$buyMax = intval( $user['capital_ava'] / ( $openprice * ( 1 + $dutyRate / 100 ) ) );
			$buyMax = $buyMax > $rs['issuenum'] ? $rs['issuenum'] : $buyMax;
			if ( $buyMin > $buyMax )
			{
				$buyMinInit	= $buyMin;
				$buyMin		= $buyMax;
				$buyMax		= $buyMinInit;
			}
			$possessnum	= DB::result_first("SELECT stocknum FROM ".DB::table('kfsm_customer')." WHERE cid='{$user[id]}' AND sid='$stock_id'");
			$sellMin = $possessnum < $sellMin ? (int)$possessnum : $sellMin;
			$sellMax = $possessnum > 0 ? $possessnum : 0;
			if ( $sellMin > $sellMax )
			{
				$sellMinInit	= $sellMin;
				$sellMin		= $sellMax;
				$sellMax		= $sellMinInit;
			}
			if ( $_G['timestamp'] - $rs['issuetime'] < 86400 )
			{
				$priceLow	= 1;
				$priceHigh	= 999.99;
			}
			else
			{
				$priceLow	= $openprice * ( 1 - $db_wavemax / 100 );
				$priceHigh	= $openprice * ( 1 + $db_wavemax / 100 );
			}
			$priceLow >= 1000 && $priceLow		= 999.99;
			$priceHigh >= 1000 && $priceHigh	= 999.99;
			$priceLow	= number_format($priceLow,2);
			$priceHigh	= number_format($priceHigh,2);
			$dutyRate	= number_format($dutyRate,2);
			$dutyMin	= number_format($dutyMin,2);
			$currprice	= number_format($currprice,2);
			if ( $buyMax <= 0 )
				$btn_buy = 'disabled';
			else
				$btn_buy = '';
			if ( $sellMax <= 0 )
				$btn_sell = 'disabled';
			else
				$btn_sell = '';
			$dsdb = $this->getDealSell($stock_id, $openprice);
			$dbdb = $this->getDealBuy($stock_id, $openprice);
			include template('stock_dzx:member_trade');
		}
		else
		{
			showmessage('该股票状态异常，暂时无法交易！');
		}
	}
	private function getDealSell($sid, $openprice)
	{
		$i = 1;
		$qds = DB::query("SELECT price_deal, SUM(quant_deal-quant_tran) AS num FROM ".DB::table('kfsm_deal')." WHERE sid='$sid' AND direction='2' AND ( ok='O' OR ok='2' ) AND hide='0' AND quant_deal-quant_tran>0 GROUP BY price_deal ORDER BY price_deal LIMIT 5");
		while ( $rsds = DB::fetch($qds) )
		{
			if ( $i == 1 )
				$rsds['i'] = '卖一';
			else if ( $i == 2 )
				$rsds['i'] = '卖二';
			else if ( $i == 3 )
				$rsds['i'] = '卖三';
			else if ( $i == 4 )
				$rsds['i'] = '卖四';
			else if ( $i == 5 )
				$rsds['i'] = '卖五';
			if ( $rsds['price_deal'] > $openprice )
				$rsds['color'] = 'ff0000';
			else if ( $rsds['price_deal'] < $openprice )
				$rsds['color'] = '008000';
			else
				$rsds['color'] = '000000';
			$i++;
			$dsdb[] = $rsds;
		}
		$dsdbR = array();
		$i = count($dsdb)-1;
		foreach( $dsdb as $v )
		{
			$dsdbR[] = $dsdb[$i];
			$i--;
		}
		return $dsdbR;
	}
	private function getDealBuy($sid, $openprice)
	{
		$i = 1;
		$qdb = DB::query("SELECT price_deal, SUM(quant_deal-quant_tran) AS num FROM ".DB::table('kfsm_deal')." WHERE sid='$sid' AND direction='1' AND ( ok='O' OR ok='2' ) AND hide='0' AND quant_deal-quant_tran>0 GROUP BY price_deal ORDER BY price_deal DESC LIMIT 5");
		while ( $rsdb = DB::fetch($qdb) )
		{
			if ( $i == 1 )
				$rsdb['i'] = '买一';
			else if ( $i == 2 )
				$rsdb['i'] = '买二';
			else if ( $i == 3 )
				$rsdb['i'] = '买三';
			else if ( $i == 4 )
				$rsdb['i'] = '买四';
			else if ( $i == 5 )
				$rsdb['i'] = '买五';
			if ( $rsds['price_deal'] > $openprice )
				$rsds['color'] = 'ff0000';
			else if ( $rsds['price_deal'] < $openprice )
				$rsds['color'] = '008000';
			else
				$rsds['color'] = '000000';
			$i++;
			$dbdb[] = $rsdb;
		}
		return $dbdb;
	}
	private function checkStock( $stock_id=0 )
	{
		$rs = DB::fetch_first("SELECT sid, stockname, openprice, currprice, issueprice, issuenum, issuer_id, issuetime, state FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
		if ( !$rs )
		{
			showmessage('没有找到指定的股票，可能该上市公司已经倒闭');
		}
		else
		{
			$rs['state'] <> 0 && showmessage('该股票异常，无法交易');
		}
		return $rs;
	}
	private function buyStock( $user, $stock_id=0, $buyPrice=0, $buyNum=0 )
	{
		global $baseScript, $_G, $kfsclass, $db_tradenummin, $db_wavemax, $db_dutyrate, $db_tradedelay, $db_dutymin, $db_iplimit;
		if ( $rs = $this->checkStock($stock_id) )
		{
			$buytime = DB::result_first("SELECT MAX(time_deal) FROM ".DB::table('kfsm_deal')." WHERE uid='{$user[id]}' AND sid='$stock_id' AND direction='1'");
			if ( is_numeric($db_tradedelay) && $db_tradedelay > 0 && $_G['timestamp'] - $buytime < $db_tradedelay * 60 )
			{
				$timedelay = ceil( $db_tradedelay - ($_G['timestamp']-$buytime)/60 );
				showmessage("股市限制：离允许再次买入该股票还差 $timedelay 分钟！");
			}
			if ( is_numeric($db_iplimit) && $db_iplimit > 0 && $user['ip'] )
			{
				$ipq = "SELECT buytime, ip FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND buytime>{$_G['timestamp']}-$db_iplimit*60";
				$sameIp = false;
				while( $rsip = DB::fetch($ipq) )
				{
					$rsip['ip'] == $user['ip'] && $sameIp = true;
				}
				if ( $sameIp )
				{
					$timedelay = ceil( $db_iplimit - ($_G['timestamp']-$rsip['buytime'])/60 );
					Showmsg("股市限制：同一IP用户买入同一股票须间隔 $timedelay 分钟！");
				}
			}
			if ( !is_numeric($buyPrice) || $buyPrice < 1 ) showmessage('请正确输入买入价格');
			if ( !is_numeric($buyNum) || $buyNum < 1 ) showmessage('请正确输入买入数量');
			$buyNum = (int)$buyNum;
			if ( is_numeric($db_tradenummin) && $db_tradenummin > 0 && $buyNum < $db_tradenummin )
				showmessage("本股市规定：每笔最少交易量为 $db_tradenummin 股！");
			$needMoney	= $buyPrice * $buyNum;
			$needFees	= $needMoney * $db_dutyrate / 100;
			$needFees	= $needFees >= $db_dutymin ? $needFees : $db_dutymin;
			if ( $user['capital_ava'] < $needMoney + $needFees )
				showmessage('您的帐户中没有足够的资金用来购买股票');
			else
			{
				DB::query("UPDATE ".DB::table('kfsm_user')." SET capital_ava=capital_ava-{$needMoney} WHERE uid='{$user[id]}'");
				$dealData = array(
					'uid'		=> $user['id'],
					'username'	=> $user['username'],
					'sid'		=> $stock_id,
					'direction'	=> '1',
					'quant_deal'=> $buyNum,
					'price_deal'=> $buyPrice,
					'time_deal'	=> $_G['timestamp'],
					'ok'		=> '0'
				);
				$newdid = DB::insert('kfsm_deal', $dealData, true);
				$query = DB::query("SELECT * FROM ".DB::table('kfsm_deal')." WHERE sid='$stock_id' AND direction='2' AND ( ok='0' OR ok='2' ) AND uid<>$user[id] AND hide='0' ORDER BY price_deal");
				while ( $dsrs = DB::fetch($query) )
				{
					$quant = $dsrs['quant_deal'] - $dsrs['quant_tran'];
					if ( ( $dsrs['price_deal'] <= $buyPrice ) && $quant > 0 )
					{
						if ( $quant >= $buyNum )
						{
							$worth		= $dsrs['price_deal'] * $buyNum;
							$stampduty	= $worth * $db_dutyrate / 100;
							$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
							// 买方股票数量增加
							$rsc = DB::fetch_first("SELECT stocknum, averageprice FROM ".DB::table('kfsm_customer')." WHERE cid='{$user[id]}' AND sid='$stock_id'");
							if ( !$rsc )
							{
								$this->changeoptb($stock_id,$user['id'],$user['username'],$buyNum);
								$psData = array(
									'cid'			=> $user['id'],
									'username'		=> $user['username'],
									'sid'			=> $stock_id,
									'buyprice'		=> $dsrs['price_deal'],
									'averageprice'	=> $dsrs['price_deal'],
									'stocknum'		=> $buyNum,
									'buytime'		=> $_G['timestamp'],
									'ip'			=> $user['ip']
								);
								DB::insert('kfsm_customer', $psData);
								DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital-{$worth}-{$stampduty}, capital_ava=capital_ava-{$worth}-{$stampduty}, stocksort=stocksort+1, todaybuy=todaybuy+{$buyNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user[id]}'");
							}
							else
							{
								$leftNum = $rsc['stocknum'];
								$this->changeoptb($stock_id, $user['id'], $user['username'], intval($buyNum+$leftNum));
								$avgprice = round( ( $dsrs['price_deal'] * $buyNum + $rsc['averageprice'] * $leftNum ) / ( $buyNum + $leftNum), 2 );
								DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum+{$buyNum}, buyprice='{$dsrs['price_deal']}', averageprice='$avgprice', buytime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$user[id]}' AND sid='$stock_id'");
								DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital-{$worth}-{$stampduty}, capital_ava=capital_ava-{$worth}-{$stampduty}, todaybuy=todaybuy+{$buyNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user[id]}'");
							}
							// 卖方资金、今日卖出更新
							DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital+{$worth}, capital_ava=capital_ava+{$worth}, todaysell=todaysell+{$buyNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='$dsrs[uid]'");
							// 卖方股票数量减少
							DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum-$buyNum, selltime='{$_G[timestamp]}' WHERE cid='{$dsrs[uid]}' AND sid='$stock_id'");
							$kfsclass->calculatefund($user['id'], $stock_id);
							$kfsclass->calculatefund($dsrs['uid'], $stock_id);
							// 卖方部分成交
							DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_tran+{$buyNum}, price_tran=price_deal, time_tran='{$_G[timestamp]}', ok='2' WHERE did='$dsrs[did]'");
							DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dsrs[uid]}', '{$dsrs[sid]}', '{$rs[stockname]}', 2, '{$buyNum}', '{$dsrs['price_deal']}', '$worth', '{$dsrs['did']}', '{$_G[timestamp]}')");
							// 买方完全成交
							DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_deal, price_tran='{$dsrs['price_deal']}', time_tran='{$_G[timestamp]}', ok='1' WHERE did='$newdid'");
							DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$user[id]}', '{$dsrs[sid]}', '{$rs[stockname]}', 1, '{$buyNum}', '{$dsrs['price_deal']}', '$worth', '{$dsrs['did']}', '{$_G[timestamp]}')");
							// 更新股票“成交价”
							$this->computeNewPrice( $stock_id, $dsrs['price_deal'], $rs['openprice'], $rs['issuetime'] );
							// 更新股票“成交量”
							DB::query("UPDATE ".DB::table('kfsm_stock')." SET todaytradenum=todaytradenum+{$buyNum} WHERE sid='$stock_id'");
							// 更新股市信息
							DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaybuy=todaybuy+{$buyNum}, todaytotal=todaytotal+{$worth}, stampduty=stampduty+{$stampduty}");
							showmessage('股票买入成功！', "$baseScript&mod=member&act=trustsmng");
						}
						else
						{
							$worth		= $dsrs['price_deal'] * $quant;
							$stampduty	= $worth * $db_dutyrate / 100;
							$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
							// 买方股票数量增加
							$rsc = DB::fetch_first("SELECT stocknum, averageprice FROM ".DB::table('kfsm_customer')." WHERE cid='{$user[id]}' AND sid='$stock_id'");
							if ( !$rsc )
							{
								$this->changeoptb($stock_id, $user['id'], $user['username'], $quant);
								$psData = array(
									'cid'			=> $user['id'],
									'username'		=> $user['username'],
									'sid'			=> $stock_id,
									'buyprice'		=> $dsrs['price_deal'],
									'averageprice'	=> $dsrs['price_deal'],
									'stocknum'		=> $quant,
									'buytime'		=> $_G['timestamp'],
									'ip'			=> $user['ip']
								);
								DB::insert('kfsm_customer', $psData);
								DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital-{$worth}-{$stampduty}, capital_ava=capital_ava-{$worth}-{$stampduty}, stocksort=stocksort+1, todaybuy=todaybuy+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user[id]}'");
							}
							else
							{
								$leftNum = $rsc['stocknum'];
								$this->changeoptb($stock_id, $user['id'], $user['username'], intval($quant+$leftNum));
								$avgprice = round( ($dsrs['price_deal'] * $quant + $rsc['averageprice'] * $leftNum ) / ( $quant + $leftNum ), 2 );
								DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum+{$quant}, buyprice='{$dsrs['price_deal']}', averageprice='$avgprice', buytime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$user[id]}' AND sid='$stock_id'");
								DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital-{$worth}-{$stampduty}, capital_ava=capital_ava-{$worth}-{$stampduty}, todaybuy=todaybuy+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user[id]}'");
							}
							// 卖方资金、今日卖出更新
							DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital+{$worth}, capital_ava=capital_ava+{$worth}, todaysell=todaysell+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='$dsrs[uid]'");
							// 卖方股票数量减少
							DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum-$quant, selltime='{$_G[timestamp]}' WHERE cid='{$dsrs[uid]}' AND sid='$stock_id'");
							$kfsclass->calculatefund($user['id'],$stock_id);
							$kfsclass->calculatefund($dsrs['uid'],$stock_id);
							// 卖方完全成交
							DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_deal, price_tran=price_deal, time_tran='{$_G[timestamp]}', ok='1' WHERE did='$dsrs[did]'");
							DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dsrs[uid]}', '{$dsrs[sid]}', '{$rs[stockname]}', 2, '$quant', '{$dsrs['price_deal']}', '$worth', '{$dsrs['did']}', '{$_G[timestamp]}')");
							// 买方部分成交，有可能完全成交
							DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_tran+{$quant}, price_tran='{$dsrs['price_deal']}', time_tran='{$_G[timestamp]}', ok='2' WHERE did='$newdid'");
							$tranState = DB::fetch_first("SELECT quant_deal, quant_tran FROM ".DB::table('kfsm_deal')." WHERE did='$newdid'");
							if ( $tranState['quant_deal'] == $tranState['quant_tran'] )
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='1' WHERE did='$newdid'");
							DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dsrs[uid]}', '{$dsrs[sid]}', '{$rs[stockname]}', 1, '$quant', '{$dsrs['price_deal']}', '$worth', '{$dsrs['did']}', '{$_G[timestamp]}')");
							// 更新股票“成交价”
							$this->computeNewPrice( $stock_id, $dsrs['price_deal'], $rs['openprice'], $rs['issuetime'] );
							// 更新股票“成交量”
							DB::query("UPDATE ".DB::table('kfsm_stock')." SET todaytradenum=todaytradenum+{$quant} WHERE sid='$stock_id'");
							// 更新股市信息
							DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaybuy=todaybuy+{$quant}, todaytotal=todaytotal+{$worth}, stampduty=stampduty+{$stampduty}");
							$buyNum -= $quant;
							continue;
						}
					}
				}
				showmessage('委托买入股票已成功挂单！', "$baseScript&mod=member&act=trustsmng");
			}
		}
	}
	private function sellStock( $user, $stock_id=0, $sellPrice=0, $sellNum=0 )
	{
		global $baseScript, $_G, $kfsclass, $db_wavemax, $db_tradenummin, $db_dutyrate, $db_dutymin, $db_tradedelay, $db_iplimit;
		if ( $rs = $this->checkStock($stock_id) )
		{
			$rss = DB::fetch_first("SELECT stocknum FROM ".DB::table('kfsm_customer')." WHERE cid='{$user[id]}' AND sid='$stock_id'");
			if ( !$rss )
				showmessage('您没有这只股票，无法进行卖出操作');
			else
			{
				$selltime = DB::result_first("SELECT MAX(time_deal) FROM ".DB::table('kfsm_deal')." WHERE uid='{$user[id]}' AND sid='$stock_id' AND direction='2'");
				if ( is_numeric($db_tradedelay) && $db_tradedelay > 0 && $_G['timestamp'] - $selltime < $db_tradedelay * 60 )
				{
					$timedelay = ceil( $db_tradedelay - ($_G['timestamp']-$selltime)/60 );
					showmessage("股市限制：离允许再次卖出该股票还差 $timedelay 分钟！");
				}
				if ( is_numeric($db_iplimit) && $db_iplimit > 0 && $user['ip'] )
				{
					$ipq = "SELECT selltime, ip FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND selltime>{$_G['timestamp']}-$db_iplimit*60";
					$sameIp = false;
					while( $rsip = DB::fetch($ipq) )
					{
						$rsip['ip'] == $user['ip'] && $sameIp = true;
					}
					if ( $sameIp )
					{
						$timedelay = ceil( $db_iplimit - ($_G['timestamp']-$rsip['selltime'])/60 );
						Showmsg("股市限制：同一IP用户卖出同一股票须间隔 $timedelay 分钟！");
					}
				}
				if ( !is_numeric($sellPrice) || $sellPrice < 1 ) showmessage('请正确输入卖出价格');
				if ( !is_numeric($sellNum) || $sellNum < 1 ) showmessage('请正确输入卖出数量');
				$sellNum = (int)$sellNum;
				$leftNum = $rss['stocknum'] - $sellNum;
				if ( $leftNum < 0 )
				{
					showmessage('您没有足够的股票卖出');
				}
				else
				{
					if ( $user['id'] == $rs['issuer_id'] && ( $leftNum < $rs['issuenum']/2 ) && ( $_G['timestamp'] - $rs['issuetime'] - 2592000 <= 0 ) )
					{
						showmessage('您是该公司股票发行人，一个月之内不能抛售股票');
					}
					if ( $rss['stocknum'] < 10 )
					{
						if ( $leftNum > 0 )
							showmessage('您的股票数量不足 10 股，必须全部卖出');
					}
					else
					{
						if ( is_numeric($db_tradenummin) && $db_tradenummin > 0 && $sellNum < $db_tradenummin )
							showmessage("本股市规定：每笔最少交易量为 $db_tradenummin 股！");
					}
					$dealData = array(
						'uid'		=> $user['id'],
						'username'	=> $user['username'],
						'sid'		=> $stock_id,
						'direction'	=> '2',
						'quant_deal'=> $sellNum,
						'price_deal'=> $sellPrice,
						'time_deal'	=> $_G['timestamp'],
						'ok'		=> '0'
					);
					$newdid = DB::insert('kfsm_deal', $dealData, true);
					$query = DB::query("SELECT * FROM ".DB::table('kfsm_deal')." WHERE sid='$stock_id' AND direction='1' AND ( ok='0' OR ok='2' ) AND uid<>'$user[id]' AND hide='0' ORDER BY price_deal DESC");
					while ( $dbrs = DB::fetch($query) )
					{
						$quant = $dbrs['quant_deal'] - $dbrs['quant_tran'];
						if ( ( $dbrs['price_deal'] >= $sellPrice ) && $quant > 0 )
						{
							if ( $quant >= $sellNum )
							{
								$worth		= $dbrs['price_deal'] * $sellNum;
								$stampduty	= $worth * $db_dutyrate / 100;
								$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
								// 卖方资金、今日卖出更新
								DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital+{$worth}-{$stampduty}, capital_ava=capital_ava+{$worth}-{$stampduty}, todaysell=todaysell+{$sellNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user[id]}'");
								// 卖方股票数量减少
								DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum-$sellNum, selltime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$user[id]}' AND sid='$stock_id'");
								// 买方股票数量增加
								$rsc = DB::fetch_first("SELECT stocknum, averageprice FROM ".DB::table('kfsm_customer')." WHERE cid='{$dbrs[uid]}' AND sid='$stock_id'");
								if ( !$rsc )
								{
									$this->changeoptb($stock_id, $user['id'], $user['username'], $sellNum);
									$psData = array(
										'cid'			=> $dbrs['uid'],
										'username'		=> $dbrs['username'],
										'sid'			=> $stock_id,
										'buyprice'		=> $dbrs['price_deal'],
										'averageprice'	=> $dbrs['price_deal'],
										'stocknum'		=> $sellNum,
										'buytime'		=> $_G['timestamp']
									);
									DB::insert('kfsm_customer', $psData);
								}
								else
								{
									$haveNum = $rsc['stocknum'];
									$this->changeoptb($stock_id, $user['id'], $user['username'], intval($sellNum+$haveNum));
									$avgprice = round( ( $dbrs['price_deal'] * $sellNum + $rsc['averageprice'] * $haveNum ) / ( $sellNum + $haveNum ), 2 );
									DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum+{$sellNum}, buyprice='{$dbrs['price_deal']}', averageprice='$avgprice', buytime='{$_G[timestamp]}' WHERE cid='$dbrs[uid]' AND sid='$stock_id'");
								}
								// 买方今日买入更新
								DB::query("UPDATE ".DB::table('kfsm_user')." SET todaybuy=todaybuy+{$sellNum}, lasttradetime='{$_G[timestamp]}' WHERE uid='$dbrs[uid]'");
								$kfsclass->calculatefund($user['id'], $stock_id);
								$kfsclass->calculatefund($dbrs['uid'], $stock_id);
								// 买方部分成交
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_tran+{$sellNum}, price_tran=price_deal, time_tran='{$_G[timestamp]}', ok='2' WHERE did='$dbrs[did]'");
								DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dbrs[uid]}', '{$dbrs[sid]}', '{$rs[stockname]}', '1', '$sellNum', '{$dbrs['price_deal']}', '$worth', '{$dbrs['did']}', '{$_G[timestamp]}')");
								// 卖方完全成交
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_deal, price_tran='{$dbrs['price_deal']}', time_tran='{$_G[timestamp]}', ok='1' WHERE did='$newdid'");
								DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$user[id]}', '{$dbrs[sid]}', '{$rs[stockname]}', '2', '$sellNum', '{$dbrs['price_deal']}', '$worth', '{$dbrs['did']}', '{$_G[timestamp]}')");
								// 更新股票“成交价”
								$this->computeNewPrice( $stock_id,  $dbrs['price_deal'], $rs['openprice'], $rs['issuetime'] );
								// 更新股票“成交量”
								DB::query("UPDATE ".DB::table('kfsm_stock')." SET todaytradenum=todaytradenum+{$sellNum} WHERE sid='$stock_id'");
								// 更新股市信息
								DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaysell=todaysell+{$sellNum}, todaytotal=todaytotal+{$worth}, stampduty=stampduty+{$stampduty}");
								showmessage('股票卖出成功！', "$baseScript&mod=member&act=trustsmng");
							}
							else
							{
								$worth		=  $dbrs['price_deal'] * $quant;
								$stampduty	= $worth * $db_dutyrate / 100;
								$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
								// 卖方资金、今日卖出更新
								DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital+{$worth}-{$stampduty}, capital_ava=capital_ava+{$worth}-{$stampduty}, todaysell=todaysell+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='{$user[id]}'");
								// 卖方股票数量减少
								DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum-$quant, selltime='{$_G[timestamp]}', ip='{$user[ip]}' WHERE cid='{$user[id]}' AND sid='$stock_id'");
								// 买方股票数量增加
								$rsc = DB::fetch_first("SELECT stocknum, averageprice FROM ".DB::table('kfsm_customer')." WHERE cid='$dbrs[uid]' AND sid='$stock_id'");
								if ( !$rsc )
								{
									$this->changeoptb($stock_id, $user['id'], $user['username'], $quant);
									$bsData = array(
										'cid'			=> $dbrs['uid'],
										'username'		=> $dbrs['username'],
										'sid'			=> $stock_id,
										'buyprice'		=> $dbrs['price_deal'],
										'averageprice'	=> $dbrs['price_deal'],
										'stocknum'		=> $quant,
										'buytime'		=> $_G['timestamp']
									);
									DB::insert('kfsm_customer', $bsData);
								}
								else
								{
									$haveNum = $rsc['stocknum'];
									$this->changeoptb($stock_id, $user['id'], $user['username'], intval($quant+$haveNum));
									$avgprice = round( ( $dbrs['price_deal'] * $quant + $rsc['averageprice'] * $haveNum ) / ( $quant + $haveNum ), 2 );
									DB::query("UPDATE ".DB::table('kfsm_customer')." SET stocknum=stocknum+{$quant}, buyprice='{$dbrs['price_deal']}', averageprice='$avgprice', buytime='{$_G[timestamp]}' WHERE cid='$dbrs[uid]' AND sid='$stock_id'");
								}
								// 买方今日买入更新
								DB::query("UPDATE ".DB::table('kfsm_user')." SET todaybuy=todaybuy+{$quant}, lasttradetime='{$_G[timestamp]}' WHERE uid='$dbrs[uid]'");
								$kfsclass->calculatefund($user['id'], $stock_id);
								$kfsclass->calculatefund($dbrs['uid'], $stock_id);
								// 买方完全成交
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_deal, price_tran=price_deal, time_tran='{$_G[timestamp]}', ok='1' WHERE did='$dbrs[did]'");
								DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$dbrs[uid]}', '{$dbrs[sid]}', '{$rs[stockname]}', '1', '{$quant}', '{$dbrs['price_deal']}', '$worth', '{$dbrs['did']}', '{$_G[timestamp]}')");
								// 卖方部分成交，有可能完全成交
								DB::query("UPDATE ".DB::table('kfsm_deal')." SET quant_tran=quant_tran+{$quant}, price_tran='{$dbrs['price_deal']}', time_tran='{$_G[timestamp]}', ok='2' WHERE did='$newdid'");
								$tranState = DB::fetch_first("SELECT quant_deal, quant_tran FROM ".DB::table('kfsm_deal')." WHERE did='$newdid'");
								if ( $tranState['quant_deal'] == $tranState['quant_tran'] )
									DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='1' WHERE did='$newdid'");
								DB::query("INSERT INTO ".DB::table('kfsm_transaction')."(uid, sid, stockname, direction, quant, price, amount, did, ttime) VALUES('{$user[id]}', '{$dbrs[sid]}', '{$rs[stockname]}', '2', '{$quant}', '{$dbrs['price_deal']}', '$worth', '{$dbrs['did']}', '{$_G[timestamp]}')");
								// 更新股票“成交价”
								$this->computeNewPrice( $stock_id, $dbrs['price_deal'], $rs['openprice'], $rs['issuetime'] );
								// 更新股票“成交量”
								DB::query("UPDATE ".DB::table('kfsm_stock')." SET todaytradenum=todaytradenum+{$quant} WHERE sid='$stock_id'");
								// 更新股市信息
								DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET todaysell=todaysell+{$quant}, todaytotal=todaytotal+{$worth}, stampduty=stampduty+{$stampduty}");
								$sellNum -= $quant;
								continue;
							}
						}
					}
					showmessage('委托卖出股票已成功挂单！', "$baseScript&mod=member&act=trustsmng");
				}
			}
		}
	}
	private function computeNewPrice( $stock_id, $tradePrice, $openPrice, $issueTime )
	{
		global $db_wavemax;
		$waved_a = round( ( $tradePrice - $openPrice ) / $openPrice * 100, 2 );
		$db_wavemax = is_numeric($db_wavemax) && $db_wavemax > 0 && $db_wavemax < 100 ? $db_wavemax : 10;
		if ( $waved_a > $db_wavemax )
		{
			$priceMax	= round( $openPrice * ( 1 + $db_wavemax / 100 ), 2 );
			$price		= $priceMax;
			$waved_a	= $db_wavemax;
		}
		else if ( $waved_a < -$db_wavemax )
		{
			$priceMin	= round( $openPrice * ( 1 - $db_wavemax / 100 ), 2 );
			$price		= $priceMin;
			$waved_a	= -$db_wavemax;
		}
		else
		{
			$price		= $tradePrice;
		}
		$waved_t = round( ( $tradePrice - $issuePrice ) / $issuePrice * 100, 2 );
		DB::query("UPDATE ".DB::table('kfsm_stock')." SET currprice={$price}, todaywave={$waved_a}, totalwave={$waved_t} WHERE sid='$stock_id'");
		DB::query("UPDATE ".DB::table('kfsm_sminfo')." SET ain_t=ain_t+{$waved_a}");
		$this->updateTSP($stock_id,$price);
	}
	private function changeoptb( $stock_id, $userid, $username, $totalnum )
	{
		$changeoptb = false;
		$rsm = DB::fetch_first("SELECT holder_id FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
		if ( $rsm['holder_id'] == 0 )
		{
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='$userid', holder_name='$username' WHERE sid='$stock_id'");
			$changeoptb = true;
		}
		else if ( $rsm['holder_id'] <> $userid )
		{
			$rs = DB::fetch_first("SELECT stocknum, cid FROM ".DB::table('kfsm_customer')." WHERE sid='$stock_id' AND cid<>'$userid' ORDER BY stocknum DESC");
			if ( $rs )
			{
				if ( $totalnum > $rs['stocknum'] )
				{
					DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='$userid', holder_name='$username' WHERE sid='$stock_id'");
					$changeoptb = true;
				}
			}
		}
		return $changeoptb;
	}
	private function changeopts( $stock_id, $userid, $username, $remnum )
	{
		$changeopts = '';
		$rsm = DB::fetch_first("SELECT holder_id FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
		$rs = DB::fetch_first("SELECT c.stocknum, c.username, c.cid FROM ".DB::table('kfsm_customer')." c INNER JOIN ".DB::table('kfsm_user')." u ON c.cid=u.uid WHERE c.sid='$stock_id' AND c.cid<>'$userid' ORDER BY c.stocknum DESC");
		if ( $rs )
		{
			if ( $remnum > $rs['stocknum'] )
			{
				if ( $rsm['holder_id'] == $userid )
				{
					$changeopts = '';
				}
				else
				{
					DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='$userid', holder_name='$username' WHERE sid='$stock_id'"); 
					$changeopts = $username;
				}
			}
			else if ( $remnum < $rs['stocknum'] )
			{
				if ( $rsm['holder_id'] == $rs['cid'] )
				{
					$changeopts = '';
				}
				else
				{
					DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_id='$rs[cid]', holder_name='$rs[username]' WHERE sid='$stock_id'");
					$changeopts = $rs['username'];
				}
			}
		}
		else if ( $remnum <= 0 )
		{
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET holder_name='-', holder_id='0' WHERE sid='$stock_id'");
		}
		return $changeopts;
	}
	private function updateTSP( $stock_id, $price )
	{
		global $_G, $db_klcolor;
		if ( $stock_id && $price )
		{
			$klcolor = $db_klcolor;
			$pricedata = DB::query("SELECT pricedata FROM ".DB::table('kfsm_stock')." WHERE sid='$stock_id'");
			$pricedata = substr($pricedata,strpos($pricedata,'|')+1).'|'.round($price,2);
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET uptime='{$_G[timestamp]}', pricedata='$pricedata' WHERE sid='$stock_id'");
		}
	}
	private function showMyDeals( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$qd = DB::query("SELECT d.*, s.stockname FROM ".DB::table('kfsm_deal')." d LEFT JOIN ".DB::table('kfsm_stock')." s ON d.sid=s.sid WHERE d.uid='{$user[id]}' ORDER BY d.did DESC");
		while ( $rsd = DB::fetch($qd) )
		{
			if ( $rsd['direction'] == 1 )
				$rsd['direction'] = '<span style="color:#FF0000">买入</span>';
			else if ( $rsd['direction'] == 2 )
				$rsd['direction'] = '<span style="color:#008000">卖出</span>';
			else
				$rsd['direction'] = '<span style="color:#0000FF">异常</span>';
			if ( $rsd['time_deal'] )
				$rsd['time_deal']	= dgmdate($rsd['time_deal'],'Y-m-d H:i:s');
			else
				$rsd['time_deal']	= '-';
			if ( $rsd['ok'] == 0 )
			{
				$rsd['ok'] = '未成交';
				$rsd['op'] = "<form name=\"form1\" action=\"$baseScript&mod=member&act=trustsmng\" method=\"post\"><input type=\"hidden\" name=\"section\" value=\"canceltt\" /><input type=\"hidden\" name=\"did\" value=\"$rsd[did]\" /><button type=\"submit\" name=\"submit\" value=\"true\" class=\"pn pnc\"><em>撤单</em></button></form>";
			}
			else if ( $rsd['ok'] == 1 )
			{
				$rsd['ok'] = '<span style="color:#008000">成交</span>';
				$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>撤单</em></button>';
			}
			else if ( $rsd['ok'] == 2 )
			{
				$rsd['ok'] = '<span style="color:#FFA500">部分成交</span>';
				$rsd['op'] = "<form name=\"form1\" action=\"$baseScript&mod=member&act=trustsmng\" method=\"post\"><input type=\"hidden\" name=\"section\" value=\"canceltt\" /><input type=\"hidden\" name=\"did\" value=\"$rsd[did]\" /><button type=\"submit\" name=\"submit\" value=\"true\" class=\"pn pnc\"><em>撤单</em></button></form>";
			}
			else if ( $rsd['ok'] == 3 )
			{
				$rsd['ok'] = '<span style="color:#0000FF">用户撤销</span>';
				$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>撤单</em></button>';
			}
			else if ( $rsd['ok'] == 4 )
			{
				$rsd['ok'] = '<span style="color:#A52A2A">系统撤销</span>';
				$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>撤单</em></button>';
			}
			else
			{
				$rsd['ok'] = '<span style="color:#FF0000">异常</span>';
				$rsd['op'] = '<button type="submit" name="submit" value="true" class="pn pnc" disabled><em>撤单</em></button>';
			}
			$ddb[] = $rsd;
		}
		include template('stock_dzx:member_trustsmng');
	}
	private function showMyTrans( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$qt = DB::query("SELECT t.*, s.stockname FROM ".DB::table('kfsm_transaction')." t LEFT JOIN ".DB::table('kfsm_stock')." s ON t.sid=s.sid WHERE t.uid='{$user[id]}' ORDER BY t.tid DESC");
		while ( $rst = DB::fetch($qt) )
		{
			if ( $rst['direction'] == 1 )
				$rst['direction'] = '<span style="color:#FF0000">买入</span>';
			else if ( $rst['direction'] == 2 )
				$rst['direction'] = '<span style="color:#008000">卖出</span>';
			else
				$rst['direction'] = '<span style="color:#0000FF">异常</span>';
			if ( $rst['ttime'] )
				$rst['ttime']	= dgmdate($rst['ttime'],'Y-m-d H:i:s');
			else
				$rst['ttime']	= '-';
			$tdb[] = $rst;
		}
		include template('stock_dzx:member_trustsmng');
	}
	private function cancelDeal( $user, $deal_id )
	{
		global $baseScript, $db_dutyrate, $db_dutymin, $kfsclass;
		$qd = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_deal')." WHERE did='$deal_id' AND uid='{$user[id]}'");
		if ( $qd )
		{
			$quantLeft = $qd['quant_deal'] - $qd['quant_tran'];
			if ( $quantLeft > 0 && $qd['hide'] == 0 )
			{
				if ( $qd['ok'] == 0 && $qd['quant_deal'] == $quantLeft )
				{
					$worth	= $qd['price_deal'] * $qd['quant_deal'];
				}
				else if ( $qd['ok'] == 2 )
				{
					$worth	= $qd['price_deal'] * $quantLeft;
				}
				else
				{
					showmessage('该委托单交易状态异常，无法撤销！');
				}
				$stampduty	= $worth * $db_dutyrate / 100;
				$stampduty	= $stampduty >= $db_dutymin ? $stampduty : $db_dutymin;
				if ( $qd['direction'] == 1 )
				{
					DB::query("UPDATE ".DB::table('kfsm_user')." SET capital_ava=capital_ava+{$worth}+{$stampduty} WHERE uid='{$user[id]}'");
					DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='3' WHERE did='{$qd[did]}'");
					$kfsclass->calculatefund($user['id']);
					showmessage('委托买入股票撤销成功！', "$baseScript&mod=member&act=trustsmng");
				}
				else if ( $qd['direction'] == 2 )
				{
					DB::query("UPDATE ".DB::table('kfsm_deal')." SET ok='3' WHERE did='{$qd[did]}'");
					$kfsclass->calculatefund($user['id']);
					showmessage('委托卖出股票撤销成功！', "$baseScript&mod=member&act=trustsmng");
				}
				else
				{
					showmessage('该委托单买卖方向异常，无法撤销！');
				}
			}
			else
			{
				showmessage('该委托单已全部成交，或已过期，无法撤销！');
			}
		}
		else
		{
			showmessage('无效的委托单！');
		}
	}
}
?>
