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
class Topuser
{
	public function showTopUser()
	{
		global $baseScript, $_G, $db_smname, $db_marketpp, $hkimg, $page;
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_user'));
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
		include template('stock_dzx:topuser');
	}
	private function getTopUser($start, $readperpage)
	{
		global $baseScript;
		$topdb = array();
		$query = DB::query("SELECT uid, username, asset, stocknum, stockcost, stockvalue, stocksort, todaybuy, todaysell, regtime FROM ".DB::table('kfsm_user')." WHERE locked<>'1' ORDER BY asset DESC LIMIT $start,$readperpage");
		$i = 0;
		while ( $rs = DB::fetch($query) )
		{
			$i++;
			$rs['i'] = $i;
			$rs['username'] = "<a href=\"$baseScript&mod=member&act=showinfo&uid={$rs[uid]}\">$rs[username]</a>";
			if ( $rs['asset'] <= 0 )
				$rs['asset'] = 0.00;
			else
				$rs['asset'] = number_format($rs['asset'],2);
			if ( $rs['stockcost'] < $rs['stockvalue'] )
				$rs['color'] = '#FF0000';
			else if ( $rs['stockcost'] > $rs['stockvalue'] )
				$rs['color'] = '#008000';
			else
				$rs['color'] = '';
			$rs['regtime'] = dgmdate($rs['regtime']);
			$topdb[] = $rs;
		}
		return $topdb;
	}
}
?>
