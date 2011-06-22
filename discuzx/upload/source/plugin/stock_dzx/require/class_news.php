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
class News
{
	public function showNewsList()
	{
		global $baseScript, $_G, $db_smname, $db_marketpp, $page, $hkimg;
		$cnt = DB::result_first("SELECT COUNT(*) FROM ".DB::table('kfsm_news'));
		if ( $cnt > 0 )
		{
			$readperpage = is_numeric($db_marketpp) && $db_marketpp > 0 ? $db_marketpp : 20;
			$page = $_G['gp_page'];
			if ( $page < 1 )
			{
				$page = 1;
				$start = 0;
			}
			$numofpage = ceil($cnt/$readperpage);
			if( $page > $numofpage )
			{
				$page = $numofpage;
				$start-=1;
			}
			$start = ( $page - 1 ) * $readperpage;
			$pages = foxpage($page,$numofpage,"$baseScript&mod=news&act=shownewslist&");
			$newsdb = array();
			$query = DB::query("SELECT * FROM ".DB::table('kfsm_news')." ORDER BY addtime DESC LIMIT $start, $readperpage");
			$i = 0;
			while ( $news = DB::fetch($query) )
			{
				$i++;
				$news['i'] = $i;
				$news['addtime'] = dgmdate($news['addtime']);
				$newsdb[] = $news;
			}
			DB::free_result($query);
		}
		include template('stock_dzx:news');
	}
	public function showNewsInfo($id=0)
	{
		global $baseScript, $_G, $db_smname, $hkimg;
		require libfile('function/discuzcode');
		$news = DB::fetch_first("SELECT * FROM ".DB::table('kfsm_news')." WHERE nid='$id'");
		$news['content'] = discuzcode($news['content']);
		$news['addtime'] = dgmdate($news['addtime']);
		include template('stock_dzx:news_info');
	}
	public function getLatestNews($num=0)
	{
		global $baseScript;
		$newsList = array();
		$query = DB::query("SELECT * FROM ".DB::table('kfsm_news')." ORDER BY addtime DESC LIMIT 0,$num");
		while ( $rs = DB::fetch($query) )
		{
			$rs['addtime'] = dgmdate($rs['addtime']);
			$newsList[] = $rs;
		}
		return $newsList;
	}
}
?>
