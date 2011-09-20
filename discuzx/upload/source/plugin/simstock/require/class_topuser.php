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
class Topuser
{
	public function showTopUser()
	{
		global $baseScript, $_G, $db_smname, $db_marketpp, $hkimg, $page;
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfss_user'));
		if ( $cnt > 0 )
		{
			$readperpage = is_numeric($db_marketpp) && $db_marketpp > 0 ? $db_marketpp : 20;
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
			$pages = foxpage($page,$numofpage,"$baseScript&mod=system&act=topuser&");
			$topdb = $this->getTopUser($start, $readperpage);
		}
		$ranktime = DB::result_first("SELECT ranktime FROM ".DB::table('kfss_sminfo')." WHERE id=1");
		$ranktime = dgmdate($ranktime);
		include template('simstock:topuser');
	}
	private function getTopUser($start=0, $readperpage)
	{
		global $baseScript;
		$topdb = array();
		$query = DB::query("SELECT uid, username, fund_ini, profit, profit_d1, profit_d5, trade_times, trade_ok_times, rank FROM ".DB::table('kfss_user')." WHERE locked<>'1' ORDER BY profit DESC LIMIT $start,$readperpage");
		while ( $rs = DB::fetch($query) )
		{
			$rs['profit_ratio']		= number_format($rs['profit'],2);
			$rs['profit_d1_ratio']	= number_format($rs['profit_d1'],2);
			$rs['profit_d5_ratio']	= number_format($rs['profit_d5'],2);
			$rs['trade_ok_ratio']	= $rs['trade_ok_times']/$rs['trade_times']*100;
			$topdb[] = $rs;
		}
		return $topdb;
	}
}
?>
