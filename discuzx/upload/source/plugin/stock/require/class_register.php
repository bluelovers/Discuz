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
class Register
{
	private $passed = false;
	private $foxinfo = array();
	public function show_reg_form()
	{
		global $baseScript, $_G, $user, $db_smname, $db_minmoney, $db_mincredit, $db_minpost, $db_allowregister, $db_credittype, $hkimg;
		if ( !$user['id'] )
		{
			if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
				$creditid	= $db_credittype;
			else
				$creditid	= $_G['setting']['creditstrans'];
			$credit = $_G['setting']['extcredits'][$creditid];
			$this->foxinfo['money']['type']	= $credit['title'];
			$this->foxinfo['money']['unit']	= $credit['unit'];
			$this->foxinfo['money']['num']	= getuserprofile('extcredits'.$creditid) ? getuserprofile('extcredits'.$creditid) : 0;
			$this->foxinfo['posts']['num']	= getuserprofile('posts') ? getuserprofile('posts') : 0;
			$ask = 0;
			if ( $this->foxinfo['money']['num'] < $db_minmoney )
			{
				$this->foxinfo['m'] = 0;
			}
			else
			{
				$ask+=1;
				$this->foxinfo['m'] = 1;
			}
			if ( $_G['member']['credits'] < $db_mincredit )
			{
				$this->foxinfo['c'] = 0;
			}
			else
			{
				$ask+=1;
				$this->foxinfo['c'] = 1;
			}
			if ( $this->foxinfo['posts']['num'] < $db_minpost )
			{
				$this->foxinfo['p'] = 0;
			}
			else
			{
				$ask+=1;
				$this->foxinfo['p'] = 1;
			}
			if ( $ask < 3 )
				$this->passed = false;
			else
				$this->passed = true;
			include template('stock:register');exit;
		}
		else
		{
			showmessage('您在股市里已经开户，请勿重复注册！');
		}
   	}
	public function create_account()
	{
		global $baseScript, $_G, $db_allowregister, $db_minmoney, $db_mincredit, $db_minpost, $db_smname, $db_credittype, $db_initialmoney;
	   	if ( !is_numeric($_G['uid']) || $_G['uid'] <= 0 )
	   	{
			showmessage('游客无法在股市开户，请登录论坛', "$baseScript");
		}
		else
		{
			if ( $db_allowregister <> '1' )
			{
				showmessage('股市已暂停开户');
			}
			else
			{
				$userId = DB::result_first("SELECT uid FROM ".DB::table('kfsm_user')." WHERE forumuid='$_G[uid]'");
				if ( $userId )
				{
					showmessage('您在股市里已经开户，请勿重复注册！');
				}
				else
				{
					if ( $db_credittype && $_G['setting']['extcredits'][$db_credittype] )
						$creditid	= $db_credittype;
					else
						$creditid	= $_G['setting']['creditstrans'];
					$credit = $_G['setting']['extcredits'][$creditid];
					$this->foxinfo['money']['type']	= $credit['title'];
					$this->foxinfo['money']['unit']	= $credit['unit'];
					$this->foxinfo['money']['num']	= getuserprofile('extcredits'.$creditid) ? getuserprofile('extcredits'.$creditid) : 0;
					if ( $this->foxinfo['money']['num'] < $db_minmoney )
					{
						showmessage("本股市已限制了开户最少{$this->foxinfo['money']['type']}为 $db_minmoney {$this->foxinfo['money']['unit']}，您现在不符合要求，暂时不能开户");
					}
					if ( $_G['member']['credits'] < $db_mincredit )
					{
						showmessage("本股市已限制了开户最少积分为 $db_mincredit ，您现在不符合要求，暂时不能开户");
					}
					if ( getuserprofile('posts') < $db_minpost )
					{
						showmessage("本股市已限制了开户最少发帖数为 $db_minpost ，您不符合要求，暂时不能开户");
					}
					DB::query("INSERT INTO ".DB::table('kfsm_user')." (forumuid, username, fund_ava, fund_war, asset, regtime, lasttradetime, locked) VALUES('$_G[uid]', '$_G[username]', '$db_initialmoney', '0', '$db_initialmoney', '$_G[timestamp]', '$_G[timestamp]', '0')");
					showmessage("股市开户成功！<br /> ".$db_smname." 欢迎您的到来，特赠送给您 ".$db_initialmoney." 元股市资金！", "$baseScript");
				}
			}
		}
	}
}
?>
