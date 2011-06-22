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
class Apply
{
	public function getNewApplyNum()
	{
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_apply')." WHERE state='0'");
		return $num ? $num : 0;
	}
	public function getApplyList()
	{
		global $baseScript;
		$query = DB::query("SELECT * FROM ".DB::table('kfsm_apply')." ORDER BY applytime DESC");
		$esdb = array();
		$i = 1;
		while ( $rs = DB::fetch($query) )
		{
			$rs['order'] = $i;
			$i++;
			if ( $rs['state'] == 0 )
			{
				$rs['state'] = '待审核';
				$rs['operate'] = "<a href=\"?$baseScript&mod=usmng&ops=pass&aid=$rs[aid]\">批准</a> <a href=\"?$baseScript&mod=usmng&ops=deny&aid=$rs[aid]\">拒绝</a>";
			}
			else if ( $rs['state'] == 1 )
			{
				$rs['state'] = '已批准';
				$rs['operate'] = "<strike>批准</strike> <strike>拒绝</strike>";
			}
			else if ( $rs['state'] == 2 )
			{
				$rs['state'] = '已拒绝';
				$rs['operate'] = "<strike>批准</strike> <strike>拒绝</strike>";
			}
			else if ( $rs['state'] == 3 )
			{
				$rs['state'] = '已上市';
				$rs['operate'] = "<strike>批准</strike> <strike>拒绝</strike>";
			}
			else
			{
				$rs['state'] = '异常';
				$rs['operate'] = "<strike>批准</strike> <strike>拒绝</strike>";
			}
			$rs['price'] = number_format($rs['price'],2);
			$rs['applytime'] = dgmdate($rs['applytime']);
			$esdb[] = $rs;
		}
		return $esdb;
	}
	public function userStockManage($apply_id=0)
	{
		global $baseScript, $_G;
		$ops = $_G['gp_ops'];
		$aprs = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_apply')." WHERE aid='$apply_id'");
		if ( !$aprs )
			cpmsg('未找到指定的股票数据', '', 'error');
		else
		{
			$baseScript .= '&mod=esset';
			if ( $ops == 'pass' )
			{
				if ( $aprs['state'] == 1 )
					cpmsg('该公司已被批准上市，请勿重复提交', '', 'error');
				else if ( $aprs['state'] == 2 )
					cpmsg('该公司已被拒绝上市，请勿重复提交', '', 'error');
				else if ( $aprs['state'] == 0 )
				{
					$photo = rand(0,5).'.jpg';
					$comintro = addslashes($aprs['comintro']);
					$stockData = array(
						'stockname'		=> $aprs['stockname'],
						'openprice'		=> $aprs['stockprice'],
						'currprice'		=> $aprs['stockprice'],
						'lowprice'		=> $aprs['stockprice'],
						'highprice'		=> $aprs['stockprice'],
						'issueprice'	=> $aprs['stockprice'],
						'issuenum'		=> $aprs['stocknum'],
						'issuer_id'		=> $aprs['userid'],
						'issuer_name'	=> $aprs['username'],
						'holder_id'		=> $aprs['userid'],
						'holder_name'	=> $aprs['username'],
						'issuetime'		=> $_G['timestamp'],
						'comphoto'		=> $photo,
						'comintro'		=> $comintro,
						'state'			=> '4'
					);
					$newsid = DB::insert('kfsm_stock', $stockData, true);
					$issuer_stock_num	= intval($aprs['stocknum'] / 2);
					$surplusnum			= $aprs['stocknum'] - $issuer_stock_num;
					// 申请人获得半数股票，扣除资金；另一半股票待正式上市后结算
					DB::query("INSERT INTO ".DB::table('kfsm_customer')." (cid, username, sid, stocknum, buyprice, averageprice, buytime) VALUES ('$aprs[userid]', '$aprs[username]', '$newsid', $issuer_stock_num, '$aprs[stockprice]', '$aprs[stockprice]', '$_G[timestamp]')");
					DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital-{$aprs[stockprice]}*{$aprs[stocknum]} WHERE uid='$aprs[userid]'");
					loadcache('plugin');
					$db_issuedays = $_G['cache']['plugin']['stock_dzx']['issuedays'];
					$db_issuedays = is_numeric($db_issuedays) && $db_issuedays > 0 ? $db_issuedays : 3;
					$db_issuedays = $_G['timestamp'] + $db_issuedays * 86400;
					DB::query("UPDATE ".DB::table('kfsm_apply')." SET sid='$newsid', surplusnum='$surplusnum', issuetime='$db_issuedays', state='1' WHERE aid='{$aprs[aid]}'");
					DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username1, username2, descrip, timestamp, ip) VALUES('申请管理', '$aprs[username]', '{$_G[username]}', '$aprs[username] 的公司 $aprs[stockname] 上市申请批准操作成功', '$_G[timestamp]', '$_G[clientip]')");
					cpmsg('公司上市申请批准成功', $baseScript, 'succeed');
				}
				else
				{
					cpmsg('该股票状态异常，无法操作', '', 'error');
				}
			}
			else if ( $ops == 'deny' )
			{
				if ( $aprs['state'] == 1 )
					cpmsg('该公司已被批准上市，请勿重复提交', '', 'error');
				else if ( $aprs['state'] == 2 )
					cpmsg('该公司已被拒绝上市，请勿重复提交', '', 'error');
				else if ( $aprs['state'] == 0 )
				{
					DB::query("UPDATE ".DB::table('kfsm_user')." SET capital_ava=capital_ava+{$aprs[stockprice]}*{$aprs[stocknum]} WHERE uid='$aprs[userid]'");
					DB::query("UPDATE ".DB::table('kfsm_apply')." SET state='2' WHERE aid='$apply_id'");
					DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username1, username2, descrip, timestamp, ip) VALUES('申请管理', '$aprs[username]', '{$_G[username]}', '$aprs[username] 的公司 $aprs[stockname] 上市申请被拒绝', '$_G[timestamp]', '$_G[clientip]')");
					cpmsg('公司上市申请拒绝成功', $baseScript, 'succeed');
				}
				else
				{
					cpmsg('该股票状态异常，无法操作', '', 'error');
				}
			}
			else if ( $ops == 'del' )
			{
				if ( $aprs['state'] == 0 )
					cpmsg('该公司正在等待审核，不能删除', '', 'error');
				else if ( $aprs['state'] == 1 || $aprs['state'] == 2 )
				{
					DB::query("DELETE FROM ".DB::table('kfsm_apply')." WHERE aid='$apply_id'");
					DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username1, username2, descrip, timestamp, ip) VALUES('申请管理', '$aprs[username]', '{$_G[username]}', '$aprs[username] 的公司 $aprs[stockname] 上市申请被删除', '$_G[timestamp]', '$_G[clientip]')");
					cpmsg('公司上市申请删除成功', $baseScript, 'succeed');
				}
				else
				{
					cpmsg('该股票状态异常，无法操作', '', 'error');
				}
			}
			else
			{
				cpmsg('操作选项参数错误', '', 'error');
			}
		}
	}
}
?>
