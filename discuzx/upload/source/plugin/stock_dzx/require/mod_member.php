<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-10
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Member
{
	private $member = array();
	public function __construct( $forumuid )
	{
		$user = $this->authorise( $forumuid );
		return $user;
	}
	public function processAction( $action )
	{
		global $_G, $kfsclass;
		$actArray = array('fundsmng', 'stocksmng', 'trustsmng', 'showinfo', 'subscribe', 'apply', 'cutamelon');
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
				$kfsclass->calculatefund($this->member['id'],0);
				$this->showMemberInfo($this->member['id']);
			break;
			case 'trustsmng':
				require_once 'class_trust.php';
				new Trust($this->member, $_G['gp_section']);
			break;
			case 'showinfo':
				$this->showMemberInfo($_G['gp_uid']);
			break;
			case 'subscribe':
				require_once 'class_subscribe.php';
				new Subscribe($this->member, $_G['gp_section'], $_G['gp_sid']);
			break;
			case 'apply':
				require_once 'class_apply.php';
				new Apply($this->member, $_G['gp_section']);
			break;
			case 'cutamelon':
				global $sid;
				if ( $_G['gp_section'] == 'docut' )
					$this->shareOutBonus($this->member, $_G['gp_sid']);
				else
					$this->showBonusForm($this->member, $_G['gp_sid']);
			break;
		}
	}
	private function authorise($forumuid=0)
	{
		global $_G;
		$rs = DB::fetch_first("SELECT * FROM kfsm_user WHERE forumuid='$forumuid'");
		if ( !$rs )
		{
			require_once 'class_register.php';
			$register = new register;
			$register->show_reg_form();
		}
		else
		{
			$this->member['id']				= $rs['uid'];
			$this->member['username']		= $rs['username'];
			$this->member['userclass']		= $rs['userclass'];
			$this->member['locked']			= $rs['locked'];
			$this->member['capital_ava']	= number_format($rs['capital_ava'],2,'.','');
			$this->member['asset']			= number_format($rs['asset'],2,'.','');
			$this->member['stocksort']		= $rs['stocksort'];
			$this->member['todaybuy']		= $rs['todaybuy'];
			$this->member['todaysell']		= $rs['todaysell'];
			$this->member['stocknum']		= $rs['stocknum'];
			$this->member['stockcost']		= $rs['stockcost'];
			$this->member['stockvalue']		= $rs['stockvalue'];
			if ( $this->member['stockvalue'] > $this->member['stockcost'] )
				$this->member['profit'] = "<font color=\"#FF0000\">↑".number_format($this->member['stockvalue'] - $this->member['stockcost'],2)."</font>";
			else if ( $this->member['stockvalue'] < $this->member['stockcost'] )
				$this->member['profit'] = "<font color=\"#008000\">↓".number_format($this->member['stockcost'] - $this->member['stockvalue'],2)."</font>";
			else
				$this->member['profit'] = "→0.00";
			$this->member['stockcost']	= number_format($this->member['stockcost'],2);
			$this->member['stockvalue']	= number_format($this->member['stockvalue'],2);
			if ( $rs['ip'] <> $_G['clientip'] )
			{
				$this->member['ip'] = $_G['clientip'];
				DB::query("UPDATE kfsm_user SET ip='$_G[clientip]' WHERE uid='{$rs[uid]}'");
			}
			else
			{
				$this->member['ip'] = $rs['ip'];
			}
		}
		return $this->member;
    }
	private function showMemberInfo($user_id=0)
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_meloncutting;
		if ( $this->member['id'] == $user_id )
			$showAccount = true;
		else
			$showAccount = false;
		if ( $user_id > 0 )
		{
			$userData = DB::fetch_first("SELECT username FROM kfsm_user WHERE uid='$user_id'");
			$username = $userData['username'];
		}
		else
		{
			$username = '-';
		}
		$usdb = array();
		$query = DB::query("SELECT c.*, s.sid, s.stockname, s.currprice, s.holder_id, s.state FROM kfsm_customer c LEFT JOIN kfsm_stock s ON s.sid=c.sid WHERE c.cid='$user_id' ORDER BY c.stocknum DESC");
		while ( $rs = DB::fetch($query) )
		{
			$averageprice	= $rs['averageprice'];
			$currentprice	= $rs['currprice'];
			$stockcost		= $averageprice * $rs['stocknum'];
			$profit			= $currentprice - $averageprice;
			$totalprofit	= $profit * $rs['stocknum'];
			$rs['averageprice']	= number_format($averageprice,2);
			$rs['currprice']	= number_format($currentprice,2);
			$rs['stockcost']	= number_format($stockcost,2);
			$rs['stockvalue']	= number_format($currentprice*$rs['stocknum'],2);
			$rs['profit']		= number_format($profit,2);
			$rs['totalprofit']	= number_format($totalprofit,2);
			$rs['profit_p']		= number_format($totalprofit/$stockcost*100,2);
			if ( $profit > 0 )
				$rs['color']	= '#FF0000';
			else if ( $profit < 0 )
				$rs['color']	= '#008000';
			else if ( $profit == 0 )
				$rs['color']	= '';
			if ( $db_meloncutting == '1' && $user_id == $this->member['id'] && $rs['holder_id'] == $this->member['id'] && $rs['state'] == 0 )
				$rs['manage'] = "<a href=\"$baseScript&mod=member&act=cutamelon&sid=$rs[sid]\">分红</a>";
			else
				$rs['manage'] = '<strike>分红</strike>';
			$usdb[] = $rs;
		}
		include template('stock_dzx:member_showinfo');
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
		include template('stock_dzx:member_fundsmng');
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
						showmessage("对不起，您的{$user['moneyType']}不足。<br/>您要存入{$user['moneyType']} $money_in {$user['moneyUnit']}，您只有{$user[moneyType]} <font color=\"#FF0000\">{$user['moneyNum']}</font> {$user['moneyUnit']}。");
					}
					else
					{
						if ( $db_proportion > 0 && $db_charge >= 0 )
						{
							DB::query("UPDATE kfsm_user SET capital=capital+{$money_sm}-{$comm_charge}, capital_ava=capital_ava+{$money_sm}-{$comm_charge}, asset=asset+'$money_sm' WHERE uid='{$user[id]}'");
							if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
								$creditid	= $db_credittype;
							else
								$creditid	= $_G['setting']['creditstrans'];
							DB::query("UPDATE ".DB::table('common_member_count')." SET extcredits".$creditid."=extcredits".$creditid."-{$money_in} WHERE uid='{$_G['uid']}'");
							DB::query("INSERT INTO kfsm_smlog (type, username2, field, descrip, timestamp, ip) VALUES('用户管理', '{$user[username]}', '资金流动', '用户 {$user[username]} 把论坛{$user['moneyTyp']} $money_in {$user['moneyUnit']}存入股市帐户，折合股市资金 ".number_format($money_sm,2)." 元，扣除手续费 ".number_format($comm_charge,2)." 元', '$_G[timestamp]', '$_G[clientip]')");
							showmessage("您已经把论坛{$user[moneyType]} $money_in {$user[moneyUnit]}折合股市资金 ".number_format($money_sm,2)." 元存进了您的股市帐户，扣除手续费 ".number_format($comm_charge,2)." 元", "$baseScript&mod=member&act=fundsmng");
						}
						else
						{
							showmessage('致命错误：论坛货币与股市资金兑换比例有误，无法存款！');	// 强力判断论坛货币与股市资金兑换比例、手续费，以避免可能因后台设置错误而引起论坛货币或股市资金数量混乱。
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
				if ( ( $user['capital_ava'] - $money_x ) < $db_initialmoney )
				{
					showmessage('本股市规定：帐户可用资金不能低于 '.number_format($db_initialmoney,2).' 元。<br/>您要取款 '.number_format($money_x,2).' 元，帐户中现有可用资金 <font color="#FF0000">'.number_format($user['capital_ava'],2).'</font> 元。');
				}
				else
				{
					if ( $money_x < $db_adoptmin )
					{
						showmessage('对不起，取款金额不能少于 '.number_format($db_adoptmin,2).' 元');
					}
					else
					{
						if ( $money_x > $user['capital_ava'] )
						{
							showmessage('对不起，您的帐户可用资金不足。您要取款 '.number_format($money_x,2).' 元，帐户中现有可用资金 '.number_format($user['capital_ava'],2).' 元。');
						}
						else
						{
							if ( $db_proportion > 0 && $db_charge >= 0 )
							{
								$comm_charge = $money_x * $db_charge/100;
								$money_f = (int)($money_x / $db_proportion);	// 股市货币兑换成论坛货币
								DB::query("UPDATE kfsm_user SET capital=capital-($money_x+$comm_charge), capital_ava=capital_ava-($money_x+$comm_charge), asset=asset-{$money_x}-{$comm_charge} WHERE uid='{$user[id]}'");
								if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
									$creditid	= $db_credittype;
								else
									$creditid	= $_G['setting']['creditstrans'];
								DB::query("UPDATE ".DB::table('common_member_count')." SET extcredits".$creditid."=extcredits".$creditid."+{$money_f} WHERE uid='{$_G['uid']}'");
								DB::query("INSERT INTO kfsm_smlog (type, username2, field, descrip, timestamp, ip) VALUES('用户管理', '{$user[username]}','资金流动','用户 {$user[username]} 把股市资金 ".number_format($money_x,2)." 元提取到论坛帐户，扣除手续费 ".number_format($comm_charge,2)." 元，折合论坛{$user[moneyType]} $money_f {$user[moneUnit]}', '$_G[timestamp]', '$_G[clientip]')");
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
					if ( $money_t + $comm_charge > $user['capital_ava'] )
					{
						showmessage('对不起，您的帐户可用资金不足。您欲转帐 '.number_format($money_t,2).'元，手续费需 '.number_format($commission_charge,2).' 元，帐户中现有可用资金 '.number_format($user['capital_ava'],2).' 元。');
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
						$rs = DB::fetch_first("SELECT uid, username, locked FROM kfsm_user WHERE username='$towho'");
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
								if ( $user['capital_ava'] - $money_t < $db_initialmoney )
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
										DB::query("UPDATE kfsm_user SET capital=capital-($money_t+$comm_charge), capital_ava=capital_ava-($money_t+$comm_charge), asset=asset-($money_t+$comm_charge) WHERE uid='{$user[id]}'");
										DB::query("UPDATE kfsm_user SET capital=capital+{$money_t}, capital_ava=capital_ava+{$money_t}, asset=asset+{$money_t} WHERE uid='{$rs[uid]}'");
										$subject = "股民 $user[username] 资金过户成功！";
										$content = "股民 [url=$baseScript&act=showuser&uid={$user['id']}]{$user['username']}[/url] 将 ".number_format($money_t,2)." 元帐户资金过户给 [url=$baseScript&act=showuser&uid={$rs['uid']}]{$rs['username']}[/url] 。请 {$rs[username]} 注意查收。";
										DB::query("INSERT INTO kfsm_news(subject, content, color, addtime) VALUES ('$subject', '$content', 'StockIns', '$_G[timestamp]')");
										DB::query("INSERT INTO kfsm_smlog (type, username1, username2, field, descrip, timestamp, ip) VALUES('用户管理','$rs[username]','$user[username]','资金流动','用户 {$user[username]} 将 ".number_format($money_t,2)." 元转帐给 {$rs[username]} ，扣除手续费 $comm_charge 元', '$_G[timestamp]', '$_G[clientip]')");
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
	private function showBonusForm( $user, $stock_id )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_otherpp;
		if ( $rs = $this->checkMelonCondition( $user, $stock_id ) )
		{
			$cs = DB::fetch_first("SELECT COUNT(*) AS usernum, SUM(stocknum) AS stocknum FROM kfsm_customer WHERE sid='$stock_id'");
			$sn = DB::fetch_first("SELECT stocknum FROM kfsm_customer WHERE cid='$user[id]' AND sid='$stock_id'");
	        require_once 'mod_stock.php';
			$stock = new stock('call');
			$cnt = $cs['usernum'];
			if ( $cnt > 0 )
			{
				$readperpage = $db_otherpp;
				$page = $_G['gp_page'];
				if ( $page <= 1 )
				{
					$page = 1;
					$start = 0;
				}
				$numofpage = ceil($cnt/$readperpage);
				if ( $page > $numofpage )
				{
					$page = $numofpage;
					$start-=1;
				}
				$start = ( $page - 1 ) * $readperpage;
				$pages = foxpage($cnt,$page,$numofpage,"$baseScript&mod=member&act=cutamelon&sid=$stock_id&");
				$holders = $stock->getStockholdersList($stock_id, $rs['currprice'], $cs['stocknum'], $start, $readperpage);
			}
			include template('stock_dzx:member_cutamelon');
		}
	}
	private function shareOutBonus($user, $stock_id)
	{
		global $baseScript, $kfsclass, $_G;
		if ( $rs = $this->checkMelonCondition( $user, $stock_id ) )
		{
			$cutnum1	= $_G['gp_cutnum1'];
			$cutnum2	= $_G['gp_cutnum2'];
			$cutnum3	= $_G['gp_cutnum3'];
			$tn			= DB::fetch_first("SELECT SUM(stocknum) AS stocknum FROM kfsm_customer WHERE sid='$stock_id'");
			$totalNum	= $tn['stocknum'];
			if ( $_G['gp_cuttype'] == '1' )
			{
				if ( !is_numeric($cutnum1) || $cutnum1 <= 0 )
					showmessage('请输入正确的分红金额');
				else
					$cutnum1 = round($cutnum1,2);
				$feesNeed = $cutnum1 * ceil($totalNum/100);
				if ( $user['capital_ava'] < $feesNeed )
					showmessage('您的帐户中没有足够的资金用来分红，请适当减少分红金额');
				else
				{
					DB::query("UPDATE kfsm_user SET capital=capital-{$feesNeed}, capital_ava=capital_ava-{$feesNeed}, asset=asset-{$feesNeed} WHERE uid='$user[id]'");
					$query = DB::query("SELECT * FROM kfsm_customer WHERE sid='$stock_id'");
					while ( $rc = DB::fetch($query) )
					{
						$addmoney = $cutnum1 * ceil($rc['stocknum']/100);
						DB::query("UPDATE kfsm_user SET capital=capital+{$addmoney}, capital_ava=capital_ava+{$addmoney}, asset=asset+{$addmoney} WHERE uid='$rc[cid]'");
					}
					$subject = "上市公司 {$rs[stockname]} 向全体股东分配红利";
					$content = "上市公司 <a href=\"hack.php?H_name=stock&mod=stock&act=showinfo&id=$stock_id\">{$rs[stockname]}</a> 大股东 <a href=\"hack.php?H_name=stock&mod=member&act=showinfo&id=$user[id]\">{$user[username]}</a> 向全体持有该公司股票的股东分配红利。\n分红方案为每 <span class=\"s3\">100</span> 股派送现金 <span class=\"s3\">$cutnum1</span> 元！";
					DB::query("INSERT INTO kfsm_news (subject, content, color, author, addtime) VALUES('$subject', '$content', '008000', 'StockIns', '$_G[timestamp]')");
					DB::query("INSERT INTO kfsm_smlog (type, username2, field, descrip, timestamp, ip) VALUES('分红管理', '{$user[username]}', '股票分红', '上市公司 {$rc[stockname]} 每 100 股现金派息 ".number_format($cutnum1,2)." 元', '$_G[timestamp]', '$_G[clientip]')");
					showmessage('红利分配成功，每 100 股现金派息 '.number_format($cutnum1,2).' 元', $baseScript);
				}
			}
			else if ( $_G['gp_cuttype'] == '2' )
			{
				if ( !is_numeric($cutnum2) || $cutnum2 <= 0 )
					showmessage('请输入正确的送股数量');
				else
					$cutnum2 = (int)$cutnum2;
				$stockPrice = DB::query("SELECT currprice FROM kfsm_stock WHERE sid='$stock_id'");
				$feesNeed = $stockPrice * $cutnum2 * ceil($totalNum/100);
				if ( $user['capital_ava'] < $feesNeed )
					showmessage('您的帐户中没有足够的资金用来分红，请适当减少分红股数');
				else
				{
					DB::query("UPDATE kfsm_user SET capital=capital-{$feesNeed}, capital_ava=capital_ava-{$feesNeed}, asset=asset-{$feesNeed} WHERE uid='$user[id]'");
					$query = DB::query("SELECT cid, sid, stocknum FROM kfsm_customer WHERE sid='$stock_id'");
					while ( $rc = DB::fetch($query) )
					{
						$addnum = $cutnum2 * ceil($rc['stocknum']/100);
						DB::query("UPDATE kfsm_customer SET stocknum=stocknum+{$addnum} WHERE cid='$rc[cid]' AND sid='$rc[sid]'");
						$kfsclass->calculatefund($rc['cid'],0);
					}
					$subject = "上市公司 {$rs[stockname]} 向全体股东分配红利";
					$content = "上市公司 [url=plugin.php?id=stock:index&mod=stock&act=showinfo&sid=$stock_id]{$rs['stockname']}[/url] 大股东 [url=plugin.php?id=stock:index&mod=member&act=showinfo&uid={$user[id]}]{$user[username]}[/url] 向全体持有该公司股票的股东分配红利。\n分红方案为每 100 股派送股票 $cutnum2 股！";
					DB::query("INSERT INTO kfsm_news (subject, content, color, author, addtime) VALUES('$subject', '$content', '008000', 'StockIns', '$_G[timestamp]')");
					DB::query("INSERT INTO kfsm_smlog (type, username2, field, descrip, timestamp, ip) VALUES('分红管理', '{$user[username]}', '股票分红', '上市公司 {$rs[stockname]} 每 100 股派送股票 {$cutnum2} 股', '$_G[timestamp]', '$_G[clientip]')");
					showmessage("红利分配成功，每 100 股派送股票 $cutnum2 股", $baseScript);
				}
			}
			else if ( $_G['gp_cuttype'] == '3' )
			{
				// 3.分红转增股本：转增股本相当于股民没得到好处，只是最大股东为了扩大公司的股本而设立的，比如每10股转增10股，则是说如果现价是10元，则所有持有该股的股民，每10股则增加10股，但同时，股价也腰斩了，价格成为5元了，就是重新按照现在的市值除以转增股后的股数来计算股价，是多少就变成多少。
			}
			else
			{
				showmessage('请选择一种分红方式');
			}
		}
		else
		{
			showmessage('分红系统出现错误');
		}
	}
	private function checkMelonCondition( $user, $stock_id )
	{
		global $db_meloncutting;
		if ( $db_meloncutting <> '1' )
			showmessage('股东分红功能已经关闭');
		else
		{
			$rs = DB::fetch_first("SELECT sid, stockname, currprice, holder_id, state FROM kfsm_stock WHERE sid='$stock_id'");
			if ( !$rs )
				showmessage('没有找到指定的上市公司，可能该上市公司已经倒闭');
			else
			{
				if ( $rs['state'] <> 0 )
					showmessage('该股票状态异常，不能进行分红操作');
				else
				{
					if ( $rs['holder_id'] <> $user['id'] )
						showmessage('您不是该股最大股东，不能进行分红操作');
					else
					{
						$usernum = DB::result_first("SELECT COUNT(*) FROM kfsm_customer c LEFT JOIN kfsm_user u ON c.cid=u.uid WHERE u.ip<>'$user[ip]' AND c.sid='$stock_id'");
						if ( $usernum < 1 )
							showmessage('该公司股东人数太少，不能分红');
						else
							return $rs;
					}
				}
			}
		}
	}
}
?>
