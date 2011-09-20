<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-14
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Logs
{
	public function getLogList()
	{
		global $baseScript, $_G;
		$page = $_G['gp_page'];
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfss_exclog'));
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
			$start = ( $page - 1 ) * $readperpage;
			$pages = foxpage($page,$numofpage,"?$baseScript&mod=logs&");
			$logdb = array();
			$query = DB::query("SELECT * FROM ".DB::table('kfss_exclog')." ORDER BY logtime DESC LIMIT $start, $readperpage");
			while ( $rslog = DB::fetch($query) )
			{
				if ( $rslog['action'] == 1 )
				{
					$rslog['action'] = '委托买入';
				}
				else if ( $rslog['action'] == 2 )
				{
					$rslog['action'] = '委托卖出';
				}
				else if ( $rslog['action'] == 9 )
				{
					$rslog['action'] = "删除日志 {$rslog['amount']} 条";
					$rslog['stockcode'] = '-';
					$rslog['amount'] = '-';
					$rslog['price'] = '-';
				}
				$rslog['logtime'] = dgmdate($rslog['logtime']);
				$logdb[] = $rslog;
			}
		}
		return array($logdb, $cnt, $readperpage, $pages);
	}
	public function deleteLogs()
	{
		global $baseScript, $_G;
		$lid	= $_G['gp_lid'];
		$value	= $_G['gp_value'];
		$ttlnum = count($lid);
		if ( $ttlnum > 0 )
		{
			$delid = '';
			foreach( $lid as $value )
			{
				$delid .= $value.',';
			}
			$delid && $delid = substr($delid,0,-1);
			DB::query("DELETE FROM ".DB::table('kfss_exclog')." WHERE lid IN ($delid)");
			DB::query("INSERT INTO ".DB::table('kfss_exclog')." (action, uname, amount, logtime, ip) VALUES(9, '{$_G['username']}', '{$ttlnum}', '$_G[timestamp]', '$_G[clientip]')");
		}
		$baseScript .= '&mod=logs';
		cpmsg("已成功删除 {$ttlnum} 条系统日志！", $baseScript, 'succeed');
	}
}
?>
