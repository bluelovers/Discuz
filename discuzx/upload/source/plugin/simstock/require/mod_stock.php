<?php
/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-08-10
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
class Stock
{
	public function __construct( $action )
	{
		$this->processAction( $action );
	}
	private function processAction( $action )
	{
		global $kfsclass, $_G;
		$actArray = array('call', 'showinfo', 'search');
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
			case 'showinfo':
				$this->showStockInfo($_G['gp_code']);
			break;
			case 'search':
				$this->searchStock($_G['gp_code']);
			case 'call':
				continue;
			break;
		}
	}
	private function showStockInfo( $code )
	{
		global $baseScript, $hkimg, $_G, $db_smname, $db_klcolor, $db_otherpp;
		if ( $code )
		{
			$cData = DB::fetch_first("SELECT COUNT(*) AS usernum , SUM(stocknum_ava) AS stocknum FROM ".DB::table('kfss_customer')." WHERE code='$code'");
			$cnt = $cData['usernum'];
			if ( $cnt > 0 )
			{
				$readperpage = is_numeric($db_otherpp) && $db_otherpp > 0 ? $db_otherpp : 20;
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
				$pages = foxpage($page,$numofpage,"$baseScript&mod=stock&act=showinfo&code=$code&");
				$rsusdb = $this->getStockholdersList($code, $cData['stocknum'], $start, $readperpage);
			}
			include template('simstock:stock_showinfo');
		}
		else
		{
			showmessage('该股票不存在');
		}
	}
	// 公共方法，分红用到
	public function getStockholdersList($code, $totalnum, $start, $readperpage)
	{
		$shldb = array();
		if ( $code )
		{
			$query = DB::query("SELECT uid, username, stocknum_ava, averageprice FROM ".DB::table('kfss_customer')." WHERE code='$code' ORDER BY stocknum DESC LIMIT $start, $readperpage");
			while ( $rs = DB::fetch($query) )
			{
				$rs['totalnum'] = $totalnum;
				$shldb[] = $rs;
			}
		}
		return $shldb;
	}
	private function searchStock()
	{
		global $_G, $baseScript;
		$keyword = $_G['gp_keyword'];
		if ( empty($keyword) )
		{
			showmessage('请输入股票代码或者股票名称关键字');
		}
		else
		{
			if ( is_numeric($keyword) )
				$sql = "code='$keyword'";
			else
				$sql = "stockname LIKE '%{$keyword}%'";
			$rs = DB::fetch_first("SELECT code FROM ".DB::table('kfss_stock')." WHERE $sql");
			if ( !$rs )
				showmessage("没有找到指定的股票，可能该上市公司已经倒闭");
			else
				header("Location:$baseScript&mod=stock&act=showinfo&code=$rs[code]");
		}
	}
}
?>
