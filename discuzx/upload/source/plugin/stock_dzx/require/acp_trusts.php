<?php
/*
 * Kilofox Services
 * StockIns v9.4
 * Plug-in for Discuz!
 * Last Updated: 2011-06-06
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Trusts
{
	public function getDealList()
	{
		global $db;
		$i = 0;
		$qd = DB::query("SELECT d.*, u.uid, u.username FROM kfsm_deal d LEFT JOIN kfsm_user u ON d.uid=u.uid ORDER BY d.did DESC");
		while ( $rsd = DB::fetch($qd) )
		{
			$i++;
			$rsd['no'] = $i;
			if ( $rsd['direction'] == 1 )
				$rsd['direction'] = '<span style="color:#FF0000">买入</span>';
			else if ( $rsd['direction'] == 2 )
				$rsd['direction'] = '<span style="color:#008000">卖出</span>';
			else
				$rsd['direction'] = '<span style="color:#0000FF">异常</span> <a href="http://www.kilofox.net" target="_blank">求助</a>';
			if ( $rsd['time_deal'] )
				$rsd['time_deal'] = dgmdate($rsd['time_deal'],'Y-m-j H:i:s');
			else
				$rsd['time_deal'] = '-';
			if ( $rsd['time_tran'] )
				$rsd['time_tran'] = dgmdate($rsd['time_tran'],'Y-m-j H:i:s');
			else
				$rsd['time_tran'] = '-';
			if ( $rsd['ok'] == 0 )
				$rsd['ok'] = '未成交';
			else if ( $rsd['ok'] == 1 )
				$rsd['ok'] = '<span style="color:#008000">成交</span>';
			else if ( $rsd['ok'] == 2 )
				$rsd['ok'] = '<span style="color:#FFA500">部分成交</span>';
			else if ( $rsd['ok'] == 3 )
				$rsd['ok'] = '<span style="color:#0000FF">用户撤销</span>';
			else if ( $rsd['ok'] == 4 )
				$rsd['ok'] = '<span style="color:#A52A2A">系统撤销</span>';
			else
				$rsd['ok'] = '<span style="color:#FF0000">异常</span> <a href="http://www.kilofox.net" target="_blank">求助</a>';
			$ddb[] = $rsd;
		}
		return $ddb;
	}
	public function getTranList()
	{
		global $db;
		$i = 0;
		$qt = DB::query("SELECT t.*, u.uid, u.username FROM kfsm_transaction t LEFT JOIN kfsm_user u ON t.uid=u.uid ORDER BY t.tid DESC");
		while ( $rst = DB::fetch($qt) )
		{
			$i++;
			$rst['no'] = $i;
			if ( $rst['direction'] == 1 )
				$rst['direction'] = '<span style="color:#FF0000">买入</span>';
			else if ( $rst['direction'] == 2 )
				$rst['direction'] = '<span style="color:#008000">卖出</span>';
			else
				$rst['direction'] = '<span style="color:#0000FF">异常</span> <a href="http://www.kilofox.net" target="_blank">求助</a>';
			if ( $rst['ttime'] )
				$rst['ttime'] = dgmdate($rst['ttime'],'Y-m-j H:i:s');
			else
				$rst['ttime'] = '-';
			$tdb[] = $rst;
		}
		return $tdb;
	}
	public function deleteDeals()
	{
		global $baseScript, $_G;
		$did	= $_G['gp_did'];
		$value	= $_G['gp_value'];
		$ttlnum = count($did);
		if ( $ttlnum > 0 )
		{
			$delid = '';
			foreach( $did as $value )
			{
				$delid .= $value.',';
			}
			$delid && $delid = substr($delid,0,-1);
			DB::query("DELETE FROM kfsm_deal WHERE did IN ($delid)");
			DB::query("INSERT INTO kfsm_smlog (type, username2, descrip, timestamp, ip) VALUES('委托记录管理', '{$_G[username]}', '删除委托记录 {$ttlnum} 条', '$_G[timestamp]', '$_G[clientip]')");
		}
		$baseScript .= '&mod=trusts';
		cpmsg("已成功删除 {$ttlnum} 条委托记录！", $baseScript, 'succeed');
	}
	public function deleteTrans()
	{
		global $baseScript, $_G;
		$tid	= $_G['gp_tid'];
		$value	= $_G['gp_value'];
		$ttlnum = count($tid);
		if ( $ttlnum > 0 )
		{
			$delid = '';
			foreach( $tid as $value )
			{
				$delid .= $value.',';
			}
			$delid && $delid = substr($delid,0,-1);
			DB::query("DELETE FROM kfsm_transaction WHERE tid IN ($delid)");
			DB::query("INSERT INTO kfsm_smlog (type, username2, descrip, timestamp, ip) VALUES('成交记录管理', '{$_G[username]}', '删除成交记录 {$ttlnum} 条', '$_G[timestamp]', '$_G[clientip]')");
		}
		$baseScript .= '&mod=trusts';
		cpmsg("已成功删除 {$ttlnum} 条成交记录！", $baseScript, 'succeed');
	}
}
?>
