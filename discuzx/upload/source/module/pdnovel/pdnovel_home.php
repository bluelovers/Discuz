<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']){
	showmessage($lang['nologin']);
}

loadcache('pdnovelcategory');
$ncc = $_G['cache']['pdnovelcategory'];

$do = $_G['gp_do'] ? $_G['gp_do'] : 'mark';
$ac = $_G['gp_ac'];

$page = $_G['gp_page'] ? $_G['gp_page'] : 1;
$perpage = 20;
$limit_start = $perpage * ($page - 1);
$navtitle = '小说中心 - '.$navtitle;

$coverpath = 'data/attachment/pdnovel/cover/';
$chapterpath = 'data/attachment/pdnovel/chapter/';

if($do == 'mark'){
	
	if($ac == 'bdel'){
		foreach($_G['gp_nidarr'] as $novelid){
			DB::query("DELETE FROM ".DB::table('pdnovel_mark')." WHERE uid=$_G[uid] AND novelid=$novelid;");
		}
		showmessage('do_success', dreferer());
	}else{
		$query = DB::query("SELECT m.*, v.catid, v.name, v.lastchapter, v.lastchapterid, v.authorid, v.author, v.full, v.lastupdate FROM ".DB::table('pdnovel_mark')." m LEFT JOIN ".DB::table('pdnovel_view')." v ON v.novelid=m.novelid WHERE m.uid=$_G[uid] ORDER BY m.dateline DESC LIMIT $limit_start,$perpage");
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pdnovel_mark')." WHERE uid=$_G[uid]");
		$mpurl = "novel.php?mod=home&do=mark";
		$multi =  multi($num, $perpage, $page, $mpurl);
		$marklist = array();
		while ($mark = DB::fetch($query)){
			$mark['lastupdate'] = strftime("%Y-%m-%d",$mark['lastupdate']);
			$mark['catname'] = $ncc[$novel['catid']]['catname'];
			$mark['upid'] = $ncc[$mark['catid']]['upid'];
			$mark['upname'] = $ncc[$mark['upid']]['catname'];
			$mark['full'] = $mark['full']==1?$lang['full']:$lang['nofull'];
			$marklist[] = $mark;
		}
	}
	$navtitle = '我的书架 - '.$navtitle;
	include template('diy:pdnovel/home_mark');

}elseif($do == 'manage'){

	$novelid = $_G['gp_novelid'];
	if(!$novelid){
		$query = DB::query("SELECT * FROM ".DB::table('pdnovel_view')." WHERE posterid = $_G[uid] ORDER BY lastupdate DESC LIMIT $limit_start,$perpage");
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pdnovel_view')." WHERE posterid=$_G[uid]");
		$mpurl = "novel.php?mod=home&do=manage";
		$managelist = array();
		while ($manage = DB::fetch($query)){
			$manage['lastupdate'] = strftime("%Y-%m-%d %H:%M",$manage['lastupdate']);
			$manage['catname'] = $ncc[$manage['catid']]['catname'];
			$manage['upid'] = $ncc[$manage['catid']]['upid'];
			$manage['upname'] = $ncc[$manage['upid']]['catname'];
			$manage['author'] = cutstr($manage['author'], 10);
			$manage['full'] = $manage['full']==1?$lang['full']:$lang['nofull'];
			$managelist[] = $manage;
		}
		$multi =  multi($num, $perpage, $page, $mpurl);
	}else{
		$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
		if(!$novel){
			showmessage('小说ID出错');
		}
		$volumechapter = "";
		$volumenum = 0;
		$vquery = DB::query("SELECT * FROM ".DB::table('pdnovel_volume')." WHERE novelid=$novelid ORDER BY volumeid ASC");
		while ($volume = DB::fetch($vquery)){
			$volumenum++;
			$volumechapter .= '<div class="contenttitle" id="volumeid'.$volume[volumeid].'"><h2 class="left"><span>'.$volumenum.'</span>《'.$novel[name].'》'.$volume[volumename].'</h2><h3 class="right"><a href="novel.php?mod=home&do=volume&ac=edit&novelid='.$novelid.'&volumeid='.$volume[volumeid].'">编辑</a> <a href="novel.php?mod=home&do=volume&ac=del&novelid='.$novelid.'&volumeid='.$volume[volumeid].'">删除</a></h3></div><div class="contentlist"><ul>';
			$cquery = DB::query("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid AND volumeid=$volume[volumeid] ORDER BY chapterid ASC");
			while ($chapter = DB::fetch($cquery)) {
				$chapter['chaptername'] = cutstr($chapter['chaptername'], 20, '');
				$chapter['lastupdate']=strftime ("%Y-%m-%d %X",$chapter['lastupdate']);
				$volumechapter .= '<li style="width:25%;" id="chapterid'.$chapter[chapterid].'"><span class="left"><a href="novel.php?mod=read&novelid='.$novelid.'&chapterid='.$chapter[chapterid].'" target="_blank">'.$chapter[chaptername].'</a></span><span class="right"><a href="novel.php?mod=home&do=chapter&ac=edit&volumeid='.$volume[volumeid].'&chapterid='.$chapter[chapterid].'">编辑</a> <a href="novel.php?mod=home&do=chapter&ac=del&volumeid='.$volume[volumeid].'&chapterid='.$chapter[chapterid].'">删除</a></span></li>';
			}
			$volumechapter .= '<li style="width:25%;"><a href="novel.php?mod=home&do=chapter&ac=add&volumeid='.$volume[volumeid].'" class="add">增加章节</a></li></ul></div>';
		}
	}
	$navtitle = '管理小说 - '.$navtitle;
	include template('diy:pdnovel/home_manage');

}elseif($do == 'comment'){

	if($ac == 'bdel'){
		if(!checkperm('pdnovelcommentmanage')) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}
		foreach($_G['gp_cidarr'] as $cid){
			DB::query("DELETE FROM ".DB::table('pdnovel_comment')." WHERE cid='$cid'");
		}
		showmessage('do_success', dreferer());
	}elseif($ac == 'del'){
		if(!checkperm('pdnovelcommentmanage')) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}
		$cid = $_G['gp_cid'];
		$comment = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_comment')." WHERE cid=$cid");
		if(!$comment){
			showmessage('评论ID出错');
		}
		if(submitcheck('postsubmit')){
			DB::query("DELETE FROM ".DB::table('pdnovel_comment')." WHERE cid='$cid'");
			showmessage('do_success', dreferer());
		}
		include template('pdnovel/comment_del');
	}elseif($ac == 'edit'){
		if(!checkperm('pdnovelcommentmanage') && $_G['uid'] != $comment['uid']) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}
		$cid = $_G['gp_cid'];
		$comment = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_comment')." WHERE cid=$cid");
		if(!$comment){
			showmessage('评论ID出错');
		}
		if(submitcheck('postsubmit')) {
			$message = getstr($_POST['message'], 0, 1, 1, 2);
			if(strlen($message) < 2) showmessage('content_is_too_short');
			$message = censor($message);
			if(censormod($message)) {
				$comment_status = 1;
			} else {
				$comment_status = 0;
			}
			DB::update('pdnovel_comment', array('message' => $message, 'status' => $comment_status), array('cid' => $comment['cid']));
			showmessage('do_success', dreferer());
		}
		include_once libfile('class/bbcode');
		$bbcode = & bbcode::instance();
		$comment['message'] = $bbcode->html2bbcode($comment['message']);
		include template('pdnovel/comment_edit');
	}else{
		$query = DB::query("SELECT c.*, v.name FROM ".DB::table('pdnovel_comment')." c LEFT JOIN ".DB::table('pdnovel_view')." v ON v.novelid=c.novelid WHERE c.uid=$_G[uid] ORDER BY c.dateline DESC LIMIT $limit_start,$perpage");
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pdnovel_comment')." WHERE uid=$_G[uid]");
		$mpurl = "novel.php?mod=home&do=comment";
		$commentlist = array();
		while ($comment = DB::fetch($query)){
			$comment['message'] = cutstr(strip_tags($comment['message']), 70);
			$comment['dateline'] = strftime("%Y-%m-%d %H:%M",$comment['dateline']);
			$commentlist[] = $comment;
		}
		$multi =  multi($num, $perpage, $page, $mpurl);
		$navtitle = '我的书评 - '.$navtitle;
		include template('diy:pdnovel/home_comment');
	}

}elseif($do == 'topic'){

	include template('diy:pdnovel/home_topic');

}elseif($do == 'novel'){

	if($ac == 'add'){
		if(!checkperm('pdnovelpost')) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}
		if(!submitcheck('postsubmit')){
			$query1 = DB::query("SELECT catid,upid,catname FROM ".DB::table('pdnovel_category')." WHERE upid=0 ORDER BY displayorder ASC");
			$selectcat = '<option value="">'.$lang['post_cat'].'</option>';
			while($upcat = DB::fetch($query1)){
				$selectcat .= '<option value="'.$upcat['catid'].'" disabled="disabled">&nbsp;'.$upcat['catname'].'</option>';
				$query2 = DB::query("SELECT catid,upid,catname FROM ".DB::table('pdnovel_category')." WHERE upid=$upcat[catid] ORDER BY displayorder ASC");
				while($cat = DB::fetch($query2)){
					$selectcat .= '<option value="'.$cat['catid'].'">&nbsp;&nbsp;&gt;'.$cat['catname'].'</option>';
				}
			}
		}else{
			if($_G['gp_type']){
				$author = addslashes($_G['gp_author']);
				$authorid = DB::result_first("SELECT authorid FROM ".DB::table('pdnovel_author')." WHERE author='$author';");
				if(!$authorid){
					DB::insert('pdnovel_author', array('author' => $author));
					$authorid = DB::insert_id();
				}
			}else{
				$author = $_G['username'];
				$authorid = $_G['uid'];
			}
			$novel_data = array(
				'catid' => $_G['gp_catid'],
				'name' => addslashes($_G['gp_name']),
				'initial' => get_initial($_G['gp_name']),
				'postdate' => $_G['timestamp'],
				'lastupdate' => $_G['timestamp'],
				'keyword' => addslashes($_G['gp_keyword']),
				'author' => $author,
				'authorid' => $authorid,
				'poster' => $_G['username'],
				'posterid' => $_G['uid'],
				'admin' => $_G['username'],
				'adminid' => $_G['uid'],
				'cover' => $_G['gp_cover'],
				'full' => $_G['gp_full'],
				'permission' => $_G['gp_permission'],
				'first' => $_G['gp_first'],
				'vip' => $_G['gp_vip'],
				'intro' => addslashes($_G['gp_intro']),
				'type' => $_G['gp_type'],
			);
			DB::insert('pdnovel_view', $novel_data);
			$novelid = DB::insert_id();
			$subnovelid = floor($novelid/1000);
			if (!file_exists($coverpath.$subnovelid)){
				@mkdir($coverpath.$subnovelid);
			}
			if($_G['gp_cover']){
				$_G['gp_oldcover'] = $subnovelid.'/'.$novelid.'-'.rand(100,999).'.jpg';
				@rename($_G['gp_cover'], $coverpath.$_G['gp_oldcover']);
				DB::update('pdnovel_view', array('cover' => $_G['gp_oldcover']), "novelid=$novelid");
			}
			updatecreditbyaction('pdnovelpost', $_G['uid']);
			showmessage('do_success', "novel.php?mod=home&do=manage&novelid=$novelid");
		}	
	}elseif($ac == 'edit'){
		$novelid = $_G['gp_novelid'];
		$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
		if(!$novel){
			showmessage('小说ID出错');
		}
		if(!checkperm('pdnovelmanage') && $_G['uid'] != $novel['posterid']) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}
		if(!submitcheck('postsubmit')){
			$query1 = DB::query("SELECT catid,upid,catname FROM ".DB::table('pdnovel_category')." WHERE upid=0 ORDER BY displayorder ASC");
			$selectcat = '<option value="">'.$lang['post_cat'].'</option>';
			while($upcat = DB::fetch($query1)){
				$selectcat .= '<option value="'.$upcat['catid'].'" disabled="disabled">&nbsp;'.$upcat['catname'].'</option>';
				$query2 = DB::query("SELECT catid,upid,catname FROM ".DB::table('pdnovel_category')." WHERE upid=$upcat[catid] ORDER BY displayorder ASC");
				while($cat = DB::fetch($query2)){
					$selected = $novel['catid'] == $cat['catid'] ? ' selected="selected"' : '';
					$selectcat .= '<option value="'.$cat['catid'].'"'.$selected.'>&nbsp;&nbsp;&gt;'.$cat['catname'].'</option>';
				}
			}
		}else{
			if($_G['gp_type']){
				$author = addslashes($_G['gp_author']);
				$authorid = DB::result_first("SELECT authorid FROM ".DB::table('pdnovel_author')." WHERE author='$author';");
				if(!$authorid){
					DB::insert('pdnovel_author', array('author' => $author));
					$authorid = DB::insert_id();
				}
			}else{
				$author = $_G['username'];
				$authorid = $_G['uid'];
			}
			if($_G['gp_cover'] && $_G['gp_oldcover']){
				if($_G['gp_cover'] != $_G['gp_oldcover']){
					@unlink($coverpath.$_G['gp_oldcover']);
					@rename($_G['gp_cover'], $coverpath.$_G['gp_oldcover']);
				}
				$_G['gp_cover'] = $_G['gp_oldcover'];
			}elseif($_G['gp_cover'] && !$_G['gp_oldcover']){
				$subnovelid = floor($novelid/1000);
				$_G['gp_oldcover'] = $subnovelid.'/'.$novelid.'-'.rand(100,999).'.jpg';
				if (!file_exists($coverpath.$subnovelid)){
					@mkdir($coverpath.$subnovelid);
				}
				@rename($_G['gp_cover'], $coverpath.$_G['gp_oldcover']);	
				$_G['gp_cover'] = $_G['gp_oldcover'];		
			}elseif(!$_G['gp_cover'] && $_G['gp_oldcover']){
				@unlink($coverpath.$_G['gp_oldcover']);
			}
			$updatearr = array(
				'catid' => $_G['gp_catid'],
				'name' => addslashes($_G['gp_name']),
				'initial' => get_initial($_G['gp_name']),
				'keyword' => addslashes($_G['gp_keyword']),
				'author' => $author,
				'authorid' => $authorid,
				'poster' => $_G['username'],
				'posterid' => $_G['uid'],
				'admin' => $_G['username'],
				'adminid' => $_G['uid'],
				'cover' => $_G['gp_cover'],
				'full' => $_G['gp_full'],
				'permission' => $_G['gp_permission'],
				'first' => $_G['gp_first'],
				'vip' => $_G['gp_vip'],
				'intro' => addslashes($_G['gp_intro']),
				'type' => $_G['gp_type'],
			);
			DB::update('pdnovel_view', $updatearr, "novelid=$novelid");
			showmessage('do_success', "novel.php?mod=home&do=manage&novelid=$novelid");
		}
	}elseif($ac == 'del'){
		$novelid = $_G['gp_novelid'];
		$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
		if(!$novel){
			showmessage('小说ID出错');
		}
		if(!checkperm('pdnovelmanage')) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}
		if(submitcheck('postsubmit')){
			DB::query("DELETE FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
			DB::query("DELETE FROM ".DB::table('pdnovel_volume')." WHERE novelid=$novelid");
			if($novel[cover]){
				@unlink($coverpath.$novel[cover]);
			}
			$query = DB::query("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid");
			while($chapter = DB::fetch($query)){
				@unlink($chapterpath.$chapter[chaptercontent]);
			}
			showmessage('do_success', "novel.php?mod=home&do=manage");
		}
	}elseif($ac == 'bdel'){
		if(!checkperm('pdnovelmanage')) {
			showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
		}
		foreach($_G['gp_nidarr'] as $novelid){
			DB::query("DELETE FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
			DB::query("DELETE FROM ".DB::table('pdnovel_volume')." WHERE novelid=$novelid");
			if($novel[cover]){
				@unlink($coverpath.$novel[cover]);
			}
			$query = DB::query("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid");
			while($chapter = DB::fetch($query)){
				@unlink($chapterpath.$chapter[chaptercontent]);
			}
		}
		showmessage('do_success', "novel.php?mod=home&do=manage");
	}
	include template('diy:pdnovel/home_novel');
	
}elseif($do == 'volume'){

	$novelid = $_G['gp_novelid'];
	$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
	if(!$novel){
		showmessage('小说ID出错');
	}
	if($ac == 'add'){
		if(submitcheck('postsubmit')){
			$volumename = addslashes(cutstr($_G['gp_volumename'], 50));
			$order = DB::result_first("SELECT volumeorder FROM ".DB::table('pdnovel_volume')." WHERE novelid=$novelid ORDER BY volumeorder DESC LIMIT 1");
			$order =  $order ? $order : 0;
			$volumeorder = $order + 1;
			$insertarr = array(
				'novelid' => $novelid,
				'volumename' => $volumename,
				'volumeorder' => $volumeorder
			);
			DB::insert('pdnovel_volume', $insertarr);
			$volumeid = DB::insert_id();
			DB::query("UPDATE ".DB::table('pdnovel_view')." SET volumes=volumes+1, lastvolume='$volumename', lastvolumeid=$volumeid WHERE novelid=$novelid");
			showmessage('do_success', "novel.php?mod=home&do=manage&novelid=$novelid#volumeid$volumeid");
		}
	}elseif($ac == 'edit'){
		$volumeid = $_G['gp_volumeid'];
		$volume = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_volume')." WHERE volumeid=$volumeid");
		if(!$volume){
			showmessage('分卷ID出错');
		}
		if(submitcheck('postsubmit')){
			$volumename = addslashes(cutstr($_G['gp_volumename'], 50));
			DB::query("UPDATE ".DB::table('pdnovel_volume')." SET volumename='$volumename' WHERE volumeid=$volumeid");
			if($volume['volumeid'] == $novel['lastvolumeid']){
				DB::query("UPDATE ".DB::table('pdnovel_view')." SET lastvolume='$volumename' WHERE novelid=$novelid");
			}
			showmessage('do_success', "novel.php?mod=home&do=manage&novelid=$novelid#volumeid$volumeid");
		}
	}elseif($ac == 'del'){
		$volumeid = $_G['gp_volumeid'];
		$volume = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_volume')." WHERE volumeid=$volumeid");
		if(!$volume){
			showmessage('分卷ID出错');
		}
		if(submitcheck('postsubmit')){
			DB::query("DELETE FROM ".DB::table('pdnovel_volume')." WHERE volumeid=$volumeid");
			if($volume[volumechapters]){
				$query = DB::query("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE volumeid=$volumeid");
				while($chapter = DB::fetch($query)){
					@unlink($chapterpath.$chapter[chaptercontent]);
				}
				DB::query("DELETE FROM ".DB::table('pdnovel_chapter')." WHERE volumeid=$volumeid");	
			}
			if($volume['volumeid'] == $novel['lastvolumeid']){
				$lvolume = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_volume')." WHERE novelid=$novelid ORDER BY volumeorder DESC LIMIT 1");
				if(!$lvolume){
					$lvolume[volumename] = '';
					$lvolume[volumeid] = 0;
				}
				DB::query("UPDATE ".DB::table('pdnovel_view')." SET lastvolume='$lvolume[volumename]', lastvolumeid='$lvolume[volumeid]', volumes=volumes-1 WHERE novelid=$novelid");
			}else{
				DB::query("UPDATE ".DB::table('pdnovel_view')." SET volumes=volumes-1 WHERE novelid=$novelid");
			}
			showmessage('do_success', "novel.php?mod=home&do=manage&novelid=$novelid");
		}
	}
	include template('diy:pdnovel/home_volume');
	
}elseif($do == 'chapter'){

	$volumeid = $_G['gp_volumeid'];
	$volume = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_volume')." WHERE volumeid=$volumeid");
	if(!$volume){
		showmessage('小说分卷ID出错');
	}
	$novelid = $volume[novelid];
	$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid");
	if(!$novel){
		showmessage('小说ID出错');
	}
	if($ac == 'add'){
		if(submitcheck('postsubmit')){
			$chaptername = addslashes(cutstr($_G['gp_chaptername'], 50));
			$content = $_G['gp_chaptercontent'];
			$lastchaptercontent = addslashes(cutstr(strip_tags($content), 600));
			$content = str_replace("\r\n", "<br>", $content);
			$chapterwords = ceil(strlen($content)/2);
			$content = "document.write('".addslashes($content)."');";
			$order = DB::result_first("SELECT chapterorder FROM ".DB::table('pdnovel_chapter')." WHERE volumeid=$volumeid ORDER BY chapterorder DESC LIMIT 1");
			$order =  $order ? $order : 0;
			$chapterorder = $order + 1;
			$time = $_G['timestamp'];
			$insertarr = array(
				'novelid' => $volume[novelid],
				'volumeid' => $volumeid,
				'poster' => $_G['username'],
				'posterid' => $_G['uid'],
				'postdate' => $time,
				'lastupdate' => $time,
				'chaptername' => $chaptername,
				'chapterorder' => $chapterorder,
				'chapterwords' => $chapterwords
			);
			DB::insert('pdnovel_chapter', $insertarr);
			$chapterid = DB::insert_id();
			$subchapterid = floor($chapterid/1000);
			$subsubchapterid = floor($subchapterid/1000);
			if (!file_exists($chapterpath.$subsubchapterid)){
				@mkdir($chapterpath.$subsubchapterid);
			}
			if (!file_exists($chapterpath.$subsubchapterid.'/'.$subchapterid)){
				@mkdir($chapterpath.$subsubchapterid.'/'.$subchapterid);
			}
			$chaptercontent = $subsubchapterid.'/'.$subchapterid.'/'.$chapterid.'-'.rand(100,999).'.txt';
			@file_put_contents($chapterpath.$chaptercontent, $content);
			DB::update('pdnovel_chapter', array('chaptercontent' => $chaptercontent), "chapterid=$chapterid");
			DB::query("UPDATE ".DB::table('pdnovel_volume')." SET volumechapters=volumechapters+1, volumewords=volumewords+$chapterwords WHERE volumeid=$volumeid");
			DB::query("UPDATE ".DB::table('pdnovel_view')." SET chapters=chapters+1, words=words+$chapterwords, lastupdate=$time, lastchapter='$chaptername', lastchapterid=$chapterid, lastchaptercontent='$lastchaptercontent' WHERE novelid=$volume[novelid]");
			updatecreditbyaction('pdnovelchapter', $_G['uid']);
			showmessage('do_success', "novel.php?mod=home&do=manage&novelid=$volume[novelid]#chapterid$chapterid");
		}
	}elseif($ac == 'edit'){
		$chapterid = $_G['gp_chapterid'];
		$chapter = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE chapterid=$chapterid");
		if(!$chapter){
			showmessage('章节ID出错');
		}
		if(submitcheck('postsubmit')){
			$chaptername = addslashes(cutstr($_G['gp_chaptername'], 50));
			$content = $_G['gp_chaptercontent'];
			$lastchaptercontent = addslashes(cutstr(strip_tags($content), 600));
			$content = str_replace("\r\n", "<br>", $content);
			$chapterwords = ceil(strlen($content)/2);
			$content = "document.write('".addslashes($content)."');";
			@file_put_contents($chapterpath.$chapter[chaptercontent], $content);	
			DB::query("UPDATE ".DB::table('pdnovel_chapter')." SET chaptername='$chaptername', chapterwords=$chapterwords WHERE chapterid=$chapterid");
			DB::query("UPDATE ".DB::table('pdnovel_volume')." SET volumewords=volumewords+$chapterwords-$chapter[chapterwords] WHERE volumeid=$volumeid");
			if($chapter['chapterid'] == $novel['lastchapterid']){
				DB::query("UPDATE ".DB::table('pdnovel_view')." SET words=words+$chapterwords-$chapter[chapterwords], lastchapter='$chaptername', lastchaptercontent='$lastchaptercontent' WHERE novelid=$novelid");
			}else{
				DB::query("UPDATE ".DB::table('pdnovel_view')." SET words=words+$chapterwords-$chapter[chapterwords] WHERE novelid=$novelid");
			}
			showmessage('do_success', "novel.php?mod=home&do=manage&novelid=$novelid#chapterid$chapterid");
		}else{
			$content = @file_get_contents($chapterpath.$chapter[chaptercontent]);
			$content = preg_replace("/document.write\('(.*?)'\);/i", "$1", $content);
			$content = str_replace("<br>", "\r\n", $content);
		}
	}elseif($ac == 'del'){
		$chapterid = $_G['gp_chapterid'];
		$chapter = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE chapterid=$chapterid");
		if(!$chapter){
			showmessage('章节ID出错');
		}
		if(submitcheck('postsubmit')){
			DB::query("DELETE FROM ".DB::table('pdnovel_chapter')." WHERE chapterid=$chapterid");
			@unlink($chapterpath.$chapter[chaptercontent]);
			DB::query("UPDATE ".DB::table('pdnovel_volume')." SET volumechapters=volumechapters-1, volumewords=volumewords-$chapter[chapterwords] WHERE volumeid=$volumeid");
			if($chapterid == $novel['lastchapterid']){
				if($volume[volumechapters] == 1){
					$lvolumeid = DB::result_first("SELECT volumeid FROM ".DB::table('pdnovel_volume')." WHERE novelid=$novelid AND volumeid<$volumeid ORDER BY volumeorder DESC LIMIT 1");
					if($lvolumeid){
						$lchapter = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE volumeid=$lvolumeid ORDER BY chapterorder DESC LIMIT 1");
					}
				}else{
					$lchapter = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE volumeid=$volumeid ORDER BY chapterorder DESC LIMIT 1");
				}
				if($lchapter){
					$content = @file_get_contents($chapterpath.$lchapter[chaptercontent]);
					$content = preg_replace("/document.write\('(.*?)'\);/i", "$1", $content);
					$content = str_replace("<br>", "\r\n", $content);
					$lastchaptercontent = addslashes(cutstr(strip_tags($content), 600));
				}else{
					$lchapter[chaptername] = '';
					$lchapter[lastupdate] = $_G['timestamp'];
					$lchapter[chapterid] = 0;
					$lastchaptercontent = '';
				}
				DB::query("UPDATE ".DB::table('pdnovel_view')." SET lastchapter='$lchapter[chaptername]', lastupdate=$lchapter[lastupdate], lastchapterid='$lchapter[chapterid]', lastchaptercontent='$lastchaptercontent', chapters=chapters-1, words=words-$chapter[chapterwords] WHERE novelid=$novelid");
			}else{
				DB::query("UPDATE ".DB::table('pdnovel_view')." SET chapters=chapters-1, words=words-$chapter[chapterwords] WHERE novelid=$novelid");
			}
			showmessage('do_success', "novel.php?mod=home&do=manage&novelid=$novelid#volumeid$volumeid");
		}		
	}
	include template('diy:pdnovel/home_chapter');
	
}



?>