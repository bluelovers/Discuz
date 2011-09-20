<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-08-09
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Ajax
{
	public function __construct($section)
	{
		method_exists($this,$section) && $this->$section();
	}
	private function account()
	{
		global $_G;
		$uAccount	= self::getUserAccount($_G['gp_uid']);
		$uStocks	= self::getUserStocks($_G['gp_uid']);
		echo 'var o={account:'.$uAccount.', stockHold:'.$uStocks.', order:'.$uStocks.'};';
	}
	private function buy()
	{
		global $_G;
		$retMsgs = '';
		if ( is_numeric($_G['gp_uid']) && $_G['gp_uid'] > 0 )
		{
			$user = self::getUserInfo($_G['gp_uid']);
			$needMoney = $_G['gp_price'] * $_G['gp_amount'];
			if ( $user['fund_ava'] < $needMoney )
			{
				$retMsgs = '用户可用资金不足';
			}
			else
			{
				DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava-{$needMoney}, fund_war=fund_war+{$needMoney} WHERE uid='{$user[uid]}'");
				$dealData = array(
					'uid'		=> $user['uid'],
					'username'	=> $user['username'],
					'code'		=> $_G['gp_code'],
					'stockname'	=> mb_convert_encoding($_G['gp_stockname'],'gbk','utf-8'),
					'direction'	=> '1',
					'quant_deal'=> $_G['gp_amount'],
					'price_deal'=> $_G['gp_price'],
					'time_deal'	=> $_G['timestamp'],
					'ok'		=> '0'
				);
				DB::insert('kfss_deal', $dealData);
				// 异常交易监测
				if ( $_G['gp_amount'] > 10000 )
				{
					$exceptData = array(
						'uid'		=> $user['uid'],
						'uname'		=> $user['username'],
						'action'	=> '1',	// 委托买入
						'stockcode'	=> $_G['gp_code'],
						'amount'	=> $_G['gp_amount'],
						'price'		=> $_G['gp_price'],
						'logtime'	=> $_G['timestamp'],
						'ip'		=> $_G['clientip']
					);
					DB::insert('kfss_exclog', $exceptData);
				}
				$codes = DB::result_first("SELECT stockcode FROM ".DB::table('kfss_sminfo')." WHERE id=1");
				$codes = $codes ? $codes . '|' . $_G['gp_code'] : $_G['gp_code'];
				DB::query("UPDATE ".DB::table('kfss_sminfo')." SET stockcode='$codes' WHERE id=1");
				$retMsgs = '0';
			}
		}
		else
		{
			$retMsgs = '用户 ID 不存在';
		}
		echo "var ret='$retMsgs';";
	}
	private function sell()
	{
		global $_G;
		$retMsgs = '';
		if ( is_numeric($_G['gp_uid']) && $_G['gp_uid'] > 0 )
		{
			$user = self::getUserInfo($_G['gp_uid']);
			$si = DB::fetch_first("SELECT cid, stocknum_ava, buytime FROM ".DB::table('kfss_customer')." WHERE uid='{$_G['gp_uid']}' AND code='{$_G['gp_code']}'");
			if ( is_numeric($si['stocknum_ava']) && $si['stocknum_ava'] > 0 )
			{
				if ( dgmdate($si['buytime'], 'd') == dgmdate($_G['timestamp'], 'd') )
				{
					$retMsgs = '执行 T+1 规则，当日买入股票，最早下一交易日才能卖出';
				}
				else
				{
					if ( $si['stocknum_ava'] < $_G['gp_amount'] )
					{
						$retMsgs = '您没有足够的股票卖出';
					}
					else
					{
						$dealData = array(
							'uid'		=> $user['uid'],
							'username'	=> $user['username'],
							'code'		=> $_G['gp_code'],
							'stockname'	=> mb_convert_encoding($_G['gp_stockname'],'gbk','utf-8'),
							'direction'	=> '2',
							'quant_deal'=> $_G['gp_amount'],
							'price_deal'=> $_G['gp_price'],
							'time_deal'	=> $_G['timestamp'],
							'ok'		=> '0'
						);
						DB::insert('kfss_deal', $dealData);
						// 异常交易监测
						if ( $_G['gp_amount'] > 10000 )
						{
							$exceptData = array(
								'uid'		=> $user['uid'],
								'uname'		=> $user['username'],
								'action'	=> '2',	// 委托卖出
								'stockcode'	=> $_G['gp_code'],
								'amount'	=> $_G['gp_amount'],
								'price'		=> $_G['gp_price'],
								'logtime'	=> $_G['timestamp'],
								'ip'		=> $_G['clientip']
							);
							DB::insert('kfss_exclog', $exceptData);
						}
						DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_ava=stocknum_ava-{$_G['gp_amount']}, stocknum_war=stocknum_war+{$_G['gp_amount']} WHERE cid='{$si['cid']}'");
						$leftNum = DB::result_first("SELECT stocknum_ava FROM ".DB::table('kfss_customer')." WHERE cid='{$si['cid']}'");
						if ( $leftNum == 0 )
						{
							DB::query("UPDATE ".DB::table('kfss_user')." SET trade_times=trade_times+1 WHERE uid='{$user['uid']}'");
						}
						$codes = DB::result_first("SELECT stockcode FROM ".DB::table('kfss_sminfo')." WHERE id=1");
						$codes = $codes ? $codes . '|' . $_G['gp_code'] : $_G['gp_code'];
						DB::query("UPDATE ".DB::table('kfss_sminfo')." SET stockcode='$codes' WHERE id=1");
						$retMsgs = '0';
					}
				}
			}
			else
			{
				$retMsgs = '卖出数量错误';
			}
		}
		else
		{
			$retMsgs = '用户 ID 不存在';
		}
		echo "var ret='$retMsgs';";
	}
	private static function getUserAccount($uid=0)
	{
		$jsData = '""';
		if ( is_numeric($uid) && $uid > 0 )
		{
			$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
			if ( $rs )
				$jsData = '{InitFund:"'.$rs['fund_ini'].'", AvailableFund:"'.$rs['fund_ava'].'", WarrantFund:"'.$rs['fund_war'].'", LastTotalFund:"'.$rs['fund_last'].'", d5ProfitRatio:"'.$rs['profit_d5'].'", TotalRank:"'.$rs['rank'].'", ProfitSellRatio:"'.($rs['trade_ok_times']/$rs['trade_times']*100).'"}';
		}
		return $jsData;
    }
    private static function getUserStocks($uid=0)
    {
    	$holds = '';
    	if ( is_numeric($uid) && $uid > 0 )
		{
			$query = DB::query("SELECT * FROM ".DB::table('kfss_customer')." WHERE uid='$uid'");
			while ( $rs = DB::fetch($query) )
			{
				if ( $rs['stocknum_ava'] || $rs['stocknum_war'] )
				{
					$holds .= '{StockCode:"'.$rs['code'].'", StockName:"'.$rs['stockname'].'", StockAmount:"'.($rs['stocknum_ava']+$rs['stocknum_war']).'", AvailSell:"'.$rs['stocknum_ava'].'", CostFund:"'.$rs['averageprice'].'"},';
				}
			}
			$holds && $holds = substr($holds, 0, -1);
		}
		$jsData .= '['.$holds.']';
		return $jsData;
	}
	private static function getUserInfo($uid=0)
	{
		$data = '';
		if ( is_numeric($uid) && $uid > 0 )
		{
			$data = DB::fetch_first("SELECT * FROM ".DB::table('kfss_user')." WHERE uid='$uid'");
		}
		return $data;
    }
	private function udd()
	{
		global $_G;
		if ( is_numeric($_G['gp_price']) && $_G['gp_price'] > 0 )
		{
			$query = DB::query("SELECT * FROM ".DB::table('kfss_deal')." WHERE code='{$_G['gp_code']}' AND ok='0' AND hide='0'");
			while ( $drs = DB::fetch($query) )
			{
				$worthDeal		= $drs['price_deal'] * $drs['quant_deal'];
				$worthLast		= $_G['gp_price'] * $drs['quant_deal'];
				$commission		= $worthLast * 0.001;	// 佣金，买卖均收取
				$transferFee	= $worthLast * 0.001;	// 过户费，买卖均收取
				$rsc = DB::fetch_first("SELECT cid, stocknum_ava, averageprice FROM ".DB::table('kfss_customer')." WHERE uid='{$drs['uid']}' AND code='{$drs['code']}'");
				if ( $drs['direction'] == 1 && $drs['price_deal'] >= $_G['gp_price'] )
				{
					if ( !$rsc )
					{
						$priceCost = round( ($worthLast+$commission+$transferFee)/$drs['quant_deal'], 2 );
						$psData = array(
							'uid'			=> $drs['uid'],
							'username'		=> $drs['username'],
							'code'			=> $drs['code'],
							'stockname'		=> $drs['stockname'],
							'buyprice'		=> $priceCost,
							'averageprice'	=> $priceCost,
							'stocknum_ava'	=> $drs['quant_deal'],
							'stocknum_war'	=> 0,
							'buytime'		=> $_G['timestamp']
						);
						DB::insert('kfss_customer', $psData);
					}
					else
					{
						$leftNum	= $rsc['stocknum_ava'];
						$avgprice	= round( ( $_G['gp_price'] * $drs['quant_deal'] + $rsc['averageprice'] * $leftNum ) / ( $drs['quant_deal'] + $leftNum), 2 );
						DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_ava=stocknum_ava+{$drs['quant_deal']}, buyprice='$priceCost', averageprice='$priceCost', buytime='{$_G[timestamp]}' WHERE cid='{$rsc['cid']}'");
					}
					$stockFund	= self::calStockFund($drs['uid']);
					DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava-$commission-$transferFee, fund_war=fund_war-{$worthDeal}, fund_last=fund_ava+fund_war+$stockFund, lasttradetime='{$_G['timestamp']}' WHERE uid='{$drs['uid']}'");
					DB::query("UPDATE ".DB::table('kfss_deal')." SET price_tran='{$_G['gp_price']}', time_tran='{$_G[timestamp]}', ok='1' WHERE did='{$drs['did']}'");
					DB::query("INSERT INTO ".DB::table('kfss_transaction')."(uid, code, stockname, direction, quant, price, amount, did, ttime) VALUES('{$drs['uid']}', '{$drs['code']}', '{$drs['stockname']}', 1, '{$drs['quant_deal']}', '{$_G['gp_price']}', '$worthLast', '{$drs['did']}', '{$_G[timestamp]}')");
				}
				else if ( $drs['direction'] == 2 && $drs['price_deal'] <= $_G['gp_price'] )
				{
					$stampDuty		= $worthLast * 0.001;	// 印花税，卖出收取
					DB::query("UPDATE ".DB::table('kfss_customer')." SET stocknum_war=stocknum_war-{$drs['quant_deal']}, selltime='{$_G[timestamp]}' WHERE cid='{$rsc['cid']}'");
					$leftNum = DB::result_first("SELECT stocknum_ava FROM ".DB::table('kfss_customer')." WHERE cid='{$rsc['cid']}'");
					$trade_ok = $leftNum == 0 ? 1 : 0;
					$stockFund	= self::calStockFund($drs['uid']);
					DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+{$worthLast}-$commission-$transferFee-$stampDuty, fund_last=fund_ava+fund_war+$stockFund, lasttradetime='{$_G['timestamp']}', trade_ok_times=trade_ok_times+{$trade_ok} WHERE uid='{$drs['uid']}'");
					DB::query("UPDATE ".DB::table('kfss_deal')." SET quant_tran=quant_deal, price_tran='{$drs['price_deal']}', time_tran='{$_G[timestamp]}', ok='1' WHERE did='{$drs['did']}'");
					DB::query("INSERT INTO ".DB::table('kfss_transaction')."(uid, code, stockname, direction, quant, price, amount, did, ttime) VALUES('{$drs['uid']}', '{$drs['code']}', '{$rs['stockname']}', '1', '{$drs['quant_deal']}', '{$_G['gp_price']}', '$worthLast', '{$drs['did']}', '{$_G[timestamp]}')");
				}
			}
		}
		echo '0';
	}
	private static function calStockFund($user_id)
	{
		$fund = 0;
		if ( is_numeric($user_id) )
		{
			$qsf = DB::query("SELECT stocknum_ava, averageprice FROM ".DB::table('kfss_customer')." WHERE uid='$user_id'");
			while ( $rssf = DB::fetch($qsf) )
			{
				$fund += $rssf['stocknum_ava'] * $rssf['averageprice'];
			}
		}
		return $fund;
	}
}
?>
