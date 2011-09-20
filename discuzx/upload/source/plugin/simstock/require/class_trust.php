<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-08-06
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
			if ( $_G['gp_code'] && $_G['gp_tradetype'] == 'b' )
				$this->buyStock($user, $_G['gp_code'], $_G['gp_price_buy'], $_G['gp_num_buy']);
			else if ( $_G['gp_code'] && $_G['gp_tradetype'] == 's' )
				$this->sellStock($user, $_G['gp_code'], $_G['gp_price_sell'], $_G['gp_num_sell']);
			else
				$this->showTradeForm($user, $_G['gp_code']);
		}
		else
			showmessage('暂时停止交易，请您稍后再来');
	}
	private function showMyDeals( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$qd = DB::query("SELECT * FROM ".DB::table('kfss_deal')." WHERE uid='{$user['uid']}' ORDER BY did DESC");
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
		include template('simstock:member_trustsmng');
	}
	private function showMyTrans( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$qt = DB::query("SELECT * FROM ".DB::table('kfss_transaction')." WHERE uid='{$user['uid']}' ORDER BY tid DESC");
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
		include template('simstock:member_trustsmng');
	}
	private function cancelDeal( $user, $deal_id )
	{
		global $baseScript, $db_dutyrate, $db_dutymin, $kfsclass;
		$qd = DB::fetch_first("SELECT * FROM ".DB::table('kfss_deal')." WHERE did='$deal_id' AND uid='{$user['uid']}'");
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
				if ( $qd['direction'] == 1 )
				{
					DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+{$worth} WHERE uid='{$user['uid']}'");
					DB::query("UPDATE ".DB::table('kfss_deal')." SET ok='3' WHERE did='{$qd[did]}'");
					showmessage('委托买入股票撤销成功！', "$baseScript&mod=member&act=trustsmng");
				}
				else if ( $qd['direction'] == 2 )
				{
					DB::query("UPDATE ".DB::table('kfss_deal')." SET ok='3' WHERE did='{$qd[did]}'");
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
