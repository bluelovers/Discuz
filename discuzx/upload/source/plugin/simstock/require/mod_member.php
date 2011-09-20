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
class Member
{
	private $member = array();
	public function __construct( $forumuid )
	{
		$this->authorise( $forumuid );
	}
	public function processAction( $action )
	{
		global $_G, $kfsclass;
		$actArray = array('fundsmng', 'stocksmng', 'trustsmng', 'showinfo', 'buy', 'sell');
		try
		{
			if ( empty($action) || !in_array($action, $actArray) )
				throw new Exception('Invalid action');
		}
		catch ( Exception $e )
		{
			showmessage('Messages from Kilofox StockIns ：' . $e->getMessage());
		}
		switch ( $action )
		{
			case 'fundsmng':
				$this->fundsManage($this->member);
			break;
			case 'stocksmng':
				$this->showMemberInfo($this->member['uid']);
			break;
			case 'trustsmng':
				require_once 'class_trust.php';
				new Trust($this->member, $_G['gp_section']);
			break;
			case 'showinfo':
				$this->showMemberInfo($_G['gp_uid']);
			break;
			case 'buy':
				$this->showBuyForm($this->member);
			break;
			case 'sell':
				$this->showSellForm($this->member);
			break;
		}
	}
	private function authorise($forumuid=0)
	{
		global $_G;
		if ( is_numeric($forumuid) && $forumuid > 0 )
		{
			$memberId = DB::result_first("SELECT uid FROM ".DB::table('kfss_user')." WHERE forumuid='$forumuid'");
			if ( $memberId )
			{
				$this->member = self::getMemberInfo($memberId);
			}
			else
			{
				require_once 'class_register.php';
				$register = new register;
				$register->show_reg_form();
			}
		}
	}
	private static function getMemberInfo($user_id=0)
	{
		$r = DB::fetch_first("SELECT * FROM ".DB::table('kfss_user')." WHERE uid='$user_id'");
		if ( $r )
		{
			$r['profit_ratio']		= number_format($r['profit']/$r['fund_ini']*100,2);
			$r['profit_d1_ratio']	= number_format($r['profit_d1']/$r['fund_ini']*100,2);
			$r['profit_d5_ratio']	= number_format($r['profit_d5']/$r['fund_ini']*100,2);
			$r['trade_ok_ratio']	= number_format($r['trade_ok_times']/$r['trade_times']*100,2);
			$r['rank']				= $r['rank'] == 0 ? '-' : $r['rank'];
		}
		return $r;
	}
	private function showMemberInfo($user_id=0)
	{
		global $baseScript, $hkimg, $_G, $db_smname;
		$showAccount = $this->member['uid'] == $user_id ? true : false;
		$m = self::getMemberInfo($user_id);
		$usdb = array();
		$query = DB::query("SELECT code, stocknum_ava, averageprice FROM ".DB::table('kfss_customer')." WHERE uid='$user_id' ORDER BY stocknum_ava DESC");
		while ( $rs = DB::fetch($query) )
		{
			if ( $user_id == $this->member['uid'] )
				$rs['sell'] = '1';
			else
				$rs['sell'] = '0';
			$usdb[] = $rs;
		}
		include template('simstock:member_showinfo');
	}
	private function fundsManage( $user )
	{
		global $_G, $db_credittype;
		$mtype = $_G['gp_mtype'];
		if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
			$creditid	= $db_credittype;
		else
			$creditid	= $_G['setting']['creditstrans'];
		$credit = $_G['setting']['extcredits'][$creditid];
		$user['moneyType']	= $credit['title'];
		$user['moneyUnit']	= $credit['unit'];
		$user['moneyNum']	= getuserprofile('extcredits'.$creditid) ? getuserprofile('extcredits'.$creditid) : 0;
		if ( empty($mtype) )
			$this->showFundsManageForm($user);
		else if ( $mtype == 'd' )
			$this->fundsDeposit($user);
		else if ( $mtype == 'a' )
			$this->fundsAdopt($user);
		else if ( $mtype == 't' )
			$this->fundsTransfer($user);
	}
	private function showFundsManageForm( $user )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_proportion, $db_charge, $db_allowdeposit, $db_allowadopt, $db_allowtransfer, $db_depositmin, $db_adoptmin, $db_transfermin, $db_transfercharge;
		if ( $user['locked'] == 0 )
			$user['state'] = '正常';
		else if ( $user['locked'] == 1 )
			$user['state'] = '<span style="color:#FF0000">冻结</span>';
		else
			$user['state'] = '<span style="color:#0000FF">异常</span>';
		$exchange_rate				= $db_proportion > 0 ? $db_proportion : 1;
		$commission_charge			= $db_charge > 0 ? $db_charge : 0;
		$commission_charge_trans	= $db_transfercharge > 0 ? $db_transfercharge : 0;
		include template('simstock:member_fundsmng');
	}
	private function showBuyForm( $user, $stock_id=0 )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_wavemax, $db_dutyrate, $db_dutymin, $db_tradenummin;
		include template('simstock:member_buy');
	}
	private function showSellForm( $user, $stock_id=0 )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_wavemax, $db_dutyrate, $db_dutymin, $db_tradenummin;
		include template('simstock:member_sell');
	}
	private function fundsDeposit( $user )
	{
		global $baseScript, $_G, $db_charge, $db_allowdeposit, $db_depositmin, $db_proportion, $db_credittype;
		if ( $db_allowdeposit <> '1' )
		{
			showmessage('对不起，存款功能已关闭');
		}
		else
		{
			if ( $user['locked'] <> 0 )
			{
				showmessage('对不起，您的帐户已被冻结，无法存款');
			}
			else
			{
				$money_in = $_G['gp_moneyi'];
				( !is_numeric($money_in) || $money_in <= 0 ) && showmessage('请输入正确的存款金额');
				if ( $money_in < $db_depositmin )
				{
					showmessage("对不起，您要存入的{$user[moneyType]}数量不能小于 $db_depositmin {$user[moneyUnit]}");
				}
				else
				{
					$money_sm = $money_in * $db_proportion;	// 论坛货币兑换成股市货币
					$comm_charge = $money_sm * $db_charge/100;	// 论坛货币与股市资金互兑手续费全部从股市中扣除；可以为0
					if ( $money_in > $user['moneyNum'] )
					{
						showmessage("对不起，您的{$user['moneyType']}不足。<br/>您要存入{$user['moneyType']} $money_in {$user['moneyUnit']}，您只有{$user['moneyType']} <font color=\"#FF0000\">{$user['moneyNum']}</font> {$user['moneyUnit']}。");
					}
					else
					{
						if ( $db_proportion > 0 && $db_charge >= 0 )
						{
							DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+{$money_sm}-{$comm_charge} WHERE uid='{$user[uid]}'");
							if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
								$creditid	= $db_credittype;
							else
								$creditid	= $_G['setting']['creditstrans'];
							DB::query("UPDATE ".DB::table('common_member_count')." SET extcredits".$creditid."=extcredits".$creditid."-{$money_in} WHERE uid='{$_G['uid']}'");
							showmessage("您已经把论坛{$user[moneyType]} $money_in {$user[moneyUnit]}折合股市资金 ".number_format($money_sm,2)." 元存进了您的股市帐户，扣除手续费 ".number_format($comm_charge,2)." 元", "$baseScript&mod=member&act=fundsmng");
						}
						else
						{
							showmessage('致命错误：论坛货币与股市资金兑换比例有误，无法存款！');
						}
					}
				}
			}
		}
	}
	private function fundsAdopt( $user )
	{
		global $baseScript, $_G, $db_allowadopt, $db_charge, $db_adoptmin, $db_proportion, $db_initialmoney, $db_credittype;
		if ( $db_allowadopt <> '1' )
		{
			showmessage('对不起，取款功能已关闭');
		}
		else
		{
			if ( $user['locked'] <> 0 )
			{
				showmessage('对不起，您的帐户已被冻结，无法取款');
			}
			else
			{
				$money_x = $_G['gp_moneyx'];
				( !is_numeric($money_x) || $money_x <=0 ) && showmessage('请输入正确的取款金额');
				if ( ( $user['fund_ava'] - $money_x ) < $db_initialmoney )
				{
					showmessage('本股市规定：帐户可用资金不能低于 '.number_format($db_initialmoney,2).' 元。<br/>您要取款 '.number_format($money_x,2).' 元，帐户中现有可用资金 <font color="#FF0000">'.number_format($user['fund_ava'],2).'</font> 元。');
				}
				else
				{
					if ( $money_x < $db_adoptmin )
					{
						showmessage('对不起，取款金额不能少于 '.number_format($db_adoptmin,2).' 元');
					}
					else
					{
						if ( $money_x > $user['fund_ava'] )
						{
							showmessage('对不起，您的帐户可用资金不足。您要取款 '.number_format($money_x,2).' 元，帐户中现有可用资金 '.number_format($user['fund_ava'],2).' 元。');
						}
						else
						{
							if ( $db_proportion > 0 && $db_charge >= 0 )
							{
								$comm_charge = $money_x * $db_charge/100;
								$money_f = (int)($money_x / $db_proportion);	// 股市货币兑换成论坛货币
								DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava-($money_x+$comm_charge) WHERE uid='{$user[uid]}'");
								if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
									$creditid	= $db_credittype;
								else
									$creditid	= $_G['setting']['creditstrans'];
								DB::query("UPDATE ".DB::table('common_member_count')." SET extcredits".$creditid."=extcredits".$creditid."+{$money_f} WHERE uid='{$_G['uid']}'");
								showmessage("您已经把股市资金 ".number_format($money_x,2)." 元折合论坛{$user[moneyType]} $money_f {$user[moneyUnit]}存进了您的论坛帐户，扣除手续费 ".number_format($comm_charge,2)." 元", "$baseScript&mod=member&act=fundsmng");
							}
							else
							{
								showmessage('致命错误：论坛货币与股市资金兑换比例有误，无法取款！');
							}
						}
					}
				}
			}
		}
	}
	private function fundsTransfer( $user )
	{
		global $baseScript, $_G, $db_allowtransfer, $db_transfercharge, $db_transfermin, $db_charge, $db_proportion, $db_initialmoney;
		if ( $db_allowtransfer <> '1' )
		{
			showmessage('转帐功能已关闭');
		}
		else
		{
			if ( $user['locked'] <> 0 )
			{
				showmessage('对不起，您的帐户已被冻结，无法转帐');
			}
			else
			{
				$money_t = $_G['gp_moneyt'];
				if ( !is_numeric($money_t) || $money_t <= 0 )
					showmessage('请输入正确的转帐资金');
				else
				{
					$comm_charge = $money_t * $db_transfercharge / 100;
					if ( $money_t + $comm_charge > $user['fund_ava'] )
					{
						showmessage('对不起，您的帐户可用资金不足。您欲转帐 '.number_format($money_t,2).'元，手续费需 '.number_format($commission_charge,2).' 元，帐户中现有可用资金 '.number_format($user['fund_ava'],2).' 元。');
					}
				}
				$towho = $_G['gp_towho'];
				if ( !$towho )
					showmessage('请输入收款人名字');
				else
				{
					if ( $towho == $user['username'] )
						showmessage('您不能把资金送给自己');
					else
					{
						$rs = DB::fetch_first("SELECT uid, username, locked FROM ".DB::table('kfss_user')." WHERE username='$towho'");
						if ( !$rs )
							showmessage("收款人 $towho 未找到");
						else
						{
							if ( $rs['locked'] == 1 )
							{
								showmessage("$towho 的帐户已被冻结，无法接受您的转帐资金");
							}
							else
							{
								if ( $user['fund_ava'] - $money_t < $db_initialmoney )
								{
									showmessage('本股市规定：帐户可用资金不得低于 '.number_format($db_initialmoney,2).' 元。您欲转帐 '.number_format($money_t,2).' 元，手续费需 '.number_format($comm_charge,2).' 元。');
								}
								else
								{
									if ( $money_t < $db_transfermin )
									{
										showmessage("对不起，转帐金额不能少于 ".number_format($db_transfermin,2)." 元");
									}
									else
									{
										DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava-($money_t+$comm_charge) WHERE uid='{$user[uid]}'");
										DB::query("UPDATE ".DB::table('kfss_user')." SET fund_ava=fund_ava+{$money_t} WHERE uid='{$rs[uid]}'");
										$subject = "股民 $user[username] 资金过户成功！";
										$content = "股民 [url=$baseScript&act=showuser&uid={$user['id']}]{$user['username']}[/url] 将 ".number_format($money_t,2)." 元帐户资金过户给 [url=$baseScript&act=showuser&uid={$rs['uid']}]{$rs['username']}[/url] 。请 {$rs[username]} 注意查收。";
										DB::query("INSERT INTO ".DB::table('kfss_news')."(subject, content, color, addtime) VALUES ('$subject', '$content', 'StockIns', '$_G[timestamp]')");
										showmessage("您已经把股市资金 ".number_format($money_t,2)." 元转给 {$rs[username]} ，扣除手续费 ".number_format($comm_charge,2)."元", "$baseScript&mod=member&act=fundsmng");
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
?>
