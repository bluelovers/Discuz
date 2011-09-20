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
class Stocks
{
	public function getStockList()
	{
		global $baseScript, $_G;
		$stname		= $_G['gp_stname'];
		$search		= $_G['gp_search'];
		$stnamechk	= $_G['gp_stnamechk'];
		$page		= $_G['gp_page'];
		if ( $search == '1' )
		{
			if ( $stname == '' )
			{
				$baseScript .= '&mod=stockset';
				cpmsg('请输入您要查找的股票名称', $baseScript, 'error');
			}
			else
			{
				$sql = "$stname";
			}
			if ( $stnamechk == '1' )
				$sql = "WHERE stockname='$stname'";
			else
				$sql = "WHERE stockname LIKE '%$stname%'";
		}
		else
		{
			$sql = '';
		}
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_stock')." $sql");
		$readperpage = 30;
		if ( $cnt > 0 )
		{
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
			$pages = foxpage($page, $numofpage, "?$baseScript&mod=stockset&");
			if ( $page > $numofpage )
			{
				$page = $numofpage;
				$start-=1;
			}
			$start = ( $page - 1 ) * $readperpage;
			$stockdb = array();
			$query = DB::query("SELECT * FROM ".DB::table('kfsm_stock')." $sql LIMIT $start,$readperpage");
			while ( $rs = DB::fetch($query) )
			{
				$rs['openprice']	= number_format($rs['openprice'],2);
				$rs['currprice']	= number_format($rs['currprice'],2);
				if ( $rs['state'] == 0 )
					$rs['state'] = '正常';
				else if ( $rs['state'] == 1 )
					$rs['state'] = '<font color="#808080">停牌</font>';
				else if ( $rs['state'] == 2 )
					$rs['state'] = '<font color="#FF0000">涨停</font>';
				else if ( $rs['state'] == 3 )
					$rs['state'] = '<font color="#008000">跌停</font>';
				else if ( $rs['state'] == 4 )
					$rs['state'] = '<font color="#0000FF">新股</font>';
				$stockdb[] = $rs;
			}
		}
		return array($stockdb,$cnt,$readperpage,$pages);
	}
	public function getStockInfo($sid)
	{
		global $_G;
		$rs = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_stock')." WHERE sid='$sid'");
		if ( !$rs )
		{
			cpmsg('没有找到指定的上市公司', '', 'error');
		}
		else
		{
			$rs['openprice'] = number_format($rs['openprice'],2);
			$rs['currprice'] = number_format($rs['currprice'],2);
			$rs['highprice'] = number_format($rs['highprice'],2);
			$rs['lowprice'] = number_format($rs['lowprice'],2);
			$ifselected = false;
			$capath = opendir('source/plugin/stock/image/ca/');
			while ( $cafile = readdir($capath) )
			{
				if ( preg_match('/\.(jpg)$/i',$cafile) )
				{
					if ( $cafile == $rs['comphoto'] )
					{
						$ifselected = true;
						$cas .= "<option value=\"$cafile\" selected>$cafile</option>";
					}
					else
					{
						$caselected = (!$ifselected && $cafile=='0.jpg') ? ' selected' : '';
						$cas .= "<option value=\"$cafile\"$caselected>$cafile</option>";
					}
				}
			}
			closedir($capath);
			loadcache('plugin');
			$db_introducemax	= $_G['cache']['plugin']['stock']['introducemax'];
			return array($rs,$cas,$db_introducemax);
		}
	}
	public function updateStock()
	{
		global $baseScript, $_G;
		$sid			= $_G['gp_sid'];
		$stname			= $_G['gp_stname'];
		$stnameold		= $_G['gp_stnameold'];
		$openprice		= $_G['gp_openprice'];
		$currprice		= $_G['gp_currprice'];
		$lowprice		= $_G['gp_lowprice'];
		$highprice		= $_G['gp_highprice'];
		$issuenum		= $_G['gp_issuenum'];
		$todaybuynum	= $_G['gp_todaybuynum'];
		$todaysellnum	= $_G['gp_todaysellnum'];
		$todaytradenum	= $_G['gp_todaytradenum'];
		$comphoto		= $_G['gp_comphoto'];
		$comintro		= $_G['gp_comintro'];
		$locked			= $_G['gp_locked'];
		$baseScript .= "&mod=stockset&section=editstock&sid=$sid";
		if ( $openprice == '' || !is_numeric($openprice) )
			cpmsg('收盘价格必须输入数字', '', 'error');
		if ( $currprice == '' || !is_numeric($currprice) )
			cpmsg('当前价格必须输入数字', '', 'error');
		if ( $lowprice == '' || !is_numeric($lowprice) )
			cpmsg('最低价格必须输入数字', '', 'error');
		if ( $highprice == '' || !is_numeric($highprice) )
			cpmsg('最高价格必须输入数字', '', 'error');
		if ( $issuenum == '' || !is_numeric($issuenum) )
			cpmsg('发行数量必须输入数值', '', 'error');
		if ( $todaybuynum == '' || !is_numeric($todaybuynum) )
			cpmsg('今日买入必须输入数值', '', 'error');
		if ( $todaysellnum == '' || !is_numeric($todaysellnum) )
			cpmsg('今日卖出必须输入数值', '', 'error');
		if ( $todaytradenum == '' || !is_numeric($todaytradenum) )
			cpmsg('今日成交必须输入数值', '', 'error');
		loadcache('plugin');
		$db_esnamemin		= $_G['cache']['plugin']['stock']['esnamemin'];
		$db_esnamemax		= $_G['cache']['plugin']['stock']['esnamemax'];
		$db_introducemax 	= $_G['cache']['plugin']['stock']['introducemax'];
		$rs = DB::result_first("SELECT * FROM ".DB::table('kfsm_stock')." WHERE sid='$sid'");
		if ( !$rs )
			cpmsg('没有找到指定的上市公司', '', 'error');
		if ( $stname == '' || strlen($stname) < $db_esnamemin || strlen($stname) > $db_esnamemax )
			cpmsg("股票名称长度不能小于 {$db_esnamemin} 字节或者大于 {$db_esnamemax} 字节", '', 'error');
		if ( $comphoto == '' )
			cpmsg('公司照片不能为空', '', 'error');
		if ( $comintro == '' || strlen($comintro) > $db_introducemax )
			cpmsg("公司简介不能为空或超过 $db_introducemax 字节", '', 'error');
		if ( $locked <> '0' && $locked <> '1' )
			cpmsg('股票状态错误', '', 'error');
		if ( $stname<>$stnameold )
		{
			$baseScript .= "&mod=stockset&section=editstock&sid=$sid";
			$rs = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_stock')." WHERE stockname='$stname'");
			$rs && cpmsg('股票名称已被其它股票使用', $baseScript, 'error');
			$rs = DB::result_first("SELECT stockname FROM ".DB::table('kfsm_apply')." WHERE stockname='$stname' AND state<>2");
			$rs && cpmsg('股票名称已被其它股票使用', $baseScript, 'error');
		}
		$openprice	= str_replace(',', '', $openprice);
		$currprice	= str_replace(',', '', $currprice);
		DB::query("UPDATE ".DB::table('kfsm_stock')." SET stockname='$stname', issuenum='$issuenum', openprice='$openprice', currprice='$currprice', lowprice='$lowprice', highprice='$highprice', todaybuynum='$todaybuynum', todaysellnum='$todaysellnum', todaytradenum='$todaytradenum', comphoto='$comphoto', comintro='$comintro', state='$locked' WHERE sid='$sid'");
		$logContent = "编辑股票（{$stnameold}）信息";
		if ( $stname <> $stnameold )
		{
			$logContent .= "，名称修改为 $stname";
		}
		DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('股票管理', '{$_G[username]}', '$logContent', '$_G[timestamp]', '$_G[clientip]')");
		cpmsg('股票信息修改完毕', $baseScript, 'succeed');
	}
	public function deleteStock($sid)
	{
		$rs = DB::fetch_first("SELECT sid,stockname FROM ".DB::table('kfsm_stock')." WHERE sid='$sid'");
		if ( !$rs )
			cpmsg('没有找到指定的股票记录', '' ,'error');
		else
			return $rs;
	}
	public function exeDeleteStock()
	{
		global $baseScript, $_G;
		$sid		= $_G['gp_sid'];
		$logtxt		= $_G['gp_logtxt'];
		$newstxt	= $_G['gp_newstxt'];
		$capital	= $_G['gp_capital'];
		$use		= $_G['gp_use'];
		$stockName	= DB::query("SELECT stockname FROM ".DB::table('kfsm_stock')." WHERE sid='$sid'");
		$baseScript .= "&mod=stockset&section=delstock&sid=$sid";
		if ( empty($stockName) )
		{
			cpmsg('未找到您要删除的股票', '', 'error');
		}
		else
		{
			if ( empty($logtxt) || strlen($logtxt) > 250 )
			{
				cpmsg('操作理由不能为空，且长度不能超过 250 字节！', $baseScript, 'error');
			}
			else
			{
				if ( $capital == 1 )
					$delstock = '（抛出股票）';
				else
					$delstock = '（注销股票）';
				$logtxt = "删除股票 {$stockName}，" . $logtxt.$delstock;
			}
			if ( $use == '1' && ( empty($newstxt) || strlen($newstxt) > 250 ) )
				cpmsg('您选择了使用信息发布功能，请填写信息内容，其长度控制在 250 字节以内', $baseScript, 'error');
			if ( $capital == '1' )
			{
				$query = DB::query("SELECT cid, stocknum_ava FROM ".DB::table('kfsm_customer')." WHERE sid='$sid'");
				while ( $rc = DB::fetch($query) )
				{
					$mystnum		= 0;
					$mystcost		= 0;
					$mystvalue		= 0;
					$totalfund		= 0;
					$query = DB::query("SELECT c.*, s.currprice FROM ".DB::table('kfsm_customer')." c INNER JOIN ".DB::table('kfsm_stock')." s ON c.sid=s.sid WHERE c.cid={$rc[cid]} AND c.sid<>'$sid'");
					while ( $rsst = DB::fetch($query) )
					{
						$mystnum	= $mystnum + $rsst['stocknum_ava'];
						$mystcost	= $mystcost + $rsst['stocknum_ava'] * $rsst['averageprice'];
						$mystvalue	= $mystvalue + $rsst['stocknum_ava'] * $rsst['currprice'];
						$totalfund	= $totalfund + $rsst['stocknum_ava'] * $rsst['currprice'];
					}
					$addmoney = round($rc['stocknum_ava']*$rs['currprice'],3);
					DB::query("UPDATE ".DB::table('kfsm_user')." SET capital=capital+'$addmoney', asset=capital+".round($totalfund,3).", stocksort=stocksort-1, stocknum='$mystnum', stockcost='$mystcost', stockvalue='$mystvalue' WHERE uid='{$rc['cid']}'");
				}
			}
			DB::query("DELETE FROM ".DB::table('kfsm_customer')." WHERE sid='$sid'");
			DB::query("DELETE FROM ".DB::table('kfsm_stock')." WHERE sid='$sid'");
			if ( $use == '1' )
				DB::query("INSERT INTO ".DB::table('kfsm_news')." (content,color,addtime) VALUES('$newstxt','#FF0000','$_G[timestamp]')");
			DB::query("INSERT INTO ".DB::table('kfsm_smlog')." (type, username2, descrip, timestamp, ip) VALUES('股票管理','{$_G[username]}','$logtxt','$_G[timestamp]','$_G[clientip]')");
			$kfsclass = new kfsclass;
			$kfsclass->resetcid();
			$baseScript .= '&mod=stockset';
			cpmsg('股票删除成功', $baseScript, 'succeed');
		}
	}
	public function resetcid()
	{
		$query = DB::query("SELECT sid FROM ".DB::table('kfsm_stock')." ORDER BY sid");
		$cid = 1;
		while ( $rs = DB::fetch($query) )
		{
			DB::query("UPDATE ".DB::table('kfsm_stock')." SET cid='$cid' WHERE sid='$rs[sid]'");
			$cid++;
		}
	}
}
?>
