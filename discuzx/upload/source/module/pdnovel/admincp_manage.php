<?php

shownav('pdnovel', 'pdnovel_manage');

if($do=='show'){
	if(!submitcheck('modsubmit')){
		$filter = $_G['gp_filter']?$_G['gp_filter']:'all';
		showsubmenu('pdnovel_manage',  array(array('pdnovel_manage_all', 'pdnovel&operation=manage&do=show&filter=all', $filter=='all'?1:0),array('pdnovel_manage_unfull', 'pdnovel&operation=manage&do=show&filter=unfull', $filter=='unfull'?1:0),array('pdnovel_manage_empty', 'pdnovel&operation=manage&do=show&filter=empty', $filter=='empty'?1:0),array('pdnovel_manage_search', 'pdnovel&operation=manage&do=show&filter=search', $filter=='search'?1:0)));
		
		showformheader("pdnovel&operation=manage&do=show&filter=search");
		showtableheader();
		echo '<tr class="hover"><td width="40px">书名：</td><td width="200px"><input type="text" class="txt" style="width:200px" value="" name="name"></td><td width="40px">作者：</td><td width="200px"><input type="text" class="txt" style="width:200px" value="" name="author"></td><td><input type="submit" value="搜索" name="srcsubmit" id="srcsubmit" class="btn"></td></tr>';
		showtablefooter();
		showformfooter();			
		
		showformheader("pdnovel&operation=manage&do=show", "onsubmit=\"javascript:if(confirm('".cplang('pdnovel_manage_confirmdele')."')) return true; else return false;\"");
		showtableheader('', '', ' style="min-width:910px; _width:910px;"');
		showsubtitle(array('', 'pdnovel_manage_name', 'pdnovel_manage_lastchapter', 'pdnovel_manage_author', 'pdnovel_manage_lastupdate', 'pdnovel_manage_full', 'pdnovel_manage_operation'));
		$perpage = 20;
		$page = $_G['gp_page']?$_G['gp_page']:1;
		if($filter=='all'){
			$sql = "";
		}elseif($filter=='unfull'){
			$sql = " AND full=0";
		}elseif($filter=='empty'){
			$sql = " AND chapters=0";
		}elseif($filter=='search'){
			$name = $_G['gp_name'];
			$author = $_G['gp_author'];
			if($name){
				$sql = " AND name LIKE '%{$name}%'";
			}elseif($author){
				$sql = " AND author LIKE '%{$author}%'";
			}
		}
		$limit_start = $perpage * ($page - 1);
		$query = DB::query("SELECT * FROM ".DB::table('pdnovel_view')." WHERE 1$sql ORDER BY lastupdate DESC LIMIT $limit_start, $perpage");
		while($novel = DB::fetch($query)) {
			$novel['lastupdate'] = strftime('%m-%d', $novel['lastupdate']);
			$novel['full'] = $novel['full']==1?cplang('pdnovel_collect_full_yes'):cplang('pdnovel_collect_full_no');
			echo '<tr class="hover"><td class="td25"><input class="checkbox" type="checkbox" name="nidarr[]" value="'.$novel['novelid'].'"/></td><td width="25%">'.$novel['name'].'</td><td width="25%">'.$novel['lastchapter'].'</td><td width="10%">'.$novel['author'].'</td><td width="10%">'.$novel['lastupdate'].'</td><td width="10%">'.$novel['full'].'</td><td width="20%"><a href="novel.php?mod=home&do=novel&ac=edit&novelid='.$novel['novelid'].'" target="_blank">编辑</a> <a href="novel.php?mod=home&do=novel&ac=del&novelid='.$novel['novelid'].'" target="_blank">删除</a> <a href="novel.php?mod=home&do=manage&novelid='.$novel['novelid'].'" target="_blank">章节</a></td></tr>';
		}
		echo '<tr class="hover"><td class="td25" colspan="7"><input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'nidarr\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label></td></tr>';
		$novelcount = DB::result_first("SELECT count(*) FROM ".DB::table('pdnovel_view')." WHERE 1$sql");
		$multi = multi($novelcount, $perpage, $page, ADMINSCRIPT."?action=pdnovel&operation=manage&do=show&filter=$filter");
		showsubmit('modsubmit', 'delete', '', '', $multi);
		showtablefooter();
		showformfooter();
	}else{
		$coverpath = 'data/attachment/pdnovel/cover/';
		$chapterpath = 'data/attachment/pdnovel/chapter/';
		foreach($_G['gp_nidarr'] as $novelid){
			$cover = DB::result_first("SELECT cover FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
			if($cover){
				@unlink($coverpath.$cover);
			}
			$query = DB::query("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid");
			while($chapter = DB::fetch($query)){
				@unlink($chapterpath.$chapter[chaptercontent]);
			}
			DB::query("DELETE FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
			DB::query("DELETE FROM ".DB::table('pdnovel_volume')." WHERE novelid=$novelid");
			DB::query("DELETE FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid");
		}
		cpmsg('pdnovel_manage_delesucceed', 'action=pdnovel&operation=manage&do=show', 'succeed');
	}
}

?>