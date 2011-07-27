<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_exttools.php 2555 2011-06-24 08:46:53Z songlixin $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

@define('TOOLS_VERSION', '1.8M');
@define('TOOLS_RELEASE', '20110624');

$doarray = array('tools');
$do = in_array($_G['gp_do'], $doarray) ? $_G['gp_do'] : 'tools';


if(file_exists(DISCUZ_ROOT.'./data/plugindata/tools.lang.php')){
	include DISCUZ_ROOT.'./data/plugindata/tools.lang.php';
} else {
	loadcache('pluginlanguage_template');
	loadcache('pluginlanguage_script');
	$scriptlang['tools'] = $_G['cache']['pluginlanguage_script']['tools'];
}


if(empty($scriptlang['tools'])) {
	cpmsg('Language File Not Exists. Reinstall Plugin TOOLS Please.','','error');
}


include_once(DISCUZ_ROOT.'/source/discuz_version.php');
$xver = preg_replace('/(X|R|C)/im','',DISCUZ_VERSION);
require DISCUZ_ROOT.'./config/config_global.php';
require_once DISCUZ_ROOT.'./source/plugin/tools/function/tools.func.php';
$toolslang = $scriptlang['tools'];
//#base  action=exttools&operation=$operation&do=tools
$menuname = "menu_exttools_{$operation}";
showsubmenu($menuname);

if($operation == 'moudle'){
	if($xver > 1) {
		cpmsg_error($toolslang['ver_has']);	
	}
	if(!isfounder()){
		cpmsg_error('tools:noperm');
	}
	
	if(submitcheck('submit')){
		$_config['app']['default'] = $_G['gp_index'];
		$_config['app']['domain']['forum'] = str_replace('http://','',$_G['gp_forumdomain']);
		$_config['app']['domain']['group'] = str_replace('http://','',$_G['gp_groupdomain']);
		$_config['app']['domain']['home'] = str_replace('http://','',$_G['gp_homedomain']);
		$_config['app']['domain']['portal'] = str_replace('http://','',$_G['gp_portaldomain']);
		$_config['home']['allowdomain'] = $_G['gp_allowdomain'];
		$_config['home']['domainroot'] = $_G['gp_rootdomain'];
		$_config['home']['holddomain'] = $_G['gp_holddomain'];
		$_config['cookie']['cookiedomain'] = $_G['gp_cookiedomain'];
		save_config_file(DISCUZ_ROOT.'./config/config_global.php',$_config,DISCUZ_ROOT.'./config/config_global.php');
		cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');
	}
	
	showformheader("exttools&operation=$operation&do=tools",'submit');
	showtableheader($toolslang['defaultindex']);
	showsetting($toolslang['nowindex'],array('index',array(array('forum',$toolslang['forum']),array('group',$toolslang['group']),array('home',$toolslang['home']),array('portal',$toolslang['portal']))),$_config['app']['default'],'mradio','',0,$toolslang['indextips']);
	showtablefooter();
	
	showtableheader($toolslang['moddomain']);
	showsetting($toolslang['forum'],'forumdomain',$_config['app']['domain']['forum'],'text','',0,$toolslang['domaintips']);
	showsetting($toolslang['group'],'groupdomain',$_config['app']['domain']['group'],'text');
	showsetting($toolslang['home'],'homedomain',$_config['app']['domain']['home'],'text');
	showsetting($toolslang['portal'],'portaldomain',$_config['app']['domain']['portal'],'text');
	showtablefooter();
	
	showtableheader($toolslang['moudle_homedomain']);
	showsetting($toolslang['moudle_domain'],'allowdomain',$_config['home']['allowdomain'],'radio');
	showsetting($toolslang['moudle_root'],'rootdomain',$_config['home']['domainroot'],'text');
	showsetting($toolslang['moudle_holddomain'],'holddomain',$_config['home']['holddomain'],'text','',0,$toolslang['moudle_holddomaintip']);
	showsetting($toolslang['moudle_cookiedomain'],'cookiedomain',$_config['cookie']['cookiedomain'],'text','',0,$toolslang['moudle_cookiedomaintip']);
	showtablefooter();
	showsubmit('submit', $toolslang['submit']);
	showformfooter();
} elseif($operation == 'cleardb') {
	if(!isfounder()){
		cpmsg_error('tools:noperm');
	}
	$rpp = $_G['gp_rpp'] ? $_G['gp_rpp'] : 1000;
	$rows = $_G['gp_rows'] ? $_G['gp_rows'] : 0;
	
	$data = DB::fetch_first("SELECT MAX(tid) as maxtid,MIN(tid) as mintid,count(tid) as count FROM ".DB::table('forum_thread'));
	$maxtid = $data['maxtid'];$mintid = $data['mintid'];$count = $data['count'];
	$posttable = array('p' => getposttable('p',0),'a' => getposttable('a',0));
	if($xver == 2){ //X2 兼容
		$posttable = array('p' => DB::table(getposttable('p',0)),'a' => DB::table(getposttable('a',0)));	
	}
	$data = DB::fetch_first("SELECT MAX(pid) as maxpid,MIN(pid) as minpid,count(pid) as count FROM ".$posttable['p']);
	$maxpid = $data['maxpid'];$minpid = $data['minpid'];$countpid = $data['count'];
	$maxposttableid = DB::result_first("SELECT MAX(posttableid) FROM ".DB::table('forum_thread'));
	$allposttalbe = array('forum_post');
	$i = 1;
	while($i <= $maxposttableid){
		$allposttalbe[] = 'forum_post_'.$i;
		$i++;
	}
	loadcache('threadtableids');
	foreach($_G['cache']['threadtableids'] as $value){
		$allthreadtalbe[] = 'forum_thread_'.$value;
	}
	

	if(submitcheck('clearpostsubmit',1)){
		$id = getmaxmin(getposttable('primary'),'pid');
		if($_G['gp_start'] == 0){
			$_G['gp_start'] = $id['min'];
		}
		$start = $_G['gp_start'];
		$end = $_G['gp_start'] + $rpp;
		$posttable = getposttable('primary');
		
		$query = DB::query("SELECT pid,tid FROM ".DB::table($posttable)." WHERE pid >= $start AND pid < $end");
		//note
		while($post = DB::fetch($query)){
			$tid = DB::result_first("SELECT tid FROM ".DB::table('forum_thread')." WHERE tid='".$post['tid']."'");
			foreach($allthreadtalbe as $value) {
				$tid = ($tid || DB::result_first("SELECT tid FROM ".DB::table($value)." WHERE tid='".$post['tid']."'"));
			}
			if(!$tid) {
				$rows ++;
				DB::delete($posttable,"pid = $post[pid]");
			}
		}
		$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&rows=$rows&clearpostsubmit=yes&rpp=$rpp";
		if($end <= $id['max']+1){
			cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $start, 'next' => $end)), $nextlink, 'loading');	
		} else {
			$inc = $id['max']+1;
			DB::query("ALTER TABLE ".DB::table('forum_post')." AUTO_INCREMENT = $inc");
			cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');	
		}
	} elseif(submitcheck('clearthreadsubmit',1)) {
		$id = getmaxmin('forum_thread','tid');
		if($_G['gp_start'] == 0){
			$_G['gp_start'] = $id['min'];
		}
		$start = $_G['gp_start'];
		$end = $_G['gp_start'] + $rpp;
	
		$query = DB::query("SELECT tid,subject FROM ".DB::table('forum_thread')." WHERE tid >= $start AND tid < $end");
		while($thread = DB::fetch($query)){
			$posttableid = getposttablebytid($thread[tid]);
			$posts = DB::result_first("SELECT count(*) FROM ".DB::table("$posttableid")." WHERE tid = $thread[tid]");
			if($posts <= 0) {
				$rows ++;
				DB::delete('forum_thread',"tid = $thread[tid]");
			} elseif($thread['subject'] == '') {
				$rows ++;
				DB::delete('forum_thread',"tid = $thread[tid]");	
				DB::delete("$posttableid","tid = $thread[tid]");	
			} else {
				$query = DB::query("SELECT a.aid FROM ".DB::table("$posttableid")." p,".DB::table('forum_attachment')." a WHERE a.tid = $thread[tid] AND a.pid = p.pid AND p.invisible = 0 LIMIT 1");	
				$attachment = DB::num_rows($query) ? 1 : 0;//修复附件
				$query  = "SELECT pid, subject, rate FROM ".DB::table("$posttableid")." WHERE tid= $thread[tid]  AND invisible='0' ORDER BY dateline LIMIT 1";
				$firstpost = DB::fetch_first($query);
				$firstpost['subject'] = trim($firstpost['subject']) ? $firstpost['subject'] : $thread['subject']; //针对某些转换过来的论坛的处理
				$firstpost['subject'] = addslashes($firstpost['subject']);
				@$firstpost['rate'] = $firstpost['rate'] / abs($firstpost['rate']);//修复发帖
				$query  = "SELECT author, dateline FROM ".DB::table("$posttableid")." WHERE tid= $thread[tid] AND invisible='0' ORDER BY dateline DESC LIMIT 1";
				$lastpost = DB::fetch_first($query);//修复最后发帖
				DB::update('forum_thread',array("subject" => $firstpost[subject],"replies" => $posts,"lastpost" => $lastpost[dateline],"lastposter" => addslashes($lastpost[author]),"rate" => $firstpost[rate],"attachment" => $attachment),"tid = $thread[tid]",1);
				DB::update("$posttableid",array('first' => '1','subject' => $firstpost[subject]),"pid = $firstpost[pid]",1);
				DB::update("$posttableid",array('first' => '0','subject' => $firstpost[subject]),"tid = $thread[tid] AND pid <> $firstpost[pid]",1);
			}
		}
		$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&rows=$rows&clearthreadsubmit=yes&rpp=$rpp";
		if($end <= $id['max']+1){
			cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $start, 'next' => $end)), $nextlink, 'loading');	
		} else {
			$inc = $id['max']+1;
			DB::query("ALTER TABLE ".DB::table('forum_thread')." AUTO_INCREMENT = $inc");
			cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');	
		}
	} elseif(submitcheck('clearattachmentsubmit',1)) {
		$id = getmaxmin('forum_attachment','aid');
		
		if($_G['gp_start'] == 0){
			$_G['gp_start'] = $id['min'];
		}
		$start = $_G['gp_start'];
		$end = $_G['gp_start'] + $rpp;
		if(intval($xver) >= 2){
			
			$query = DB::query("SELECT aid,pid,tid FROM ".DB::table('forum_attachment')." WHERE aid >= $start AND aid <= $end");
			while($attach = DB::fetch($query)){
				$tid = $attach['tid'];
				$aid = $attach['aid'];
				$pid = DB::result_first("SELECT pid FROM ".DB::table('forum_post')." WHERE pid='".$attach['pid']."'");
				foreach($allposttalbe as $value){
					$pid = ($pid || DB::result_first("SELECT pid FROM ".DB::table($value)." WHERE pid='".$attach['pid']."'"));
				}
				if(!$pid) {
					$rows ++;
					DB::delete('forum_attachment',"aid = $attach[aid]");
					$tableid = $tid{strlen($tid)-1};
					$attach['attachment'] = DB::result_first("SELECT attachment FROM ".DB::table('forum_attachment_'.$tableid)." WHERE aid = $aid");
					DB::delete('forum_attachment_'.$tableid,"aid = $attach[aid]");	
					//DB::delete('forum_attachpaymentlog',"aid = $attach[aid]"); DiscuzX 613 去掉
					@unlink($_G['setting']['attachdir'].'/forum/'.$attach['attachment']);
				}
			}
		} else {
			$query = DB::query("SELECT aid,pid,attachment FROM ".DB::table('forum_attachment')." WHERE aid >= $start AND aid <= $end");
			while($attach = DB::fetch($query)){
				$pid = DB::result_first("SELECT pid FROM ".DB::table('forum_post')." WHERE pid='".$attach['pid']."'");
				foreach($allposttalbe as $value){
					$pid = ($pid || DB::result_first("SELECT pid FROM ".DB::table($value)." WHERE pid='".$attach['pid']."'"));
				}
				if(!$pid) {
					$rows ++;
					DB::delete('forum_attachment',"aid = $attach[aid]");
					DB::delete('forum_attachmentfield ',"aid = $attach[aid]");
					//DB::delete('forum_attachpaymentlog',"aid = $attach[aid]"); DiscuzX 613 去掉
					@unlink($_G['setting']['attachdir'].'/forum/'.$attach['attachment']);
				}
			}
		}

		$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&rows=$rows&clearattachmentsubmit=yes&rpp=$rpp";
		if($end <= $id['max']+1){
			cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $start, 'next' => $end)), $nextlink, 'loading');	
		} else {
			$inc = $id['max']+1;
			DB::query("ALTER TABLE ".DB::table('forum_attachment')." AUTO_INCREMENT = $inc");
			cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');	
		}
	} elseif(submitcheck('clearmemberssubmit',1)) {
		$id = getmaxmin('common_member_field_forum','uid');
		if($_G['gp_start'] == 0){
			$_G['gp_start'] = $id['min'];
		}
		$start = $_G['gp_start'];
		$end = $_G['gp_start'] + $rpp;
		$query = DB::query("SELECT uid FROM ".DB::table('common_member_field_forum')." WHERE uid >= $start AND uid <= $end");
		while($member = DB::fetch($query)){
			$uid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE uid='".$member['uid']."'");
			if(!$uid) {
				$rows ++;
				DB::delete('common_member',"uid = $member[uid]");
				@DB::delete('common_member_field_home',"uid = $member[uid]");
				@DB::delete('common_member_log',"uid = $member[uid]");
				@DB::delete('common_member_security',"uid = $member[uid]");
				@DB::delete('common_member_status',"uid = $member[uid]");
				@DB::delete('common_member_profile',"uid = $member[uid]");
			}
		}
		$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&rows=$rows&clearmemberssubmit=yes&rpp=$rpp";
		if($end <= $id['max']+1){
			cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $start, 'next' => $end)), $nextlink, 'loading');	
		} else {
			cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');	
		}
	} elseif(submitcheck('clearalbumsubmit',1)) {
		$id = getmaxmin('home_album','albumid');
		if($_G['gp_start'] == 0){
			$_G['gp_start'] = $id['min'];
		}
		$start = $_G['gp_start'];
		$end = $_G['gp_start'] + $rpp;
		$query = DB::query("SELECT albumid,pic FROM ".DB::table('home_album')." WHERE albumid >= $start AND albumid <= $end");
		while($album = DB::fetch($query)){
			$pic = DB::result_first("SELECT count(picid) FROM ".DB::table('home_pic')." WHERE albumid = $album[albumid]");
			if($pic == 0){
				$rows ++;
				DB::delete('home_album',"albumid = $album[albumid]");
				@unlink($_G['setting']['attachdir'].'/album/'.$album['pic']);
			}
		}
		$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&rows=$rows&clearalbumsubmit=yes&rpp=$rpp";
		if($end <= $id['max']+1){
			cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $start, 'next' => $end)), $nextlink, 'loading');	
		} else {
			$inc = $id['max']+1;
			DB::query("ALTER TABLE ".DB::table('home_album')." AUTO_INCREMENT = $inc");
			cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');	
		}	
	} elseif(submitcheck('clearpicsubmit',1)) {
		$id = getmaxmin('home_pic','picid');
		if($_G['gp_start'] == 0){
			$_G['gp_start'] = $id['min'];
		}
		$start = $_G['gp_start'];
		$end = $_G['gp_start'] + $rpp;
		$query = DB::query("SELECT picid,albumid,filepath FROM ".DB::table('home_pic')." WHERE picid >= $start AND picid <= $end AND albumid > 0");
		while($pic = DB::fetch($query)){
			$album = DB::result_first("SELECT albumid FROM ".DB::table('home_album')." WHERE albumid = $pic[albumid]");
			if($album == 0){
				$rows ++;
				DB::delete('home_pic',"picid = $pic[picid]");
				DB::delete('home_picfield ',"picid = $pic[picid]");
				@unlink($_G['setting']['attachdir'].'/album/'.$pic['filepath']);
				@unlink($_G['setting']['attachdir'].'/album/'.$pic['filepath'].'.thumb.jpg');
			}
		}
		$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&rows=$rows&clearpicsubmit=yes&rpp=$rpp";
		if($end <= $id['max']+1){
			cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $start, 'next' => $end)), $nextlink, 'loading');	
		} else {
			$inc = $id['max']+1;
			DB::query("ALTER TABLE ".DB::table('home_pic')." AUTO_INCREMENT = $inc");
			cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');	
		}
	} elseif(submitcheck('repairmfsubmit',1)) {
		$id = getmaxmin('common_member ','uid');
		if($_G['gp_start'] == 0){
			$_G['gp_start'] = $id['min'];
		}
		$start = $_G['gp_start'];
		$end = $_G['gp_start'] + $rpp;
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE uid >= $start AND uid <= $end");
		$field = array('statusid' => 'common_member_status',
				'profileid' => 'common_member_profile',
				'forumuid' => 'common_member_field_forum',
				'homeuid' => 'common_member_field_home',
				'countid' => 'common_member_count');
		while($member = DB::fetch($query)){
			foreach($field as $key => $value){
				$$key = DB::result_first("SELECT uid FROM ".DB::table($value)." WHERE uid = $member[uid]");
				if(!$$key){
					if($key == 'forumuid') {
						DB::insert('common_member_field_forum',array('uid' => $member['uid'],'customshow' => '26'));	
					} else {
						DB::insert($value,array('uid' => $member['uid']));	
					}
				}
			}
		}
		$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&rows=$rows&repairmfsubmit=yes&rpp=$rpp";
		if($end <= $id['max']+1){
			cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $start, 'next' => $end)), $nextlink, 'loading');	
		} else {
			cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');	
		}
	} elseif(submitcheck('replacetidsubmit',1)) {
		
		$percent = $_G['gp_percent'] ? $_G['gp_percent'] : 2;
		if($percent == '10') {
			$auto = $maxtid + 1;
			DB::query("ALTER TABLE ".DB::table('forum_thread')." AUTO_INCREMENT = $auto");
			$nextlink = "action=exttools&operation=$operation&do=tools";
			cpmsg($toolslang['replacesuccess'],$nextlink,'succeed');	
		}
		$eightpre = floor(($maxtid - $mintid - $count)*$percent/10);
		$pretid = DB::result_first("SELECT tid FROM ".DB::table('forum_thread')." WHERE tid < $eightpre ORDER BY tid DESC LIMIT 1");
		$pretid = $pretid ? $pretid : 0;
		$oldcount = DB::result_first("SELECT count(tid) FROM ".DB::table('forum_thread')." WHERE tid > $pretid AND tid < $eightpre");
		//echo "SELECT count(tid) FROM ".DB::table('forum_thread')." WHERE tid > $pretid AND tid < $eightpre".'<br/>';
		$differ = $eightpre - 1 - $pretid;
		//echo $differ;
		if(($differ > 0) && ($oldcount == 0)) {
			//echo "UPDATE ".DB::table('forum_thread')." SET tid = tid - $differ WHERE tid >= $eightpre";
			DB::query("UPDATE ".DB::table('forum_thread')." SET tid = tid - $differ WHERE tid >= $eightpre ORDER BY tid");
			$tablelist = array('forum_attachment','forum_activity','forum_activityapply','forum_attachmentfield','forum_debate',
					'forum_debatepost','forum_debatepost','forum_forumrecommend','forum_memberrecommend','forum_poll','forum_polloption','forum_postcomment',
					'forum_postlog','forum_postposition','forum_relatedthread','forum_rsscache','forum_threadlog','forum_threadmod','forum_trade','forum_tradelog','forum_typeoptionvar');
			$tablelist = array_merge($allposttalbe,$tablelist);
			foreach($tablelist as $value){
				DB::query("UPDATE ".DB::table($value)." SET tid = tid - $differ WHERE tid >= $eightpre ORDER BY tid");
			}		
			$nextlink = "action=exttools&operation=$operation&do=tools&percent=$percent&replacetidsubmit=yes";
			cpmsg($toolslang['nowreplace'],$nextlink,'loading',array('percent' => ($percent - 1)));
		}
		if($differ == 0) {
			$nextpercent = $percent + 1;
			$lastpercent = $percent - 1;
			$nextlink = "action=exttools&operation=$operation&do=tools&percent=$nextpercent&replacetidsubmit=yes";
			cpmsg($toolslang['nowreplace'],$nextlink,'loading',array('percent' => $percent));
		}
	} elseif(submitcheck('replacepidsubmit',1)) {
		
		$percent = $_G['gp_percent'] ? $_G['gp_percent'] : 2;
		if($percent == '10') {
			$auto = $maxpid + 1;
			DB::query("ALTER TABLE ".$posttable['p']." AUTO_INCREMENT = $auto");
			$nextlink = "action=exttools&operation=$operation&do=tools";
			cpmsg($toolslang['replacesuccess'],$nextlink,'succeed');	
		}
		
		$eightpre = floor(($maxpid - $minpid - $countpid)*$percent/10);
		
		$prepid = DB::result_first("SELECT pid FROM ".$posttable['p']." WHERE pid < $eightpre ORDER BY pid DESC LIMIT 1");
		$prepid = $prepid ? $prepid : 0;
		$oldcount = DB::result_first("SELECT count(pid) FROM ".$posttable['p']." WHERE pid > $prepid AND pid < $eightpre");
		$oldcount = $oldcount ? $oldcount : 0;
		$differ = $eightpre - 1 - $prepid;
		//echo "SELECT count(pid) FROM ".$posttable['p']." WHERE pid > $prepid AND pid < $eightpre";
		if(($differ > 0) && ($oldcount == 0)) {
			DB::query("UPDATE ".$posttable['p']." SET pid = pid - $differ WHERE pid >= $eightpre ORDER BY pid");
			$tablelist = array('forum_attachment','forum_attachmentfield','forum_debatepost','forum_postcomment','forum_postlog','forum_postposition','forum_ratelog','forum_trade','forum_tradecomment','forum_tradelog',
						'forum_warning');
			foreach($tablelist as $value){
				DB::query("UPDATE ".DB::table($value)." SET pid = pid - $differ WHERE pid >= $eightpre ORDER BY pid");
			}
			$nextlink = "action=exttools&operation=$operation&do=tools&percent=$percent&replacepidsubmit=yes";
			cpmsg($toolslang['nowreplace'],$nextlink,'loading',array('percent' => ($percent - 1)));
		}
		if($differ == 0) {
			$nextpercent = $percent + 1;
			$lastpercent = $percent - 1;
			$nextlink = "action=exttools&operation=$operation&do=tools&percent=$nextpercent&replacepidsubmit=yes";
			cpmsg($toolslang['nowreplace'],$nextlink,'loading',array('percent' => $percent));
		}
		
	} elseif(submitcheck('clearmoderetersumit',1)) {
		$tids = array();       //主题ID
		$pids = array();      //回复ID
		$blogids = array();  //日志ID
		$doids = array();   //记录ID
		$picids = array();
		$sids = array();
		$cids = array();
		$aids = array();
		$acids = array();
	
		$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE displayorder = -2");
		while($data = DB::fetch($query)){
			$tids[] = $data['tid'];	
		}
		
		$query = DB::query("SELECT pid FROM ".DB::table('forum_post')." WHERE invisible = -2");
		while($data = DB::fetch($query)){
			$pids[] = $data['pid'];	
		}
		
		$query = DB::query("SELECT blogid FROM ".DB::table('home_blog')." WHERE status = 1");
		while($data = DB::fetch($query)){
		
			$blogids[] = $data['blogid'];	
		}
		
		$query = DB::query("SELECT doid FROM ".DB::table('home_doing')." WHERE status = 1");
		while($data = DB::fetch($query)){
			$doids[] = $data['doid'];	
		}
		
		$query = DB::query("SELECT picid FROM ".DB::table('home_pic')." WHERE status = 1");
		while($data = DB::fetch($query)){
			$picids[] = $data['picid'];	
		}
		
		$query = DB::query("SELECT sid FROM ".DB::table('home_share')." WHERE status = 1");
		while($data = DB::fetch($query)){
			$sids[] = $data['sid'];	
		}

		$query = DB::query("SELECT cid FROM ".DB::table('home_comment')." WHERE status = 1");
		while($data = DB::fetch($query)){
			$cids[] = $data['cid'];	
		}
		$query = DB::query("SELECT aid FROM ".DB::table('portal_article_title')." WHERE status = 1");
		while($data = DB::fetch($query)){
			$aids[] = $data['aid'];	
		}
		$query = DB::query("SELECT cid FROM ".DB::table('portal_comment')." WHERE status = 1");
		while($data = DB::fetch($query)){
			$acids[] = $data['cid'];	
		}
		
		//处理
		if(count($tids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'tid' and id NOT IN (".implode(',',$tids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'tid'");
		}
		
		if(count($pids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'postid' and id NOT IN (".implode(',',$pids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'postid'");
		}
		//update 20110705
		if(count($pids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'pid' and id NOT IN (".implode(',',$pids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'pid'");
		}
		
		if(count($blogids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'blogid' and id NOT IN (".implode(',',$blogids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'blogid'");
		}
		if(count($doids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'doid' and id NOT IN (".implode(',',$doids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'doid'");
		}
		if(count($picids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'picid' and id NOT IN (".implode(',',$picids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'picid'");
		}
		if(count($sids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'sid' and id NOT IN (".implode(',',$sids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'sid'");
		}
		if(count($cids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype IN ('uid_cid','blogid_cid','picid_cid') and id NOT IN (".implode(',',$cids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype IN ('uid_cid','blogid_cid','picid_cid')");
		}
		if(count($aids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'aid' and id NOT IN (".implode(',',$aids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype = 'aid'");
		}
		if(count($acids)){
			$query = "DELETE FROM ".DB::table('common_moderate')." WHERE idtype IN ('aid_cid','topicid_cid') and id NOT IN (".implode(',',$acids).")";
			DB::query($query);
		} else {
			DB::query("DELETE FROM ".DB::table('common_moderate')." WHERE idtype IN ('aid_cid','topicid_cid')");
		}
		
		cpmsg($toolslang['clearmodereter_suc'],dreferer(),'succeed');
	}
	//需要清理数据库冗余的列表
	$dbtablearray = array(
		'clearpost' => 'pid',
		'clearthread' => 'tid',
		'clearattachment' => 'aid',
		'clearmembers' => 'uid',
		'clearalbum' => 'albumid',
		'clearpic' => 'picid',
		);
	
	showtips($toolslang['cleardbtips']);
	showformheader("exttools&operation=$operation&do=tools",'submit');
	showtableheader($toolslang['cleardb']);
		showtablerow('', array('class="td21"'), array(
			$toolslang['jump'],
			'<input type="text" class="txt" name="rpp" value="1000" />',
		));
	
	foreach($dbtablearray as $key => $value){
		showtablerow('', array('class="td21"'), array(
			"$toolslang[$key]",
			'<input type="submit" class="btn" name="'.$key.'submit" value="'.$lang['submit'].'" />'
		));
	}	
	showtablefooter();
	showtableheader($toolslang['repairmf']);
		showtablerow('', array('class="td21"'), array(
			"$toolslang[repairmf]",
			'<input type="submit" class="btn" name="repairmfsubmit" value="'.$lang['submit'].'" />'
		));
	showtablefooter();
	if($xver >= 2){
		showtableheader($toolslang['clearmodereter']);
			showtablerow('', array('class="td21"'), array(
				"$toolslang[clearmodereter]",
				'<input type="submit" class="btn" name="clearmoderetersumit" value="'.$lang['submit'].'" />'
			));
		showtablefooter();
	}

	
	
//	if($maxtid - $mintid - $count >= 1){
//		showtableheader($toolslang['replacetid']);
//		showtablerow('', array('class="td21"'), array(
//			"$toolslang[replacetid]",
//			'<input type="submit" class="btn" name="replacetidsubmit" value="'.$lang['submit'].'" />',
//			$toolslang['replacetidtip']
//		));
//		showtablefooter();
//	}
	
//	if($maxpid - $minpid - $countpid >= 1){
//		showtableheader($toolslang['replacepid']);
//		showtablerow('', array('class="td21"'), array(
//			"$toolslang[replacepid]",
//			'<input type="submit" class="btn" name="replacepidsubmit" value="'.$lang['submit'].'" />',
//			$toolslang['replacepidtip']
//		));
//		showtablefooter();
//	}
	
	showformfooter();	
} elseif($operation == 'exportdata') {
	if($xver > 1) {
		cpmsg_error($toolslang['ver_has']);	
	}
	$field = DB::query("SELECT fieldid,title FROM ".DB::table('common_member_profile_setting')." WHERE available = 1");
	while($data = DB::fetch($field)){
		$fields[] = array('s.'.$data['fieldid'],$data['title']);
	}
	
	if(submitcheck('submit')){
		$searchfields = implode(',',$_G['gp_field']);
		
		$titlefields[] = $lang['username'];
		$titlefields[] = $lang['email'];
		foreach($fields as $value){
			if(in_array($value[0],$_G['gp_field'])){
				$titlefields[] = $value[1];
			}	
		}
	
		$persondata = DB::query("SELECT m.username,m.email,$searchfields FROM ".DB::table('common_member_profile')." s,".DB::table('common_member')." m WHERE s.uid = m.uid");
		$fp = fopen(DISCUZ_ROOT.'/data/file.csv', 'w');
		fputcsv($fp,$titlefields);
		while($data = DB::fetch($persondata)){
			$data['gender'] = sex($data['gender']);
			fputcsv($fp,$data);
		}
		fclose($fp);
		$downloadurl = $_G['siteurl'].'data/file.csv';
		cpmsg('tools:exportsuccess',NULL,'succeed',array('url'=> $downloadurl ));
	}
	
	
	showformheader("exttools&operation=$operation&do=tools",'submit');
	showtableheader($toolslang['profilefield']);
	showsetting('',array('field',$fields),'','mcheckbox');
	showtablefooter();
	
	
	showsubmit('submit', $toolslang['submit']);
	showformfooter();
} elseif($operation == 'district') {
	$dc = $_G['gp_dc'];
	$bkroot = DISCUZ_ROOT.'./data/plugindata/';
	$bksep='|';
	
	if(empty($dc)){
		showtips($toolslang['district_tips']);
		echo '<br/>';
		showtableheader($toolslang['district_backup']);
		showformheader("exttools&operation=$operation&do=tools&dc=bk");
		showsubmit('submit',$toolslang['backup']);
		showformfooter();
		showtablefooter();
		
		showtableheader($toolslang['district_renew']);
		showformheader("exttools&operation=$operation&do=tools&dc=re", 'enctype');
		
		showtablerow('', array('colspan="2" class="rowform"', 'colspan="8" class="rowform"'), array(
				$toolslang['backup_file'].' : '.'<input type="file" name="datafile" size="29" class="uploadbtn marginbot" />'
			));
		showsubmit('submit',$toolslang['renew']);
		showformfooter();
		showtablefooter();
		
		showtableheader($toolslang['district_renew_of']);
		showformheader("exttools&operation=$operation&do=tools&dc=res");
		if(!file_exists(DISCUZ_ROOT.'install/data/common_district_1.sql') || !file_exists(DISCUZ_ROOT.'install/data/common_district_2.sql') || !file_exists(DISCUZ_ROOT.'install/data/common_district_3.sql')){
			showtablerow('', array('class="td21"'), array(
				$toolslang['nodicsql'],
			));
		} else {
			showsubmit('submit',$toolslang['setup']);	
		}
		
		showformfooter();
	
		showtablefooter();
	} elseif($dc=='bk') {	//备份现有数据
		$str = '';
		set_time_limit(0);
		//file_put_contents($bkroot.TIMESTAMP.'_dic.bk',$str);
		//header('location:./data/plugindata/'.TIMESTAMP.'_dic.bk');
		ob_end_clean();
		dheader('Cache-control: max-age=0');
		dheader('Expires: '.gmdate('D, d M Y H:i:s', $timestamp - 31536000).' GMT');
		dheader('Content-Encoding: none');
		dheader('Content-Disposition: attachment; filename="'.TIMESTAMP.'_dic.bk"');
		dheader('Content-Type: backup/bk');
		$query = DB::query('SELECT * FROM '.DB::table('common_district'));
		while($row = DB::fetch($query)){
			echo $row['id'].$bksep.$row['name'].$bksep.$row['level'].$bksep.$row['upid']."\n";
		}
		exit('');
		/*
		header('Content-type: backup/bk');
		header('Content-Disposition: attachment; filename="'.TIMESTAMP.'_dic.bk"');
		echo $str;
		*/
	} elseif($dc=='res') {
		$bakfiles[] = DISCUZ_ROOT.'install/data/common_district_1.sql';
		$bakfiles[] = DISCUZ_ROOT.'install/data/common_district_2.sql';
		$bakfiles[] = DISCUZ_ROOT.'install/data/common_district_3.sql';
		foreach($bakfiles as $bakfile){
			@$fp = fopen($bakfile, "r");
			@flock($fp, 3);
			$sqldump = @fread($fp, filesize($bakfile));
			@fclose($fp);
			runquery($sqldump);
		}
		cpmsg('tools:successinstall',"action=exttools&operation=$operation&do=tools",'succeed');
	} else {
		echo "<br>";
		$inf = $_FILES['datafile']['tmp_name'];
		if(!file_exists($inf))
			cpmsg('tools:bk_file_miss',"action=exttools&operation=$operation&do=tools",'error');
		//清楚原有数据
		DB::delete('common_district');
		$fp = fopen($inf,'r');
		while(!feof($fp)){
			$str=fgets($fp);
			$row=explode($bksep,$str);
			if(($l=count($row))>=4){
				for($i=1,$str='';$i<$l-2;$i++)
					$str.=$row[$i].'|';
				DB::query('INSERT INTO '.DB::table('common_district').' (id,name,level,upid) VALUES ('.$row[0].',"'.substr($str,0,-1).'","'.$row[$l-2].'",'.substr($row[$l-1],0,-1).')');
			} else { 
				break;
			}
		}
		fclose($fp);
		cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');
	}	
} elseif($operation == 'censor') {
	$ppp = 20;
	$page = max(1, intval($_G['gp_page']));
	$startlimit = ($page - 1) * $ppp;
	$deletes = '';
	$extrasql = '';
	
	$filter = $_G['gp_filter'];
	if($filter == 'banned') {
		$extrasql = "AND replacement LIKE '%BANNED%'";
	} elseif($filter == 'mod') {
		$extrasql = "AND replacement LIKE '%MOD%'";	
	} elseif($filter == 'replace') {
		$extrasql = "AND replacement NOT LIKE '%MOD%' AND replacement NOT LIKE '%BANNED%'";
	} else {
		$extrasql = '';	
	}

	if(submitcheck('censorsubmit')){
		//print_r($_POST);exit;
		if($ids = dimplode($_G['gp_delete'])) {
			DB::delete('common_word', "id IN ($ids) AND ('{$_G['adminid']}'='1' OR admin='{$_G['username']}')");
		}
	
		if(is_array($_G['gp_find'])) {
			foreach($_G['gp_find'] as $id => $val) {
				$_G['gp_find'][$id]  = $val = trim(str_replace('=', '', $_G['gp_find'][$id]));
				if(strlen($val) < 3) {
					cpmsg('censor_keywords_tooshort', '', 'error');
				}
				$_G['gp_replace'][$id] = $_G['gp_replace'][$id] == '{REPLACE}' ? $_G['gp_replacecontent'][$id] : $_G['gp_replace'][$id];
				$_G['gp_replace'][$id] = daddslashes(str_replace("\\\'", '\'', $_G['gp_replace'][$id]), 1);
				DB::update('common_word', array(
					'find' => $_G['gp_find'][$id],
					'replacement' => $_G['gp_replace'][$id],
				), "id='$id' AND ('{$_G['adminid']}'='1' OR admin='{$_G['username']}')");
			}
		}
	
		$newfind_array = !empty($_G['gp_newfind']) ? $_G['gp_newfind'] : array();
		$newreplace_array = !empty($_G['gp_newreplace']) ? $_G['gp_newreplace'] : array();
		$newreplacecontent_array = !empty($_G['gp_newreplacecontent']) ? $_G['gp_newreplacecontent'] : array();
		
		foreach($newfind_array as $key => $value) {
			$newfind = trim(str_replace('=', '', $newfind_array[$key]));
			$newreplace  = trim($newreplace_array[$key]);
			
			if($newfind != '') {
				if(strlen($newfind) < 3) {
					cpmsg('censor_keywords_tooshort', '', 'error');
				}
				if($newreplace == '{REPLACE}') {
					$newreplace = daddslashes(str_replace("\\\'", '\'', $newreplacecontent_array[$key]), 1);
				}
				if($oldcenser = DB::fetch_first("SELECT admin FROM ".DB::table('common_word')." WHERE find='$newfind'")) {
					cpmsg('censor_keywords_existence', '', 'error');
				} else {
					DB::insert('common_word', array(
						'admin' => $_G['username'],
						'find' => $newfind,
						'replacement' => $newreplace,
					));
				}
			}
		}
	
		updatecache('censor');
		cpmsg('censor_succeed', "action=exttools&operation=$operation&do=tools&page=$page", 'succeed');
	} elseif(submitcheck('censorsercsubmit')) {
		if($_G['gp_beforeword']) {
			$extrasql = "AND find LIKE '%$_G[gp_beforeword]%'";	
		}
		//echo $extrasql = "AND find LIKE %$_G[gp_beforeword]%";exit;
	} elseif(submitcheck('bbsscansubmit',1)) {
		$rpp = '500';
		$convertedrows = isset($_G['gp_convertedrows']) ? $_G['gp_convertedrows'] : 0;
		$start = isset($_G['gp_start']) && $_G['gp_start'] > 0 ? $_G['gp_start'] : 0;
		$end = $start + $rpp - 1;
		$converted = 0;
		$scaned = isset($_G['gp_scaned']) && $_G['gp_scaned'] > 0 ? $_G['gp_scaned'] : 0;
		
		$wordstart = isset($_G['gp_wordstart']) && $_G['gp_wordstart'] > 0 ? $_G['gp_wordstart'] : 0;
		$wordend =  $scaned ? ($scaned + $rpp -1) : ($wordstart + $rpp - 1);
		
		$maxid = isset($_G['gp_maxid']) ? $_G['gp_maxid'] : 0;
		if($posttablemaxid == 0) {
			$posttablemaxid = DB::result_first("SELECT MAX(posttableid) FROM ".DB::table('forum_thread'));
		}
		$posttableid = isset($_G['gp_posttableid']) ? $_G['gp_posttableid'] : $posttablemaxid;
		if($posttableid > 0){
			$posttable = "forum_post_".$posttableid;	
		} else {
			$posttable = "forum_post";
		}
		$wordmaxid = isset($_G['gp_wordmaxid']) ? $_G['gp_wordmaxid'] : 0;
		
		$threads_mod = isset($_G['gp_threads_mod']) ? $_G['gp_threads_mod'] : 0;
		$threads_banned = isset($_G['gp_threads_banned']) ? $_G['gp_threads_banned'] : 0;
		$posts_mod = isset($_G['gp_posts_mod']) ? $_G['gp_posts_mod'] : 0;
		
		$log = toolsgetsetting('bbsltime');
		$logs = explode('|',$log);
		
		$array_find = $array_replace = $array_findmod = $array_findbanned = array();
		if($wordmaxid == 0) {
			$result = DB::fetch_first("SELECT MIN(id) AS wordminid, MAX(id) AS wordmaxid FROM ".DB::table('common_word'));
			$wordstart = $result['wordminid'] ? $result['wordminid'] - 1 : 0;
			$wordmaxid = $result['wordmaxid'];
		}
	
		
		$wordextsql = "where id >= $wordstart AND id <= $wordend";
		$query = DB::query("SELECT find,replacement from ".DB::table('common_word')." $wordextsql");//获得现有规则{BANNED}放回收站 {MOD}放进审核列表
		while($row = DB::fetch($query)) {
			$find = preg_quote($row['find'], '/');
			$replacement = $row['replacement'];
			if($replacement == '{BANNED}') {
				$array_findbanned[] = $find;
			} elseif($replacement == '{MOD}') {
				$array_findmod[] = $find;
			} else {
				$array_find[] = $find;
				$array_replace[] = $replacement;
			}
		}
	
		$array_find = topattern_array($array_find);
		$array_findmod = topattern_array($array_findmod);
		$array_findbanned = topattern_array($array_findbanned);	
	
		$scantype = $_G['gp_scantype'];
		if($scantype == 'addscan') {
			$logs[0] = $logs[0] ? $logs[0] : 0;
			$lasttime = $logs[0];
			if($lasttime) {
				$sqlplus = "AND dateline > $lasttime";
			}
		}
	
		
		if($maxid == 0) {
			$result = DB::fetch_first("SELECT MIN(pid) AS minid, MAX(pid) AS maxid FROM ".DB::table($posttable));
			$start = $result['minid'] ? $result['minid'] - 1 : 0;
			$maxid = $result['maxid'];
		}
	
		$sql = "SELECT pid, tid, first, subject, message from ".DB::table($posttable)." where pid >= $start and pid <= $end AND invisible = 0 $sqlplus";
		if(DB::result_first("SELECT count(pid) from ".DB::table($posttable)." where pid >= $start and pid <= $end AND invisible = 0 $sqlplus") == 0 && $scantype == 'addscan' && ($posttableid < 1)) {
			cpmsg($toolslang[censor_noneedtoscan], "action=exttools&operation=$operation&do=tools&a=scanbbs", 'succeed');
		} elseif(DB::result_first("SELECT count(pid) from ".DB::table($posttable)." where pid >= $start and pid <= $end AND invisible = 0 $sqlplus") == 0 && $scantype == 'addscan') {
			$posttableid2 = $posttableid-1;
			cpmsg($toolslang[censor_jumpinto], "action=exttools&operation=$operation&do=tools&a=scanbbs&posttableid=$posttableid2&scantype=addscan&bbsscansubmit=yes", 'loading',array('id' => $posttableid));
		}
		
		$query = DB::query($sql);
		
		while($row =  DB::fetch($query)) {
			$pid = $row['pid'];
			$tid = $row['tid'];
			$subject = $row['subject'];
			$message = $row['message'];
			$first = $row['first'];
			$displayorder = 0;//  -2 MOD -1 Banned
			if(count($array_findmod) > 0) {
				foreach($array_findmod as $value) {
					if(preg_match($value,$subject.$message)) {
						$displayorder = '-2';
						break;
					}
				}
			}
			if(count($array_findbanned) > 0) {
				foreach($array_findbanned as $value) {
					if(preg_match($value,$subject.$message)) {
						$displayorder = '-1';
						break;
					}
				}
			}
			
			if($displayorder < 0) {
				if($displayorder == '-2' && $first == 0) {
					if(DB::affected_rows(DB::query("UPDATE ".DB::table($posttable)." SET invisible = '$displayorder' WHERE pid = $pid AND invisible >= 0")) > 0) {
						$xver >= 2 && updatemoderate('pid',$pid);
						$posts_mod ++;
					}
				} else {
					if(DB::affected_rows(DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder = '$displayorder' WHERE tid = $tid and displayorder >= 0")) > 0) {
						if($displayorder == '-2'){
							$threads_mod ++;
							$xver >= 2 && updatemoderate('tid',$tid);	
						}
						$displayorder == '-1' && $threads_banned ++;
					}
				}
			}
			$subject = preg_replace($array_find,$array_replace,addslashes($subject));
			$message = preg_replace($array_find,$array_replace,addslashes($message));
			if($subject != addslashes($row['subject']) || $message != addslashes($row['message'])) {
				if(DB::query("UPDATE ".DB::table($posttable)." SET subject = '$subject', message = '$message' WHERE pid = $pid")) {
					$convertedrows ++;
				}
			}
			$converted = 1;
	
		}
		
		$sql2 = "SELECT tid,subject from ".DB::table('forum_thread')." where tid >= $start and tid <= $end AND displayorder = 0 $sqlplus";
		$query2 = DB::query($sql2);
		while($row2 = DB::fetch($query2)) {
			$tid = $row2['tid'];
			$subject = $row2['subject'];
			$subject = preg_replace($array_find,$array_replace,addslashes($subject));
			if($subject != addslashes($row2['subject'])) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET subject = '$subject' WHERE tid = $tid");
			}
			$converted = 1;
		}
	
		if($converted  || $end < $maxid) {
			$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&maxid=$maxid&threads_mod=$threads_mod&threads_banned=$threads_banned&posts_mod=$posts_mod&convertedrows=$convertedrows&wordstart=$wordstart&wordmaxid=$wordmaxid&posttableid=$posttableid&bbsscansubmit=yes";
			cpmsg($toolslang[censor_scanstart], $nextlink, 'loading', array('start' => $start,'end' => $end,'wordstart' => $wordstart,'wordend' => $wordend,'posttableid' => $posttableid));
		} elseif($wordend < $wordmaxid) {
			$nextlink = "action=exttools&operation=$operation&do=tools&start=0&maxid=$maxid&threads_mod=$threads_mod&threads_banned=$threads_banned&posts_mod=$posts_mod&convertedrows=$convertedrows&wordstart=$wordend&wordmaxid=$wordmaxid&posttableid=$posttableid&bbsscansubmit=yes";
			cpmsg($toolslang[censor_scanstart], $nextlink, 'loading',array('start' => $start,'end' => $end,'wordstart' => $wordstart,'wordend' => $wordend,'posttableid' => $posttableid));
		} elseif(($posttableid > 0) &&($end >= $maxid || $wordend >= $wordmaxid)) {
			$posttableid2 = $posttableid - 1;
			$nextlink = "action=exttools&operation=$operation&do=tools&start=0&threads_mod=$threads_mod&threads_banned=$threads_banned&posts_mod=$posts_mod&convertedrows=$convertedrows&posttableid=$posttableid2&bbsscansubmit=yes";
			cpmsg($toolslang[censor_jumpposttable], $nextlink, 'loading',array('id' => $posttableid));
		} elseif($end >= $maxid || $wordend >= $wordmaxid) {
			$mod = $posts_mod + $threads_mod;
			$counter = $convertedrows + $mod + $threads_banned;
			$discuz_user = $_G['member']['username'];
			toolssetsetting('bbsltime',"$_G[timestamp]|$discuz_user|$counter|$convertedrows|$mod|$threads_banned");
			cpmsg($toolslang[censor_scanresult], "action=exttools&operation=$operation&do=tools&a=scanbbs", 'succeed',array('count' => $counter));
		}
	} elseif(submitcheck('blogscansubmit',1) || submitcheck('commentscansubmit',1) || submitcheck('doingscansubmit',1) || submitcheck('docommentsubmit',1)) {
		$rpp = '500';
		$convertedrows = isset($_G['gp_convertedrows']) ? $_G['gp_convertedrows'] : 0;
		$start = isset($_G['gp_start']) && $_G['gp_start'] > 0 ? $_G['gp_start'] : 0;
	
		$end = $start + $rpp - 1;
	
		
		$wordstart = isset($_G['gp_wordstart']) && $_G['gp_wordstart'] > 0 ? $_G['gp_wordstart'] : 0;
		$wordend =  $scaned ? ($scaned + $rpp -1) : ($wordstart + $rpp - 1);
		$maxid = isset($_G['gp_maxid']) ? $_G['gp_maxid'] : 0;
		$wordmaxid = isset($_G['gp_wordmaxid']) ? $_G['gp_wordmaxid'] : 0;
		
		$array_find = $array_replace = $array_findmod = $array_findbanned = array();
		if($wordmaxid == 0) {
			$result = DB::fetch_first("SELECT MIN(id) AS wordminid, MAX(id) AS wordmaxid FROM ".DB::table('common_word'));
			$wordstart = $result['wordminid'] ? $result['wordminid'] - 1 : 0;
			$wordmaxid = $result['wordmaxid'];
		}
	
		
		$wordextsql = "where id >= $wordstart AND id <= $wordend";
		$query = DB::query("SELECT find,replacement from ".DB::table('common_word')." $wordextsql");//获得现有规则{BANNED}放回收站 {MOD}放进审核列表
		while($row = DB::fetch($query)) {
			$find = preg_quote($row['find'], '/');
			$replacement = $row['replacement'];
			if($replacement == '{BANNED}') {
				$array_findbanned[] = $find;
			} elseif($replacement == '{MOD}') {
				$array_findmod[] = $find;
			} else {
				$array_find[] = $find;
				$array_replace[] = $replacement;
			}
		}
	
		$array_find = topattern_array($array_find);
		$array_findmod = topattern_array($array_findmod);
		$array_findbanned = topattern_array($array_findbanned);	
		
		if($_G['gp_blogscansubmit']){
			$table = array('home_blog','home_blogfield','blogid','message');
			$action = 'blogscansubmit';
			$type = 'blog';
		} elseif($_G['gp_commentscansubmit']) {
			$table = array('home_comment','','cid','message');
			$action = 'commentscansubmit';
			$type = 'comment';
		} elseif($_G['gp_doingscansubmit']) {
			$table = array('home_doing','','doid','message');
			$action = 'doingscansubmit';
			$type = 'doing';
		} elseif($_G['gp_docommentsubmit']) {
			$table = array('home_docomment','','id','message');
			$action = 'docommentsubmit';
			$type = 'docomment';
		}
		if($start == 0){
			DB::query("DELETE FROM ".DB::table('tools_censorhome')." WHERE type = '$type'");	
		}
		list($table1,$table2,$id,$content) = $table;
		$table1 = DB::table($table1);
		$table2 = $table2 ? DB::table($table2) : $table2;
		if($maxid == 0) {
			$result = DB::fetch_first("SELECT MIN($id) AS minid, MAX($id) AS maxid FROM ".$table1);
			$start = $result['minid'] ? $result['minid'] - 1 : 0;
			$maxid = $result['maxid'];
		}
		if($table2){
			$sql = "SELECT {$table1}.{$id},{$table2}.{$content} FROM ".$table1.",".$table2." WHERE {$table1}.{$id}={$table2}.{$id} AND {$table1}.{$id} >= $start and {$table1}.{$id} <= $end"; 	
		} else {
			$sql = "SELECT {$id},{$content} FROM ".$table1." WHERE {$id} >= $start and {$id} <= $end"; 	
		}
		$query = DB::query($sql);
		
		while($row =  DB::fetch($query)) {
			
			$id2 = $row[$id];
			$content2 = $row[$content];
	
			$displayorder = 0;//  -2 MOD -1 Banned
			if(count($array_findmod) > 0) {
				foreach($array_findmod as $value) {
					if(preg_match($value,$content2)) {
						$displayorder = '-2';
						break;
					}
				}
			}
			if(count($array_findbanned) > 0) {
				foreach($array_findbanned as $value) {
					if(preg_match($value,$content2)) {
						$displayorder = '-1';
						break;
					}
				}
			}
			
			if($displayorder < 0) {
				if(in_array($type,array('blog','comment'))){
					DB::query("REPLACE INTO ".DB::table('tools_censorhome')." (`itemid`, `type`) VALUES ('$id2','$type')");
				} else {
					DB::query("DELETE FROM ".$table1." WHERE $id = $id2");
				}
				$convertedrows ++;
			}
	
			$content2 = preg_replace($array_find,$array_replace,addslashes($content2));
			if($content2 != addslashes($row[$content])) {
				$table = $table2 ? $table2 : $table1;
				if(DB::query("UPDATE ".$table." SET {$content} = '$content2' WHERE {$id} = $id2")) {
					$convertedrows ++;
				}
			}
			$converted = 1;
		}
	
		if($converted  || $end < $maxid) {
			$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&maxid=$maxid&convertedrows=$convertedrows&wordstart=$wordstart&wordmaxid=$wordmaxid&{$action}=yes";
			cpmsg($toolslang['censor_homescanstart'], $nextlink, 'loading', array('start' => $start,'end' => $end,'wordstart' => $wordstart,'wordend' => $wordend,'posttableid' => $posttableid));
		} elseif($wordend < $wordmaxid) {
			$nextlink = "action=exttools&operation=$operation&do=tools&start=0&maxid=$maxid&convertedrows=$convertedrows&wordstart=$wordend&wordmaxid=$wordmaxid&{$action}=yes";
			cpmsg($toolslang['censor_homescanstart'], $nextlink, 'loading',array('start' => $start,'end' => $end,'wordstart' => $wordstart,'wordend' => $wordend,'posttableid' => $posttableid));
		} elseif($end >= $maxid || $wordend >= $wordmaxid) {
			cpmsg($toolslang['censor_scanresult'], "action=exttools&operation=$operation&do=tools&a=scanhome", 'succeed',array('count' => $convertedrows));
		}
	} elseif(submitcheck('articlescansubmit',1) || submitcheck('acommentscansubmit',1)) {
		$rpp = '500';
		$convertedrows = isset($_G['gp_convertedrows']) ? $_G['gp_convertedrows'] : 0;
		$start = isset($_G['gp_start']) && $_G['gp_start'] > 0 ? $_G['gp_start'] : 0;
		$end = $start + $rpp - 1;
	
		$wordstart = isset($_G['gp_wordstart']) && $_G['gp_wordstart'] > 0 ? $_G['gp_wordstart'] : 0;
		$wordend =  $scaned ? ($scaned + $rpp -1) : ($wordstart + $rpp - 1);
		$maxid = isset($_G['gp_maxid']) ? $_G['gp_maxid'] : 0;
		$wordmaxid = isset($_G['gp_wordmaxid']) ? $_G['gp_wordmaxid'] : 0;
		
		$array_find = $array_replace = $array_findmod = $array_findbanned = array();
		if($wordmaxid == 0) {
			$result = DB::fetch_first("SELECT MIN(id) AS wordminid, MAX(id) AS wordmaxid FROM ".DB::table('common_word'));
			$wordstart = $result['wordminid'] ? $result['wordminid'] - 1 : 0;
			$wordmaxid = $result['wordmaxid'];
		}
		
		$wordextsql = "where id >= $wordstart AND id <= $wordend";
		$query = DB::query("SELECT find,replacement from ".DB::table('common_word')." $wordextsql");//获得现有规则{BANNED}放回收站 {MOD}放进审核列表
		while($row = DB::fetch($query)) {
			$find = preg_quote($row['find'], '/');
			$replacement = $row['replacement'];
			if($replacement == '{BANNED}') {
				$array_findbanned[] = $find;
			} elseif($replacement == '{MOD}') {
				$array_findmod[] = $find;
			} else {
				$array_find[] = $find;
				$array_replace[] = $replacement;
			}
		}
	
		$array_find = topattern_array($array_find);
		$array_findmod = topattern_array($array_findmod);
		$array_findbanned = topattern_array($array_findbanned);	
		
		if($_G['gp_articlescansubmit']){
			$table = array('portal_article_title','portal_article_content','aid','content');
			$action = 'articlescansubmit';
			$type = 'article';
		} elseif($_G['gp_acommentscansubmit']) {
			$table = array('portal_comment','','cid','message');
			$action = 'acommentscansubmit';
			$type = 'acomment';
		}
		if($start == 0){
			DB::query("DELETE FROM ".DB::table('tools_censorhome')." WHERE type = '$type'");	
		}
		list($table1,$table2,$id,$content) = $table;
		$table1 = DB::table($table1);
		$table2 = $table2 ? DB::table($table2) : $table2;
		if($maxid == 0) {
			$result = DB::fetch_first("SELECT MIN($id) AS minid, MAX($id) AS maxid FROM ".$table1);
			$start = $result['minid'] ? $result['minid'] - 1 : 0;
			$maxid = $result['maxid'];
		}
		if($table2){
			$sql = "SELECT {$table1}.{$id},{$table2}.{$content} FROM ".$table1.",".$table2." WHERE {$table1}.{$id}={$table2}.{$id} AND {$table1}.{$id} >= $start and {$table1}.{$id} <= $end"; 	
		} else {
			$sql = "SELECT {$id},{$content} FROM ".$table1." WHERE {$id} >= $start and {$id} <= $end"; 	
		}
		$query = DB::query($sql);
		
		while($row =  DB::fetch($query)) {
			
			$id2 = $row[$id];
			$content2 = $row[$content];
	
			$displayorder = 0;//  -2 MOD -1 Banned
			if(count($array_findmod) > 0) {
				foreach($array_findmod as $value) {
					if(preg_match($value,$content2)) {
						$displayorder = '-2';
						break;
					}
				}
			}
			if(count($array_findbanned) > 0) {
				foreach($array_findbanned as $value) {
					if(preg_match($value,$content2)) {
						$displayorder = '-1';
						break;
					}
				}
			}
			
			if($displayorder < 0) {
				if(in_array($type,array('article','acomment'))){
					DB::query("REPLACE INTO ".DB::table('tools_censorhome')." (`itemid`, `type`) VALUES ('$id2','$type')");
				}
				$convertedrows ++;
			}
	
			$content2 = preg_replace($array_find,$array_replace,addslashes($content2));
			if($content2 != addslashes($row[$content])) {
				$table = $table2 ? $table2 : $table1;
				if(DB::query("UPDATE ".$table." SET {$content} = '$content2' WHERE {$id} = $id2")) {
					$convertedrows ++;
				}
			}
			$converted = 1;
		}
	
		if($converted  || $end < $maxid) {
			$nextlink = "action=exttools&operation=$operation&do=tools&start=$end&maxid=$maxid&convertedrows=$convertedrows&wordstart=$wordstart&wordmaxid=$wordmaxid&{$action}=yes";
			cpmsg($toolslang['censor_homescanstart'], $nextlink, 'loading', array('start' => $start,'end' => $end,'wordstart' => $wordstart,'wordend' => $wordend,'posttableid' => $posttableid));
		} elseif($wordend < $wordmaxid) {
			$nextlink = "action=exttools&operation=$operation&do=tools&start=0&maxid=$maxid&convertedrows=$convertedrows&wordstart=$wordend&wordmaxid=$wordmaxid&{$action}=yes";
			cpmsg($toolslang['censor_homescanstart'], $nextlink, 'loading',array('start' => $start,'end' => $end,'wordstart' => $wordstart,'wordend' => $wordend,'posttableid' => $posttableid));
		} elseif($end >= $maxid || $wordend >= $wordmaxid) {
			cpmsg($toolslang['censor_scanresult'], "action=exttools&operation=$operation&do=tools&pmod=censor&a=scanprotal", 'succeed',array('count' => $convertedrows));
		}
	}

	showformheader("exttools&operation=$operation&do=tools");
	$a = $_G['gp_a'];
	showsubmenu($toolslang['censor_ext'],array('manage' => array($toolslang['censor_admin'],"exttools&operation=$operation&do=tools",$a == ''),
	'scanbbs' => array($toolslang['censor_scanbbs'],"exttools&operation=$operation&do=tools&a=scanbbs",$a == 'scanbbs'),
	'scanhome' => array($toolslang['censor_scanhome'],"exttools&operation=$operation&do=tools&a=scanhome",$a == 'scanhome'),
	'scanprotal' => array($toolslang['censor_scanprotal'],"exttools&operation=$operation&do=tools&a=scanprotal",$a == 'scanprotal'),
	));
	
	if($a == 'scanbbs'){
		
		showtableheader($toolslang['censor_bbsinfo'],'censor');
		$log = toolsgetsetting('bbsltime');
		$logs = explode('|',$log);
		$bbsltime = $logs[0];
		$totalthreadcount = DB::result_first("SELECT count(tid) FROM ".DB::table('forum_thread'));
		$baththreadcount = DB::result_first("SELECT count(tid) FROM ".DB::table('forum_thread')." WHERE dateline >= '$bbsltime'");
		$posttablemaxid = DB::result_first("SELECT MAX(posttableid) FROM ".DB::table('forum_thread'));
		$id = 0;
		$postcount = 0;
		$totalcount= 0;
		while($id <= $posttablemaxid){
			if($id == 0){
				$totalpostcount = DB::result_first("SELECT count(pid) FROM ".DB::table('forum_post'));
				$bathpostcount = DB::result_first("SELECT count(pid) FROM ".DB::table('forum_post')." WHERE dateline >= '$bbsltime'");	
			} else {
				$totalpostcount = DB::result_first("SELECT count(pid) FROM ".DB::table('forum_post_'.$id));
				$bathpostcount = DB::result_first("SELECT count(pid) FROM ".DB::table('forum_post_'.$id)." WHERE dateline >= '$bbsltime'");	
			}
			$postcount = $postcount + $bathpostcount;
			$totalcount = $totalcount + $totalpostcount;
			$id ++;
		}
		
		showtablerow('', array('class="td21"'), array($toolslang['censor_threadcount'],$totalthreadcount));
		showtablerow('', array('class="td21"'), array($toolslang['censor_newthreadcount'],$baththreadcount));	
		showtablerow('', array('class="td21"'), array($toolslang['censor_postcount'],$totalcount));
		showtablerow('', array('class="td21"'), array($toolslang['censor_newpostcount'],$postcount));
		showtablefooter();
		
		showtableheader($toolslang['censor_scan']);
		showformheader('exttools&operation=$operation&do=tools&mod=scan');
		showsetting($toolslang['censor_scantype'],array('scantype',array(array('addscan',$toolslang['censor_addscan']),array('allscan',$toolslang['censor_allscan']))),'addscan','mradio','',0,$toolslang['censor_scantips']);
		showsubmit('bbsscansubmit', $toolslang['censor_beginscan']);
		showformfooter();
		showtablefooter();
		
		showtableheader($toolslang['censor_scanlog']);
		$logs[0] = date('Y-m-d',$logs[0]);
		echo "<tr><th>$toolslang[censor_scantime]</th><th>$toolslang[censor_scanuser]</th><th>$toolslang[censor_scancount]</th><th>$toolslang[censor_scanrep]</th><th>$toolslang[censor_scanmod]</th><th>$toolslang[censor_scanban]</th></tr>";
		showtablerow('','',$logs);
		showtablefooter();
	
	} elseif($a == 'scanhome') {
		showtableheader($toolslang['censor_homeinfo'],'censor');
	
		$totalblogcount = DB::result_first("SELECT count(blogid) FROM ".DB::table('home_blog'));
		$totalcommontcount = DB::result_first("SELECT count(cid) FROM ".DB::table('home_comment'));
		$totaldocommentcount = DB::result_first("SELECT count(id) FROM ".DB::table('home_docomment'));
		$totaldoingcount = DB::result_first("SELECT count(doid) FROM ".DB::table('home_doing'));
		
		showtablerow('', array('class="td21"'), array($toolslang['censor_blogcount'],$totalblogcount,"<input type='submit' value=$toolslang[censor_beginscan] title=$toolslang[censor_beginscan] name='blogscansubmit' class='btn'>"));
		showtablerow('', array('class="td21"'), array($toolslang['censor_commontcount'],$totalcommontcount,"<input type='submit' value=$toolslang[censor_beginscan] title=$toolslang[censor_beginscan] name='commentscansubmit' class='btn'>"));	
		showtablerow('', array('class="td21"'), array($toolslang['censor_doingcount'],$totaldoingcount,"<input type='submit' value=$toolslang[censor_beginscan] title=$toolslang[censor_beginscan] name='doingscansubmit' class='btn'>"));
		showtablerow('', array('class="td21"'), array($toolslang['censor_docommentcount'],$totaldocommentcount,"<input type='submit' value=$toolslang[censor_beginscan] title=$toolslang[censor_beginscan] name='docommentsubmit' class='btn'>"));
		showtablefooter();
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('tools_censorhome')." WHERE type IN ('blog','comment')");
		$multipage = multi($count, $ppp, $page, ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&a=scanhome");
	
		$query = DB::query("SELECT * FROM ".DB::table('tools_censorhome')." WHERE type IN ('blog','comment') LIMIT $startlimit, $ppp");
		showtableheader($toolslang['censor_homemod'],'censor');
		showtablerow('', array('class="td21"'), array($toolslang[censor_hometype],$toolslang[censor_homelink]));
		while($data = DB::fetch($query)){
			$datas['type'] = convtype($data['type'],$toolslang);	
			if($data['type'] == 'blog'){
				$datas['links'] = "<a href=$_G[siteurl]home.php?mod=space&do=blog&id={$data[itemid]} target=_blank>$_G[siteurl]home.php?mod=space&do=blog&id={$data[itemid]}</a>";
			} elseif($data['type'] == 'comment') {
				$datas['links'] = "<a href=$_G[siteurl]home.php?mod=spacecp&ac=comment&op=edit&cid={$data[itemid]} target=_blank>$_G[siteurl]home.php?mod=spacecp&ac=comment&op=edit&cid={$data[itemid]}</a>";
			}
			showtablerow('', '', $datas);
		}
		showtablerow('', '', array($multipage,''));
		showtablefooter();
		
		
	} elseif($a == 'scanprotal') {
		showtableheader($toolslang['censor_protalinfo'],'censor');
	
		$totalarticlecount = DB::result_first("SELECT count(aid) FROM ".DB::table('portal_article_title'));
		$totalcommontcount = DB::result_first("SELECT count(cid) FROM ".DB::table('portal_comment'));
		
		showtablerow('', array('class="td21"'), array($toolslang['censor_articlecount'],$totalarticlecount,"<input type='submit' value=$toolslang[censor_beginscan] title=$toolslang[censor_beginscan] name='articlescansubmit' class='btn'>"));
		showtablerow('', array('class="td21"'), array($toolslang['censor_acommontcount'],$totalcommontcount,"<input type='submit' value=$toolslang[censor_beginscan] title=$toolslang[censor_beginscan] name='acommentscansubmit' class='btn'>"));	
		showtablefooter();
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('tools_censorhome')." WHERE type IN ('article','acomment')");
		$multipage = multi($count, $ppp, $page, ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&a=scanhome");
	
		$query = DB::query("SELECT * FROM ".DB::table('tools_censorhome')." WHERE type IN ('article','acomment') LIMIT $startlimit, $ppp");
		showtableheader($toolslang['censor_homemod'],'censor');
		showtablerow('', array('class="td21"'), array($toolslang[censor_hometype],$toolslang[censor_homelink]));
		while($data = DB::fetch($query)){
			$datas['type'] = convtype($data['type'],$toolslang);	
			if($data['type'] == 'article'){
				$datas['links'] = "<a href=$_G[siteurl]portal.php?mod=view&aid={$data[itemid]} target=_blank>$_G[siteurl]portal.php?mod=view&aid={$data[itemid]}</a>";
			} elseif($data['type'] == 'acomment') {
				$datas['links'] = "<a href=$_G[siteurl]portal.php?mod=portalcp&ac=comment&op=edit&cid={$data[itemid]} target=_blank>$_G[siteurl]portal.php?mod=portalcp&ac=comment&op=edit&cid={$data[itemid]}</a>";
			}
			showtablerow('', '', $datas);
		}
		showtablerow('', '', array($multipage,''));
		showtablefooter();
		
	} else {	
		showtableheader($toolslang['censor_admin'],'censor');
		showsubmit('censorsercsubmit', $toolslang['censorsearch'], $toolslang['find'].' <input name="beforeword" value="" class="txt" />');
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_word')." w WHERE 1 $extrasql");
		$multipage = multi($count, $ppp, $page, ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&filter=$filter");
		echo "<td>".$toolslang['tips'].$count.
			$toolslang['filter'].
			"<a href='$BASESCRIPT?action=exttools&operation=$operation&do=tools&filter=banned'><font color=red>$toolslang[censor_banned]</font></a> ".
			"<a href='$BASESCRIPT?action=exttools&operation=$operation&do=tools&filter=mod'><font color=green>$toolslang[censor_mod]</font></a> ".
			"<a href='$BASESCRIPT?action=exttools&operation=$operation&do=tools&filter=replace'><font color=magenta>$toolslang[censor_re]</font></a> ".
			"<a href='$BASESCRIPT?action=exttools&operation=$operation&do=tools'>$toolslang[censor_all]</a>".
			"</td>";
		showtablefooter();
	
		showtableheader($toolslang['censor_view'], 'fixpadding');
		showsubtitle(array('', 'misc_censor_word', 'misc_censor_replacement', 'operator'));
		
		$query = DB::query("SELECT * FROM ".DB::table('common_word')." WHERE 1 $extrasql ORDER BY find ASC LIMIT $startlimit, $ppp");
		while($censor =	DB::fetch($query)) {
			$censor['replacement'] = dstripslashes($censor['replacement']);
			$censor['replacement'] = dhtmlspecialchars($censor['replacement']);
			$censor['find'] = dhtmlspecialchars($censor['find']);
			$disabled = $_G['adminid'] != 1 && $censor['admin'] != $_G['member']['username'] ? 'disabled' : NULL;
			if(in_array($censor['replacement'], array('{BANNED}', '{MOD}'))) {
				$replacedisplay = 'style="display:none"';
				$optionselected = array();
				foreach(array('{BANNED}', '{MOD}') as $option) {
					$optionselected[$option] = $censor['replacement'] == $option ? 'selected' : '';
				}
			} else {
				$optionselected['{REPLACE}'] = 'selected';
				$replacedisplay = '';
			}
			showtablerow('', array('class="td25"', '', '', 'class="td26"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$censor[id]\" $disabled>",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"find[$censor[id]]\" value=\"$censor[find]\" $disabled>",
				'<select name="replace['.$censor['id'].']" onchange="if(this.options[this.options.selectedIndex].value==\'{REPLACE}\'){$(\'divbanned'.$censor['id'].'\').style.display=\'\';$(\'divbanned'.$censor['id'].'\').value=\'\';}else{$(\'divbanned'.$censor['id'].'\').style.display=\'none\';}" '.$disabled.'>
				<option value="{BANNED}" '.$optionselected['{BANNED}'].'>'.cplang('misc_censor_word_banned').'</option><option value="{MOD}" '.$optionselected['{MOD}'].'>'.cplang('misc_censor_word_moderated').'</option><option value="{REPLACE}" '.$optionselected['{REPLACE}'].'>'.cplang('misc_censor_word_replaced').'</option></select>
				<input class="txt" type="text" size="10" name="replacecontent['.$censor['id'].']" value="'.$censor['replacement'].'" id="divbanned'.$censor['id'].'" '.$replacedisplay.' '.$disabled.'>',
				$censor['admin']
			));
		}
		$misc_censor_word_banned = cplang('misc_censor_word_banned');
		$misc_censor_word_moderated = cplang('misc_censor_word_moderated');
		$misc_censor_word_replaced = cplang('misc_censor_word_replaced');
		echo <<<EOT
		<script type="text/JavaScript">
		var rowtypedata = [
		[[1,''], [1,'<input type="text" class="txt" size="30" name="newfind[]">'], [1, ' <select onchange="if(this.options[this.options.selectedIndex].value==\'{REPLACE}\'){this.nextSibling.style.display=\'\';}else{this.nextSibling.style.display=\'none\';}" name="newreplace[]" $disabled><option value="{BANNED}">$misc_censor_word_banned</option><option value="{MOD}">$misc_censor_word_moderated</option><option value="{REPLACE}">$misc_censor_word_replaced</option></select><input class="txt" type="text" size="15" name="newreplacecontent[]" style="display:none;">'], [1,'']],
		];
		</script>
EOT;
		echo '<tr><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['add_new'].'</a></div></td></tr>';
		
		showsubmit('censorsubmit', 'submit', 'del', "<input type=hidden value=$page name=page />", $multipage);
		showtablefooter();
	}
	
	showformfooter();
} elseif($operation == 'ucenter') {
	$cp = in_array($_G['gp_cp'],array('synusername','clrnotice','synuid','clrfeed','pm','avator')) ? $_G['gp_cp'] : 'clrnotice';
	
	@include_once(DISCUZ_ROOT.'./config/config_ucenter.php');
	echo "<style>.floattopempty {height:20px !important;}</style>";
	if(!defined('UC_DBUSER')) {
		cpmsg($toolslang['uc_config_no_exist'],'','error');
	} elseif(UC_DBHOST != $_G[config][db][1][dbhost]) {
		cpmsg($toolslang['uc_config_no_db'],'','error');
	}
	
	$ppp = 100;
	$page = max(1, intval($_G['gp_page']));
	$startlimit = ($page - 1) * $ppp;
	
	if(in_array($cp,array('synusername','clrnotice','synuid','clrfeed','avator'))) {
		showtips($toolslang[$cp.'_tip']);
	}
	
	
	showsubmenu($toolslang['ucenter'],
		array(array($toolslang['clrnotice'],"exttools&operation=$operation&do=tools&cp=clrnotice",$cp == 'clrnotice'),
		array($toolslang['clrfeed'],"exttools&operation=$operation&do=tools&cp=clrfeed",$cp == 'clrfeed'),
		array($toolslang['synusername'],"exttools&operation=$operation&do=tools&cp=synusername",$cp == 'synusername'),
		array($toolslang['synuid'],"exttools&operation=$operation&do=tools&cp=synuid",$cp == 'synuid'),
		array($toolslang['uc_pm'],"exttools&operation=$operation&do=tools&cp=pm",$cp == 'pm'),
		array($toolslang['uc_avator'],"exttools&operation=$operation&do=tools&cp=avator",$cp == 'avator'),
	));
	
	$step=intval($_G['gp_step']);
	
	if($_G['gp_'.$cp.'_submit'] || $step>0){
		if($cp == 'synusername'){
			$step = intval($_G['gp_step']);
			$perpage = 1000;
			$count = isset($_G['gp_count']) ? $_G['gp_count'] : DB::result_first('SELECT count(uid) FROM '.UC_DBTABLEPRE.'members');
	
			$query = DB::query('SELECT uid,username FROM '.UC_DBTABLEPRE.'members limit '.($step*$perpage).','.$perpage);
			while($row = DB::fetch($query)){
				//print_r($row);exit;
				//DB::update('common_member',array('username' => daddslashes($row['username'])),'uid='.$row['uid']);
				//DB::update('forum_thread',array('author' => daddslashes($row['username'])),'authorid='.$row['uid']);
				$tables = array(
					'common_block' => array('id' => 'uid', 'name' => 'username'),
					'common_invite' => array('id' => 'fuid', 'name' => 'fusername'),
					'common_member' => array('id' => 'uid', 'name' => 'username'),
					'common_member_security' => array('id' => 'uid', 'name' => 'username'),
					'common_mytask' => array('id' => 'uid', 'name' => 'username'),
					'common_report' => array('id' => 'uid', 'name' => 'username'),
		
					'forum_thread' => array('id' => 'authorid', 'name' => 'author'),
					'forum_post' => array('id' => 'authorid', 'name' => 'author'),
					'forum_activityapply' => array('id' => 'uid', 'name' => 'username'),
					'forum_groupuser' => array('id' => 'uid', 'name' => 'username'),
					'forum_pollvoter' => array('id' => 'uid', 'name' => 'username'),
					'forum_postcomment' => array('id' => 'authorid', 'name' => 'author'),
					'forum_ratelog' => array('id' => 'uid', 'name' => 'username'),
		
					'home_album' => array('id' => 'uid', 'name' => 'username'),
					'home_blog' => array('id' => 'uid', 'name' => 'username'),
					'home_clickuser' => array('id' => 'uid', 'name' => 'username'),
					'home_docomment' => array('id' => 'uid', 'name' => 'username'),
					'home_doing' => array('id' => 'uid', 'name' => 'username'),
					'home_feed' => array('id' => 'uid', 'name' => 'username'),
					'home_feed_app' => array('id' => 'uid', 'name' => 'username'),
					'home_friend' => array('id' => 'fuid', 'name' => 'fusername'),
					'home_friend_request' => array('id' => 'fuid', 'name' => 'fusername'),
					'home_notification' => array('id' => 'authorid', 'name' => 'author'),
					'home_pic' => array('id' => 'uid', 'name' => 'username'),
					'home_poke' => array('id' => 'fromuid', 'name' => 'fromusername'),
					'home_share' => array('id' => 'uid', 'name' => 'username'),
					'home_show' => array('id' => 'uid', 'name' => 'username'),
					'home_specialuser' => array('id' => 'uid', 'name' => 'username'),
					'home_visitor' => array('id' => 'vuid', 'name' => 'vusername'),
		
					'portal_article_title' => array('id' => 'uid', 'name' => 'username'),
					'portal_comment' => array('id' => 'uid', 'name' => 'username'),
					'portal_topic' => array('id' => 'uid', 'name' => 'username'),
					'portal_topic_pic' => array('id' => 'uid', 'name' => 'username'),
				);
		
				foreach($tables as $table => $conf) {
					DB::query("UPDATE ".DB::table($table)." SET `$conf[name]`='".daddslashes($row['username'])."' WHERE `$conf[id]`='$row[uid]'");
				}
				$i++;
			}
			if(($step*$perpage) <= $count){
				cpmsg($step*$perpage,"action=exttools&operation=$operation&do=tools&count=".$count.'&step='.($step+1).'&cp='.$cp,'loading');
			}else{
				cpmsg($toolslang['success'],"action=exttools&operation=$operation&do=tools&cp=".$cp,'succeed');
			}
		} elseif($cp == 'synuid') {
			$frow=DB::result_first('SELECT MAX(uid) muid FROM '.DB::table('common_member'));
			$urow=DB::result_first('SELECT MAX(uid) muid FROM '.UC_DBTABLEPRE.'members');
	
			if($frow > $urow){
				$frow = $frow +1;
				DB::query("ALTER TABLE ".UC_DBTABLEPRE."members AUTO_INCREMENT = '$frow'");
			}
			cpmsg($toolslang['success'],"action=exttools&operation=$operation&do=tools&cp=".$cp,'succeed');
		} elseif($cp == 'clrnotice') {
			DB::query('delete from '.UC_DBTABLEPRE.'notelist');
			cpmsg($toolslang['success'],"action=exttools&operation=$operation&do=tools&cp=".$cp,'succeed');
		} elseif($cp == 'clrfeed') {
			$time = $_G['timestamp'] - 3*30*24*3600;
			DB::query("delete from ".UC_DBTABLEPRE."feeds WHERE dateline < $time");
			cpmsg($toolslang['success'],"action=exttools&operation=$operation&do=tools&cp=".$cp,'succeed');
		} elseif($cp == 'avator') {
			$start = $_G['gp_start'] ? $_G['gp_start'] : 0;
			$limit = 500;
			if($_G['gp_count']){
				$max = $_G['gp_count'];	
			} else {
				$max = DB::fetch_first("SELECT uid FROM ".DB::table('common_member')." ORDER BY uid DESC");
				$max = $max['uid'];
			}
	
			if($start >= $max){
				cpmsg($toolslang['uc_avatar_done'],"action=exttools&operation=$operation&do=tools&cp=$cp",'succeed');	
			}
			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE avatarstatus = '0' AND uid > '$start' LIMIT $limit");
			while($userinfo = DB::fetch($query)){
				$userinfos[] = $userinfo['uid'];	
			}
			foreach($userinfos as $value){
				loaducenter();
				$hasavatar = uc_check_avatar($value);
				DB::query("UPDATE ".DB::table('common_member')." SET avatarstatus = '$hasavatar' WHERE uid='$value'");
			}
			$nextstart = $start + $limit;
			cpmsg($toolslang['uc_avatar_jump'],"action=exttools&operation=$operation&do=tools&cp=$cp&start=$nextstart&max=$max&avator_submit=yes",'loading',array('start' => $start,'limit' => $limit));
		}
	} elseif(in_array($cp,array('synusername','clrnotice','synuid','clrfeed','avator'))) {
		showformheader("exttools&operation=$operation&do=tools&cp=".$cp);
		showtableheader();
		showsubmit($cp.'_submit','submit');
		showtablefooter();
		showformfooter();
	} elseif($cp == 'pm') {
		loaducenter();
		$uc_version = uc_check_version();
		if($uc_version[file] >='1.6' ){
			if($_G['gp_username']){
				$msgfromid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username = '$_G[gp_username]'");
			} else {
				$msgfromid = $_G['gp_msgfromid'] ? $_G['gp_msgfromid'] : '';	
			}
			
			
			if($_G['gp_clearpms']){
				$clearpms = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username = '$_G[gp_clearpms]'");
			} else {
				$clearpms = $_G['gp_clearpms'] ? $_G['gp_clearpms'] : '';	
			}
			
			showformheader("exttools&operation=$operation&do=tools&cp=pm",'submit');
			showtableheader($toolslang['uc_viewpm']);
			showtablerow('', array('class="td21"'), array(
				$toolslang['uc_viewusername'],
				'<input type="text" class="txt" name="username" value="'.dstripslashes($_G['gp_username']).'" /><input type="submit" class="btn" name="submit" value="'.$lang['submit'].'" />'
			));
			showtablerow('', array('class="td21"'), array(
				$toolslang['uc_clearpm'],
				'<input type="text" class="txt" name="clearpms" value="" /><input type="submit" class="btn" name="submit" value="'.$lang['submit'].'" />'
			));
			showtablefooter();
			showformfooter();
			//清理短消息
			if($clearpms) {
				DB::query("DELETE FROM ".UC_DBTABLEPRE."pm_lists WHERE authorid = $clearpms");
				$query = DB::query("SELECT plid FROM ".UC_DBTABLEPRE."pm_lists");
				while($plid = DB::fetch($query)){
					$plids[] = $plid['plid'];
				}
				DB::query("DELETE FROM ".UC_DBTABLEPRE."pm_indexes WHERE plid NOT IN (".dimplode($plids).")");
				DB::query("DELETE FROM ".UC_DBTABLEPRE."pm_members WHERE uid = $clearpms");
				
				$rows = 0;
				for($i=0;$i<=9;$i++){
					DB::query("DELETE FROM ".UC_DBTABLEPRE."pm_messages_".$i." WHERE authorid = $clearpms");
					$rows += DB::affected_rows();
				}
				cpmsg($rows,"action=exttools&operation=$operation&do=tools&cp=pm");
			}
			
			if($_G['gp_submit']){
				
				//echo "SELECT * FROM ".UC_DBTABLEPRE."pms $sqlplus GROUP BY dateline, msgtoid ORDER BY dateline DESC LIMIT $startlimit,$ppp";exit;
				if($_G['gp_uid'] && $_G['gp_touid']){
					$data = uc_pm_view($_G['gp_uid'],0,$_G['gp_touid'],5);
					//print_r($data);
					$data = array_reverse($data);
					showtableheader(dstripslashes($_G['gp_username']).$toolslang['uc_pmhis']);
					showsubtitle(array($toolslang['uc_pmfrom'],$toolslang['uc_pmtoer'],$toolslang['uc_pmcontent'],$toolslang['uc_pmtime']));
					foreach($data as $key => $value){
						$showdata[1] =  DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = $value[authorid]");
						$showdata[2] = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = $value[touid]");
						$showdata[3] =  $value['message'];
						$showdata[4] =  date('Y-m-d H:i',$value['dateline']);
						showtablerow('', array(), $showdata);
					}
					showtablerow('',array('class="td25"'),array('','','','',$multipage));
					showtablefooter();
				} else {
					$data = uc_pm_list($msgfromid,$page,$ppp,'inbox','privatepm','',100);
					$count = $data['count'];
					$multipage = multi($count, $ppp, $page, ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&cp=pm&username=$_G[gp_username]&submit=yes");

					$data = $data[data];
					//print_r($data);
					showtableheader(dstripslashes($_G['gp_username']).$toolslang['uc_pmhli']);
					showsubtitle(array($toolslang['uc_pmusername'],$toolslang['uc_relausername'],$toolslang['uc_pmlastcontent'],$toolslang['uc_pmtime']));
					if(is_array($data)){
						foreach($data as $key => $value){
							$showdata[1] =  DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = $value[uid]");
							$showdata[2] = $value['tousername'];
							$showdata[3] =  $value['message']."<a href=".ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&cp=pm&uid=$value[uid]&touid=$value[touid]&submit=yes> (detail)</a>";
							$showdata[4] =  date('Y-m-d H:i',$value['dateline']);
							showtablerow('', array(), $showdata);
						}
					}

					showtablerow('',array('class="td25"'),array('','','',$multipage));
					showtablefooter();
				}
				

			}

		} else {
			if($_G['gp_msgttoname']){
				$msgtoid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username = '$_G[gp_msgttoname]'");
			} else {
				$msgtoid = $_G['gp_msgtoid'] ? $_G['gp_msgtoid'] : '';	
			}
			$sqlplus = "WHERE 1 ";
			$msgtoid && $username1 = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = $msgtoid");
			$msgtoid && $sqlplus = "WHERE msgfrom != '$username1' ";
			if($_G['gp_username'] && $msgtoid){
				$sqlplus .= "AND msgfrom = '$_G[gp_username]' AND msgtoid = $msgtoid";
			} elseif($_G['gp_username']) {
				$fromuid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username = '$_G[gp_username]'");
				$sqlplus .= "AND msgfrom = '$_G[gp_username]' AND msgtoid != $fromuid";
			} elseif($msgtoid) {
				$tousername = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = $msgtoid");
				$sqlplus .= "AND msgtoid = $msgtoid";	
			} else {
				$sqlplus .= "";
			}
			
			if($_G['gp_clearpms']){
				$clearpms = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username = '$_G[gp_clearpms]'");
			} else {
				$clearpms = $_G['gp_clearpms'] ? $_G['gp_clearpms'] : '';	
			}
			
			showformheader("exttools&operation=$operation&do=tools&cp=pm",'submit');
			showtableheader($toolslang['uc_viewpm']);
			showtablerow('', array('class="td21"'), array(
				$toolslang['uc_viewsend'],
				'<input type="text" class="txt" name="username" value="'.dstripslashes($_G['gp_username']).'" /><input type="submit" class="btn" name="submit" value="'.$lang['submit'].'" />'
			));
			showtablerow('', array('class="td21"'), array(
				$toolslang['uc_viewto'],
				'<input type="text" class="txt" name="msgttoname" value="'.dstripslashes($_G['gp_msgttoname']).'" /><input type="submit" class="btn" name="submit" value="'.$lang['submit'].'" />'
			));
			showtablerow('', array('class="td21"'), array(
				$toolslang['uc_clearpm'],
				'<input type="text" class="txt" name="clearpms" value="" /><input type="submit" class="btn" name="submit" value="'.$lang['submit'].'" />'
			));
			showtablefooter();
			showformfooter();
			
			if($clearpms) {
				DB::query("DELETE FROM ".UC_DBTABLEPRE."pms WHERE msgfromid = $clearpms OR msgtoid = $clearpms");
				$rows = DB::affected_rows();
				cpmsg($rows,"action=exttools&operation=$operation&do=tools&cp=pm");
			}
			
			$count = DB::result_first("SELECT count(*) FROM ".UC_DBTABLEPRE."pms $sqlplus");	
			$multipage = multi($count, $ppp, $page, ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&cp=pm&username=$_G[gp_username]");
			//echo "SELECT * FROM ".UC_DBTABLEPRE."pms $sqlplus GROUP BY dateline, msgtoid ORDER BY dateline DESC LIMIT $startlimit,$ppp";exit;
			$pmss = DB::query("SELECT * FROM ".UC_DBTABLEPRE."pms $sqlplus AND related = 1 ORDER BY msgtoid DESC LIMIT $startlimit,$ppp");
			showtableheader(dstripslashes($_G['gp_username']).$toolslang['uc_pmhis']);
			showsubtitle(array($toolslang['uc_pmfrom'],$toolslang['uc_pmtoer'],$toolslang['uc_pmcontent'],$toolslang['uc_pmtime']));
			while($data = DB::fetch($pmss)){
				//$showdata[] =  $data['pmid'];
				$showdata[1] =  "<a href=".ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&cp=pm&username=$data[msgfrom]>".$data['msgfrom']."</a>";
				if(!$data['msgfrom']) {$showdata[1] = 'SYSTEM';}
				$showdata[2] = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid = $data[msgtoid]");
				$showdata[3] =  $data['message'];
				$showdata[4] =  date('Y-m-d H:i',$data['dateline']);
				showtablerow('', array(), $showdata);
			}
			showtablerow('',array('class="td25"'),array('','','','',$multipage));
			showtablefooter();
		}
	}	
} elseif($operation == 'safe') {
	$rule = get_rule();
	echo "<style>.floattopempty {height:20px !important;}</style>";
	$a = $_G['gp_a'];
	$a = $a ? $a : 'php';
	showformheader("exttools&operation=$operation&do=tools&a=$a");
	showsubmenu($lang[$menuname],array('php' => array($toolslang['file_php'],"exttools&operation=$operation&do=tools&a=php",$a == 'php'),
	'hack' => array($toolslang['file_hack'],"exttools&operation=$operation&do=tools&a=hack",$a == 'hack'),
	'search' => array($toolslang['file_search'],"exttools&operation=$operation&do=tools&a=search",$a == 'search'),
	'changekey' => array($toolslang['changekey'],"exttools&operation=$operation&do=tools&a=changekey",$a == 'changekey'),
	));
	
	
	if(submitcheck('templatesubmit') || submitcheck('attsubmit') || submitcheck('staticsubmit') || submitcheck('othersubmit')) {
		$filelist = '';
		if($_G['gp_templatesubmit']) {
			findfile('./template',array('php'));
		} elseif($_G['gp_attsubmit']) {
			findfile('./data/attachment',array('php'));	
		} elseif($_G['gp_staticsubmit']) {
			findfile('./static',array('php'));	
		} elseif($_G['gp_othersubmit']) {
			findfile('./data',array('php'),array('attachment','template','threadcache','request','cache','log','plugindata'));
		}
	}
	
	if(submitcheck('firsthacksubmit') || submitcheck('sechacksubmit')){
		$check = '';
		if($_G['gp_firsthacksubmit']) {
			$rule2 = $rule['first'];
			searchkeyword($rule2,'./',1,array('attachment','template'),1);	
		} elseif($_G['gp_sechacksubmit']) {
			$rule2 = $rule['sec'];
			searchkeyword($rule2,'./',1,array('attachment','template'),1);			
		}
		
		if(is_array($check) && count($check) > 0) {
			showtableheader($toolslang['file_result']."<font color=red>$rule2</font>");
			showsubtitle(array('', $toolslang['file_realpath'],$toolslang['file_hackresult']));
			foreach($check as $key => $value){
				if($value){
					showtablerow('', array(), array('',$key,$value));	
				}
			}
			showtablefooter();
		} else {
			cpmsg($toolslang['nocheck'],"action=exttools&operation=$operation&do=tools&a=$a",'error');	
		}
	}

	if(submitcheck('updatesubmit')){
		$newrule = file('http://www.discuz.net/forum.php?mod=attachment&aid=NzQ5ODMxfDUzOTA3YTY4fDEyNzkyNjA3Njd8MTA1NjM4Mw%3D%3D');
		foreach($newrule as $value) {
			$newarray = explode('........',$value);
			DB::insert('tools_rule',array('name' => $newarray[0],'rule' => daddslashes($newarray[1])),0,1,1);	
		}
	}
	
	if(submitcheck('keysubmit')){
		$cpmessage = '';
		$localuc = 0;
		if(file_exists(DISCUZ_ROOT.'./uc_server/data/config.inc.php')){
			require_once DISCUZ_ROOT.'./uc_server/data/config.inc.php';
			$localuc = 1;
		}
		@loaducenter();
		//require_once DISCUZ_ROOT.'./uc_client/client.php';
		
		$key = array('uc_key','config_authkey','setting_authkey','my_sitekey'); // UCenter通信KEY   Discuz! 安全KEY  Discuz!加密解密key  漫游KEY
		foreach($key as $value){
			if($value == 'uc_key'){
				//echo $localuc;exit;
				if(strexists(UC_API,$_G['siteurl']) && $localuc == 1){ //local ucenter
					$newuc_mykey = UC_MYKEY;              //更新到UCenter配置文件
					$newuc_uckey = UC_KEY;            //更新到UCenter配置文件
					$newapp_authkey = generate_key();           //更新到 Discuz! UC配置文件
					$newapp_appkey = authcode($newapp_authkey,'ENCODE',$newuc_mykey);   //更新到UCenter数据库
					$newapp_appkey = daddslashes($newapp_appkey);
					//echo $newcu_mykey;exit;
					$ucdb = new db_mysql();
					$ucdblink = $ucdb->_dbconnect(UC_DBHOST,UC_DBUSER,UC_DBPW,UC_DBCHARSET,UC_DBNAME);
					$apptablename = UC_DBTABLEPRE.'applications';
//					$a = $ucdb->query("SELECT appid,authkey FROM $apptablename");
//					$apparray = array();
//					while($data = $ucdb->fetch_array($a)){
//						$apparray[] = $data;
//					}
					//echo UC_DBTABLEPRE;exit;
					$uc_dbtablepre = UC_DBTABLEPRE;
					$ucconfig = array($newapp_authkey,UC_APPID,UC_DBHOST,UC_DBNAME,UC_DBUSER,UC_DBPW,UC_DBCHARSET,$uc_dbtablepre,UC_CHARSET,UC_API,UC_IP);
					$ucconfig = @implode('|',$ucconfig);
					save_uc_config($ucconfig,DISCUZ_ROOT.'./config/config_ucenter.php');
					$ucdb->query("UPDATE $apptablename SET authkey = '$newapp_appkey' WHERE appid = ".UC_APPID);
					//note
				} else {
					$cpmessage .= $toolslang['nlocaluc'];
				}
				// $authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$username.$password.$pconnect.substr($timestamp, 0, 6)), 8, 6).random(10);	
			} elseif($value == 'config_authkey') {
				$default_config = $_config;
				$authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$username.$password.$pconnect.substr($timestamp, 0, 8)), 8, 6).random(10);
				$_config['security']['authkey'] = $authkey;
				$cpmessage .= $toolslang['resetauthkey'];
				save_config_file('./config/config_global.php', $_config, $default_config);
			} elseif($value == 'setting_authkey') {
				$authkey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].$dbhost.$dbuser.$dbpw.$dbname.$username.$password.$pconnect.substr($timestamp, 0, 8)), 8, 6).random(10);
				DB::update('common_setting',array('svalue' => $authkey),"skey = 'authkey'");
			} elseif($value == 'my_sitekey' && $xver >= 2) {
				require_once DISCUZ_ROOT.'/api/manyou/Manyou.php';
				$cloudClient = new Discuz_Cloud_Client();
				$res = $cloudClient->resetKey();
				if(!$res) {
					$cpmessage .= $toolslang['mykeyerror'];
				} else {
					$sId = $res['sId'];
					$sKey = $res['sKey'];
					DB::query("REPLACE INTO ".DB::table('common_setting')." (`skey`, `svalue`)
								VALUES ('my_siteid', '$sId'), ('my_sitekey', '$sKey'), ('cloud_status', '1')");
				}
			}
		}
		updatecache('setting');
		cpmsg($toolslang['changekey_update'].$cpmessage,"action=exttools&operation=$operation&do=tools&a=$a",'succeed');
	}
	if($a == 'search') {
		if(!submitcheck('search')) {
			showtableheader($toolslang['file_search']);
			$dirlist[] = array('.','./');
			getdirentry('./');
			
			$showlist = array('sdir[]',$dirlist);
			showsetting($toolslang['file_keyword'],'keyword','','text','',0,$toolslang['file_keywordtip']);
			showsetting($toolslang['file_searchdir'],$showlist,'','mselect','',0,$toolslang['file_searchdirtip']);
			showsubmit('search');
			showtablefooter();
		} else {
			if(empty($_G['gp_keyword'])){
				cpmsg($toolslang['file_nokeyword'],"action=exttools&operation=$operation&do=tools&a=$a",'error');
				exit;	
			}
			if(empty($_G['gp_sdir'])){
				cpmsg($toolslang['file_nodir'],"action=exttools&operation=$operation&do=tools&a=$a",'error');
				exit;
			}
			$_G['gp_keyword'] = str_replace('*','(.*)',$_G['gp_keyword']);
			$keyword = strtolower(dstripslashes($_G['gp_keyword']));
			$dir = $_G['gp_sdir'];
			$check = '';
			$keyword2 = str_replace(array('.','/','$','(',')','?','{','}','|','+','[',']','^'),array('\.','\/','\$','\(','\)','\?','\{','\}','\|','\+','\[','\]','\^'),$keyword);
			
			foreach($dir as $value){
				$sub = $value == '.' ? 0 : 1;
				//echo $value;exit;
				searchkeyword($keyword2,$value.'/',$sub);
			}
			
			if(is_array($check) && count($check) > 0) {
				showtableheader($toolslang['file_result']."<font color=red>$keyword</font>");
				showsubtitle(array('', $toolslang['file_realpath'],$toolslang['file_keyrows']));
				foreach($check as $key => $value){
					if($value){
						showtablerow('', array(), array('',$key,$value));	
					}
				}
				showtablefooter();
			} else {
				cpmsg($toolslang['nocheck'],"action=exttools&operation=$operation&do=tools&a=$a",'error');
			}
		}	
	} elseif($a == 'php') {
		showtips($toolslang['file_phptip']);
		showtableheader($toolslang['file_php']);
		showsubmit('templatesubmit','submit',$toolslang['template_php']);
		showsubmit('attsubmit','submit',$toolslang['attachment_php']);
		showsubmit('staticsubmit','submit',$toolslang['static_php']);
		showsubmit('othersubmit','submit',$toolslang['other_php']);
		showtablefooter();
	} elseif($a == 'hack') {
		showtips($toolslang['file_hacktip']);
		showtableheader($toolslang['file_hack']);
		foreach($rule as $key => $value){
			showsubmit($key.'hacksubmit','submit',$value);
		}
		showtablefooter();
		
//		showtableheader($toolslang['file_hackupdate']);
//			showsubmit('updatesubmit','submit',$toolslang['file_hackupdate']);
//		showtablefooter();
	} elseif($a == 'changekey') {
		loaducenter();
		showtips($toolslang['changekey_tips']);
		showtableheader($toolslang['changekey']);
		$uckey = substr(UC_KEY,0,5).'**********';
		$config_authkey = substr($_config['security']['authkey'],0,5).'**********';
		$setting_authkey = substr($_G[setting][authkey],0,5).'**********';
		$my_sitekey = substr($_G[setting][my_sitekey],0,5).'**********';
		showtablerow('','',$toolslang['nowuc_key'].' : '.$uckey);
		showtablerow('','',$toolslang['nowconfig_authkey'].' : '.$config_authkey);
		showtablerow('','',$toolslang['nowmy_sitekey'].' : '.$my_sitekey);
		showsubmit('keysubmit',$toolslang['changekey']);
		showtablefooter();
	}
	
	if(is_array($filelist) && count($filelist) > 0){
		showtableheader($toolslang['file_php_result']);
		showsubtitle(array('', $toolslang['file_path']));
		foreach($filelist as $value) {
			showtablerow('',array(),array('',realpath($value)));	
		}
		showtablefooter();	
	}
	showformfooter();
} elseif($operation == 'pw') {
	if(!isfounder()){
		cpmsg_error('tools:noperm');
	}
	
	require_once DISCUZ_ROOT.'./source/plugin/tools/function/tools.func.php';
	echo '<br/>';
	if(submitcheck('submit')){
		if($_G['gp_newtoolspw'] == ''){
			cpmsg('tools:emptypw',"action=exttools&operation=$operation&do=tools",'succeed');
		}
		if($_G['gp_newtoolspw'] != 'n*o*m*o*d*i*f*y'){
			$newpass = md5($_G['gp_newtoolspw']);
			DB::insert('common_setting',array('skey'=>'toolspw','svalue'=>$newpass),'','1');
		}
		
		if($_G['gp_filelock'] == 'yes'){
			$keyname = substr(md5(rand(9,100000)."DISCUZ X"."TOOLS"),6,10);
			DB::insert('common_setting',array('skey'=>'toolskey','svalue'=>$keyname),'','1');
			cpmsg_error($toolslang['keyname'].$keyname);
		} else {
			DB::delete('common_setting','skey=\'toolskey\'');
		}
		cpmsg('tools:success',"action=exttools&operation=$operation&do=tools",'succeed');
	}
	
	$filelock = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey = 'toolskey'") ? 'yes' : 'no';
	showformheader("exttools&operation=$operation&do=tools",'submit');
	showtableheader($toolslang['file']);
	showsetting($toolslang['iffilelock'],array('filelock',array(array('yes',$toolslang['use']),array('no',$toolslang['nouse']))),$filelock,'mradio','',0,$toolslang['filetips']);
	showtablefooter();
	
	showtableheader($toolslang['pass']);
	showsetting($toolslang['password'],'newtoolspw','n*o*m*o*d*i*f*y','password','',0,$toolslang['toostips']);
	showtablefooter();
	showsubmit('submit', $toolslang['submit']);
	showformfooter();	
} elseif($operation == 'motion') {
	
	if(submitcheck('motion_viewsubmit')) {
		if(!is_num($_G['gp_tid']) || !is_num($_G['gp_views'])){
			cpmsg($toolslang['motion_error'],"action=exttools&operation=$operation&do=tools",'error');	
		}
		$_G['gp_tid'] = intval($_G['gp_tid']);
		$_G['gp_views'] = intval($_G['gp_views']);
		$threadtalbe = getallthreadtable();
		$tid = 0;
		foreach($threadtalbe as $value){
			$temptid = DB::result_first("SELECT tid FROM ".DB::table($value)." WHERE tid='".$_G['gp_tid']."'");
			if($temptid > 0){
				$thread = $value;	
			}
			$tid = ($tid || $temptid);
		}
		if(!$tid){
			cpmsg($toolslang['motion_emptytid'],"action=exttools&operation=$operation&do=tools",'error');	
		}
		DB::update($thread,array('views' => $_G['gp_views']),"tid = $_G[gp_tid]");
		cpmsg($toolslang['motion_success'],"action=exttools&operation=$operation&do=tools",'succeed');	
	} elseif(submitcheck('motion_hispostsubmit')) {
		if(!is_num($_G['gp_hispost']) || !is_num($_G['gp_fid'])){
			cpmsg($toolslang['motion_hiserror'],"action=exttools&operation=$operation&do=tools",'error');	
		}
		$_G['gp_hispost'] = intval($_G['gp_hispost']);
		$_G['gp_fid'] = intval($_G['gp_fid']);
		$fidcount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_forum')." WHERE fid = $_G[gp_fid]");
		if($fidcount == 0){
			cpmsg($toolslang['motion_nofid'],"action=exttools&operation=$operation&do=tools",'error');	
		} else {
			DB::update('forum_forum',array('todayposts' => "$_G[gp_hispost]"),"fid = $_G[gp_fid]");
			cpmsg($toolslang['motion_success'],"action=exttools&operation=$operation&do=tools",'succeed');	
		}
	}
	
	showformheader("exttools&operation=$operation&do=tools",'submit');
	showtableheader($toolslang['motion_threadclick']);
	showsetting($toolslang['motion_tid'],'tid','','text');
	showsetting($toolslang['motion_views'],'views','','text');
	showsubmit('motion_viewsubmit', $toolslang['submit']);
	showtablefooter();
	//historyposts
	showtableheader($toolslang['motion_hispost']);
	showsetting($toolslang['motion_forumfid'],'fid','','text');
	showsetting($toolslang['motion_forumpost'],'hispost','','text');
	showsubmit('motion_hispostsubmit', $toolslang['submit']);
	showtablefooter();
	showformfooter();	
} elseif($operation == 'att') {
	
	if(submitcheck('att_clean_submit')){
		if(count($_G['gp_attarray']) <= 0) {
			cpmsg($toolslang['clearatt_noselect'],NULL,'error');	
		} else {
			foreach($_G['gp_attarray'] as $value){
				@unlink(DISCUZ_ROOT.'/data/attachment/'.$value);	
				@unlink(DISCUZ_ROOT.'/data/attachment/'.$value.'.thumb.jpg');
			}
			cpmsg($toolslang['clearatt_done'],"action=exttools&operation=$operation&do=tools",'succeed');	
		}
	}

	if(submitcheck('att_submit')){
		set_time_limit(0);
		if(function_exists(ini_set)){
			ini_set('memory_limit','256M');
		}		
		
		$dlist = array();
		$dir = $_G['gp_dir'];
		$mod = preg_match('/(album|forum|portal)/im', $dir,$match);
		$mod = $match[0];
		$att = '';
		dlist($dir,intval($xver));
		if(count($dlist) <= 0) {
			cpmsg($toolslang['clearatt_nolaji'],"action=exttools&operation=$operation&do=tools",'error');	
		}
		foreach($dlist as $key => $value){
			$att .= showtablerow('', array('class="td25"', ''), array(
					"<input type=\"checkbox\" name=\"attarray[]\" value=\"$value\" class=\"checkbox\">",
					"<a href=\"data/attachment/{$mod}/{$key}\" target=\"_blank\">$value</a>",
				), TRUE);
		}
		showformheader("exttools&operation=$operation&do=tools");
		showtableheader($toolslang['clearatt_lajiatt']);
		echo $att;
		showsubmit('att_clean_submit', 'submit', '<input type="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'attarray\')" class="checkbox">'.cplang('del'), '');
		showtablefooter();
		showformfooter();
	} else {
		if(intval($xver) >= 2){
			$tablearray = array('forum_attachment_0' => 'attachment',
			'forum_attachment_1' => 'attachment',
			'forum_attachment_2' => 'attachment',
			'forum_attachment_3' => 'attachment',
			'forum_attachment_4' => 'attachment',
			'forum_attachment_5' => 'attachment',
			'forum_attachment_6' => 'attachment',
			'forum_attachment_7' => 'attachment',
			'forum_attachment_8' => 'attachment',
			'forum_attachment_9' => 'attachment',
			'home_pic' => 'filepath',
			'portal_attachment' => 'attachment');
		} else {
			$tablearray = array('forum_attachment' => 'attachment',
			'home_pic' => 'filepath',
			'portal_attachment' => 'attachment');	
		}
		foreach($tablearray as $key => $value){
			checkattindex($key,$value);	
		}
		
		$dirlist = array();
		foreach(glob(DISCUZ_ROOT."/data/attachment/portal/*",GLOB_ONLYDIR) as $dirname){
			$dirlist[] = array($dirname,str_replace(DISCUZ_ROOT.'/data/attachment/','',$dirname));		
		}
		foreach(glob(DISCUZ_ROOT."/data/attachment/album/*",GLOB_ONLYDIR) as $dirname){
			if(strpos($dirname,'cover') === false){
				$dirlist[] = array($dirname,str_replace(DISCUZ_ROOT.'/data/attachment/','',$dirname));	
			}
		}
		foreach(glob(DISCUZ_ROOT."/data/attachment/forum/*",GLOB_ONLYDIR) as $dirname){
			$dirlist[] = array($dirname,str_replace(DISCUZ_ROOT.'/data/attachment/','',$dirname));		
		}

		showformheader("exttools&operation=$operation&do=tools",'submit');
		showtips($toolslang['clearatt']);
		showtableheader();
		showsetting('dir',array('dir',$dirlist),'','select','','');
		showsubmit('att_submit', 'submit');
		showtablefooter();
		showformfooter();
	}
} elseif($operation == 'convert') {
	$dbhost = $_G['config']['db']['1']['dbhost'];
	$dbuser = $_G['config']['db']['1']['dbuser'];
	$dbpw = $_G['config']['db']['1']['dbpw'];
	$dbcharset = $_G['config']['db']['1']['dbcharset'];
	$dbname = $_G['config']['db']['1']['dbname'];
	$tablepre = $_G['config']['db']['1']['tablepre'];
	$allowchar = array('gbk' => 'utf8','utf8' => 'gbk');
	$todbcharset = $allowchar[$dbcharset];
	$tableno = $_G['gp_tableno'];
	$cdo = $_G['gp_cdo'];
	$start = $_G['gp_start'];
	!$tableno && $tableno = 0;
	!$cdo && $cdo = 'create';
	!$start && $start = 0;
	$limit = 2000;
	if($_G['gp_convert_submit']){
		do_datago($tableno,$cdo,$start,$limit);
	}
	echo "<div id=serialize></div>";
	showformheader("exttools&operation=$operation&do=tools");
	showtableheader($toolslang['convert_scharset']);
	showtablerow('', array('class="td21"'), array($toolslang[convert_dbbase],$dbname));
	showtablerow('', array('class="td21"'), array($toolslang[convert_curcharset],$dbcharset));
	showtablerow('', array('class="td21"'), array($toolslang[convert_tocharset],$todbcharset));
	showsubmit('convert_submit', 'submit');
	showtablefooter();
	showformfooter();
} elseif($operation == 'house') {
	if(!isfounder()){
		cpmsg_error('tools:noperm');
	}
	$db = & DB::object();
	$tabletype = $db->version() > '4.1' ? 'Engine' : 'Type';
	//房产数据表前缀
	$tablepre = $_G['config']['db'][1]['tablepre'].'category_';
	$dbcharset = $_G['config']['db'][1]['dbcharset'];

	$backupdir = 'house';
	$backupdir = 'backup_'.$backupdir;
	if(!is_dir('./data/'.$backupdir)) {
		mkdir('./data/'.$backupdir, 0777);
	}
	$cp = in_array($_G['gp_cp'],array('export','import','importzip')) ? $_G['gp_cp'] : 'export';
	if($cp == 'export') {
		if(!submitcheck('exportsubmit', 1)) {
	
			$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';
	
			$tables = '';
			$dztables = array();
			if($tables = $custombackup) {
				$tables = unserialize($tables['svalue']);
				$tables = is_array($tables) ? $tables : '';
			}
	
			$discuz_tables = fetchtablelist($tablepre);
	
			foreach($discuz_tables as $table) {
				$dztables[$table['Name']] = $table['Name'];
			}
	
			$defaultfilename = date('ymd').'_'.random(8);
	
			showsubmenu($menuname, array(
				array('nav_db_export', "exttools&operation=$operation&do=tools&cp=export", 1),
				array('nav_db_import', "exttools&operation=$operation&do=tools&cp=import", 0),	
			));
			
			showformheader("exttools&operation=$operation&do=tools&setup=1");
			showtableheader($toolslang['house_export']);
			showsetting('db_export_type', array('type', array(
				array('discuz', $lang['db_export_discuz'], array('showtables' => 'none')),
			)), 'discuz', 'mradio');
	
	
			showtagheader('tbody', 'showtables');
			showtablerow('', '', '<input class="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'customtables\', \'chkall\', true)" checked="checked" type="checkbox" id="chkalltables" /><label for="chkalltables"> '.cplang('db_export_custom_select_all').' - '.cplang('db_export_discuz_table')).'</label>';
			showtablerow('', 'colspan="2"', mcheckbox('customtables', $dztables));
			showtagfooter('tbody');
	
			showtagheader('tbody', 'advanceoption');
			showsetting('db_export_method', '', '', '<ul class="nofloat"><li><input class="radio" type="radio" name="method" value="multivol" checked="checked" onclick="this.form.sqlcompat[2].disabled=false; this.form.sizelimit.disabled=false; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=false;}" id="method_multivol" /><label for="method_multivol"> '.$lang['db_export_multivol'].'</label> <input type="text" class="txt" size="40" name="sizelimit" value="2048" /></li></ul>');
			showtitle('db_export_options');
			showsetting('db_export_options_extended_insert', 'extendins', 0, 'radio');
			showsetting('db_export_options_sql_compatible', array('sqlcompat', array(
				array('', $lang['default']),
				array('MYSQL40', 'MySQL 3.23/4.0.x'),
				array('MYSQL41', 'MySQL 4.1.x/5.x')
			)), '', 'mradio');
			showsetting('db_export_options_charset', array('sqlcharset', array(
				array('', cplang('default')),
				$dbcharset ? array($dbcharset, strtoupper($dbcharset)) : array(),
				$db->version() > '4.1' && $dbcharset != 'utf8' ? array('utf8', 'UTF-8') : array()
			), TRUE), 0, 'mradio');
			showsetting('db_export_usehex', 'usehex', 1, 'radio');
			if(function_exists('gzcompress')) {
				showsetting('db_export_usezip', array('usezip', array(
					array('1', $lang['db_export_zip_1']),
					array('2', $lang['db_export_zip_2']),
					array('0', $lang['db_export_zip_3'])
				)), 0, 'mradio');
			}
			showsetting('db_export_filename', '', '', '<input type="text" class="txt" name="filename" value="'.$defaultfilename.'" />.sql');
			showtagfooter('tbody');
	
			showsubmit('exportsubmit', 'submit', '', 'more_options');
			showtablefooter();
			showformfooter();
	
		} else {
	
			DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
	
			if(!$_G['gp_filename'] || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $_G['gp_filename'])) {
				cpmsg('database_export_filename_invalid', '', 'error');
			}
	
			$time = dgmdate(TIMESTAMP);
			if($_G['gp_type'] == 'discuz') {
				$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
			}
	
			$volume = intval($_G['gp_volume']) + 1;
			$idstring = '# Identify: '.base64_encode("$_G[timestamp],".$_G['setting']['version'].",{$_G['gp_type']},{$_G['gp_method']},{$volume}")."\n";
	
	
			$dumpcharset = $_G['gp_sqlcharset'] ? $_G['gp_sqlcharset'] : str_replace('-', '', $_G['charset']);
			$setnames = ($_G['gp_sqlcharset'] && $db->version() > '4.1' && (!$_G['gp_sqlcompat'] || $_G['gp_sqlcompat'] == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
			if($db->version() > '4.1') {
				if($_G['gp_sqlcharset']) {
					DB::query("SET NAMES '".$_G['gp_sqlcharset']."';\n\n");
				}
				if($_G['gp_sqlcompat'] == 'MYSQL40') {
					DB::query("SET SQL_MODE='MYSQL40'");
				} elseif($_G['gp_sqlcompat'] == 'MYSQL41') {
					DB::query("SET SQL_MODE=''");
				}
			}
	
			$backupfilename = './data/'.$backupdir.'/'.str_replace(array('/', '\\', '.'), '', $_G['gp_filename']);
	
			if($_G['gp_usezip']) {
				require_once './source/class/class_zip.php';
			}
	
			if($_G['gp_method'] == 'multivol') {
	
				$sqldump = '';
				$tableid = intval($_G['gp_tableid']);
				$startfrom = intval($_G['gp_startfrom']);
	
				if(!$tableid) {
					foreach($tables as $table) {
						$sqldump .= sqldumptablestruct($table);
					}
				}
				$complete = TRUE;
				for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $_G['gp_sizelimit'] * 1000; $tableid++) {
					$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
					if($complete) {
						$startfrom = 0;
					}
				}
	
				$dumpfile = $backupfilename."-%s".'.sql';
				!$complete && $tableid--;
				if(trim($sqldump)) {
					$sqldump = "$idstring".
						"# <?exit();?>\n".
						"# Discuz! Multi-Volume Data Dump Vol.$volume\n".
						"# Version: Discuz! {$_G[setting][version]}\n".
						"# Time: $time\n".
						"# Type: {$_G['gp_type']}\n".
						"# Table Prefix: $tablepre\n".
						"#\n".
						"# Discuz! Home: http://www.discuz.com\n".
						"# Please visit our website for newest infomation about Discuz!\n".
						"# --------------------------------------------------------\n\n\n".
						"$setnames".
						$sqldump;
					$dumpfilename = sprintf($dumpfile, $volume);
					@$fp = fopen($dumpfilename, 'wb');
					@flock($fp, 2);
					if(@!fwrite($fp, $sqldump)) {
						@fclose($fp);
						cpmsg('database_export_file_invalid', '', 'error');
					} else {
						fclose($fp);
						if($_G['gp_usezip'] == 2) {
							$fp = fopen($dumpfilename, "r");
							$content = @fread($fp, filesize($dumpfilename));
							fclose($fp);
							$zip = new zipfile();
							$zip->addFile($content, basename($dumpfilename));
							$fp = fopen(sprintf($backupfilename."-%s".'.zip', $volume), 'w');
							if(@fwrite($fp, $zip->file()) !== FALSE) {
								@unlink($dumpfilename);
							}
							fclose($fp);
						}
						unset($sqldump, $zip, $content);
						//note
						cpmsg('database_export_multivol_redirect', "action=exttools&operation=$operation&do=tools&type=".rawurlencode($_G['gp_type'])."&saveto=server&filename=".rawurlencode($_G['gp_filename'])."&method=multivol&sizelimit=".rawurlencode($_G['gp_sizelimit'])."&volume=".rawurlencode($volume)."&tableid=".rawurlencode($tableid)."&startfrom=".rawurlencode($startrow)."&extendins=".rawurlencode($_G['gp_extendins'])."&sqlcharset=".rawurlencode($_G['gp_sqlcharset'])."&sqlcompat=".rawurlencode($_G['gp_sqlcompat'])."&exportsubmit=yes&usehex={$_G['gp_usehex']}&usezip={$_G['gp_usezip']}", 'loading', array('volume' => $volume));
					}
				} else {
					$volume--;
					$filelist = '<ul>';
					cpheader();
	
					if($_G['gp_usezip'] == 1) {
						$zip = new zipfile();
						$zipfilename = $backupfilename.'.zip';
						$unlinks = array();
						for($i = 1; $i <= $volume; $i++) {
							$filename = sprintf($dumpfile, $i);
							$fp = fopen($filename, "r");
							$content = @fread($fp, filesize($filename));
							fclose($fp);
							$zip->addFile($content, basename($filename));
							$unlinks[] = $filename;
							$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
						}
						$fp = fopen($zipfilename, 'w');
						if(@fwrite($fp, $zip->file()) !== FALSE) {
							foreach($unlinks as $link) {
								@unlink($link);
							}
						} else {
							cpmsg('database_export_multivol_succeed', '', 'succeed', array('volume' => $volume, 'filelist' => $filelist));
						}
						unset($sqldump, $zip, $content);
						fclose($fp);
						@touch('./data/'.$backupdir.'/index.htm');
						$filename = $zipfilename;
						cpmsg('database_export_zip_succeed', '', 'succeed', array('filename' => $filename));
					} else {
						@touch('./data/'.$backupdir.'/index.htm');
						for($i = 1; $i <= $volume; $i++) {
							$filename = sprintf($_G['gp_usezip'] == 2 ? $backupfilename."-%s".'.zip' : $dumpfile, $i);
							$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
						}
						cpmsg('database_export_multivol_succeed', '', 'succeed', array('volume' => $volume, 'filelist' => $filelist));
					}
				}
	
			} else {
	
				$tablesstr = '';
				foreach($tables as $table) {
					$tablesstr .= '"'.$table.'" ';
				}
	
				require DISCUZ_ROOT . './config/config_global.php';
				list($dbhost, $dbport) = explode(':', $dbhost);
	
				$query = DB::query("SHOW VARIABLES LIKE 'basedir'");
				list(, $mysql_base) = DB::fetch($query, MYSQL_NUM);
	
				$dumpfile = addslashes(dirname(dirname(__FILE__))).'/'.$backupfilename.'.sql';
				@unlink($dumpfile);
	
				$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
				@shell_exec($mysqlbin.'mysqldump --force --quick '.($db->version() > '4.1' ? '--skip-opt --create-options' : '-all').' --add-drop-table'.($_G['gp_extendins'] == 1 ? ' --extended-insert' : '').''.($db->version() > '4.1' && $_G['gp_sqlcompat'] == 'MYSQL40' ? ' --compatible=mysql40' : '').' --host="'.$dbhost.($dbport ? (is_numeric($dbport) ? ' --port='.$dbport : ' --socket="'.$dbport.'"') : '').'" --user="'.$dbuser.'" --password="'.$dbpw.'" "'.$dbname.'" '.$tablesstr.' > '.$dumpfile);
	
				if(@file_exists($dumpfile)) {
	
					if($_G['gp_usezip']) {
						require_once adminfile('function/zip');
						$zip = new zipfile();
						$zipfilename = $backupfilename.'.zip';
						$fp = fopen($dumpfile, "r");
						$content = @fread($fp, filesize($dumpfile));
						fclose($fp);
						$zip->addFile($idstring."# <?exit();?>\n ".$setnames."\n #".$content, basename($dumpfile));
						$fp = fopen($zipfilename, 'w');
						@fwrite($fp, $zip->file());
						fclose($fp);
						@unlink($dumpfile);
						@touch('./data/'.$backupdir.'/index.htm');
						$filename = $backupfilename.'.zip';
						unset($sqldump, $zip, $content);
						cpmsg('database_export_zip_succeed', '', 'succeed', array('filename' => $filename));
					} else {
						if(@is_writeable($dumpfile)) {
							$fp = fopen($dumpfile, 'rb+');
							@fwrite($fp, $idstring."# <?exit();?>\n ".$setnames."\n #");
							fclose($fp);
						}
						@touch('./data/'.$backupdir.'/index.htm');
						$filename = $backupfilename.'.sql';
						cpmsg('database_export_succeed', '', 'succeed', array('filename' => $filename));
					}
	
				} else {
	
					cpmsg('database_shell_fail', '', 'error');
	
				}
	
			}
		}
	} elseif($cp == 'import') {
		checkpermission('dbimport');
		if(!submitcheck('importsubmit', 1) && !submitcheck('deletesubmit')) {
	
			$exportlog = $exportsize = $exportziplog = array();
			if(is_dir(DISCUZ_ROOT.'./data/'.$backupdir)) {
				$dir = dir(DISCUZ_ROOT.'./data/'.$backupdir);
				while($entry = $dir->read()) {
					$entry = './data/'.$backupdir.'/'.$entry;
					if(is_file($entry)) {
						if(preg_match("/\.sql$/i", $entry)) {
							$filesize = filesize($entry);
							$fp = fopen($entry, 'rb');
							$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
							fclose($fp);
							$key = preg_replace('/^(.+?)(\-\d+)\.sql$/i', '\\1', basename($entry));
							$exportlog[$key][$identify[4]] = array(
								'version' => $identify[1],
								'type' => $identify[2],
								'method' => $identify[3],
								'volume' => $identify[4],
								'filename' => $entry,
								'dateline' => filemtime($entry),
								'size' => $filesize
							);
							$exportsize[$key] += $filesize;
						} elseif(preg_match("/\.zip$/i", $entry)) {
							$filesize = filesize($entry);
							$exportziplog[] = array(
								'type' => 'zip',
								'filename' => $entry,
								'size' => filesize($entry),
								'dateline' => filemtime($entry)
							);
						}
					}
				}
				$dir->close();
			} else {
				cpmsg('database_export_dest_invalid', '', 'error');
			}
	
			showsubmenu($menuname, array(
				array('nav_db_export', "exttools&operation=$operation&do=tools&cp=export", 0),
				array('nav_db_import', "exttools&operation=$operation&do=tools&cp=import", 1),	
	
			));
			showtips('db_import_tips');
			showtableheader('db_import');
			showformheader("exttools&operation=$operation&do=tools&cp=import", 'enctype');
			showtablerow('', array('colspan="2" class="rowform"', 'colspan="7" class="rowform"'), array(
				'<input class="radio" type="radio" name="from" value="server" checked="checked" onclick="this.form.datafile_server.disabled=!this.checked;this.form.datafile.disabled=this.checked" />'.$lang[db_import_from_server],
				'<input type="text" class="txt" name="datafile_server" value="./data/'.$backupdir.'/" style="width:245px;" />'
			));
			showtablerow('', array('colspan="2" class="rowform"', 'colspan="8" class="rowform"'), array(
				'<input class="radio" type="radio" name="from" value="local" onclick="this.form.datafile_server.disabled=this.checked;this.form.datafile.disabled=!this.checked" />'.$lang[db_import_from_local],
				'<input type="file" name="datafile" size="29" disabled="disabled" class="uploadbtn marginbot" />'
			));
			showsubmit('importsubmit');
			showformfooter();
	
			showformheader('db&operation=import');
			showtitle('db_export_file');
			showsubtitle(array('', 'filename', 'version', 'time', 'type', 'size', 'db_method', 'db_volume', ''));
	
			foreach($exportlog as $key => $val) {
				$info = $val[1];
				$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
				$info['size'] = sizecount($exportsize[$key]);
				$info['volume'] = count($val);
				$info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? $lang['db_multivol'] : $lang['db_shell']) : '';
				showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".$key."\">",
					"<a href=\"javascript:;\" onclick=\"display('exportlog_$key')\">".$key."</a>",
					$info['version'],
					$info['dateline'],
					$lang['db_export_'.$info['type']],
					$info['size'],
					$info['method'],
					$info['volume'],
					$info['type'] == 'zip' ? "<a href=\"".ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&cp=importzip&datafile_server=$info[filename]&importsubmit=yes\" class=\"act\">$lang[db_import_unzip]</a>" : "<a class=\"act\" href=\"".ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&cp=import&from=server&datafile_server=$info[filename]&importsubmit=yes\"".($info['version'] != $_G['setting']['version'] ? " onclick=\"return confirm('$lang[db_import_confirm]');\"" : '')." class=\"act\">$lang[import]</a>"
				));
				echo '<tbody id="exportlog_'.$key.'" style="display:none">';
				foreach($val as $info) {
					$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
					$info['size'] = sizecount($info['size']);
					showtablerow('', '', array(
						'',
						"<a href=\"$info[filename]\">".substr(strrchr($info['filename'], "/"), 1)."</a>",
						$info['version'],
						$info['dateline'],
						'',
						$info['size'],
						'',
						$info['volume'],
						''
					));
				}
				echo '</tbody>';
			}
	
			foreach($exportziplog as $info) {
				$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
				$info['size'] = sizecount($info['size']);
				$info['method'] = $info['method'] == 'multivol' ? $lang['db_multivol'] : $lang['db_zip'];
				showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".basename($info['filename'])."\">",
					"<a href=\"$info[filename]\">".substr(strrchr($info['filename'], "/"), 1)."</a>",
					'',
					$info['dateline'],
					$lang['db_export_'.$info['type']],
					$info['size'],
					$info['method'],
					'',
					"<a href=\"".ADMINSCRIPT."?action=exttools&operation=$operation&do=tools&cp=importzip&datafile_server=$info[filename]&importsubmit=yes\" class=\"act\">$lang[db_import_unzip]</a>"
				));
			}
	
			showsubmit('deletesubmit', 'submit', 'del');
			showformfooter();
	
			showtablefooter();
	
		} elseif(submitcheck('importsubmit', 1)) {
	
			$readerror = 0;
			$datafile = '';
			if($_G['gp_from'] == 'server') {
				$datafile = DISCUZ_ROOT.'./'.$_G['gp_datafile_server'];
			}
	
			elseif($_G['gp_from'] == 'local') {
				$datafile = $_FILES['datafile']['tmp_name'];
			}
			$datafile = urldecode($datafile);
			if(@$fp = fopen($datafile, 'rb')) {
				$sqldump = fgets($fp, 256);
				$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", $sqldump)));
				$dumpinfo = array('method' => $identify[3], 'volume' => intval($identify[4]));
				if($dumpinfo['method'] == 'multivol') {
					$sqldump .= fread($fp, filesize($datafile));
				}
				fclose($fp);
			} else {
				if($_G['gp_autoimport']) {
					updatecache();
					cpmsg('database_import_multivol_succeed', '', 'succeed');
				} else {
					cpmsg('database_import_file_illegal', '', 'error');
				}
			}
			if($dumpinfo['method'] == 'multivol') {
				$sqlquery = splitsql($sqldump);
				unset($sqldump);
	
				foreach($sqlquery as $sql) {
	
					$sql = syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset);
	
					if($sql != '') {
						DB::query($sql, 'SILENT');
						if(($sqlerror = DB::error()) && DB::errno() != 1062) {
							$db->halt('MySQL Query Error', $sql);
						}
					}
				}
	
				if($_G['gp_delunzip']) {
					@unlink($_G['gp_datafile_server']);
				}
	
				if($_G['gp_from'] == 'local') {
					cpmsg('database_import_file_succeed', 'action=db&operation=import', 'succeed');
				}
				$datafile_next = preg_replace("/-($dumpinfo[volume])(\..+)$/", "-".($dumpinfo['volume'] + 1)."\\2", $_G['gp_datafile_server']);
				$datafile_next = urlencode($datafile_next);
				if($dumpinfo['volume'] == 1) {
					cpmsg('database_import_multivol_prompt',
						"action=exttools&operation=$operation&do=tools&cp=import&from=server&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes".(!empty($_G['gp_delunzip']) ? '&delunzip=yes' : ''),
						'form');
				} elseif($_G['gp_autoimport']) {
					cpmsg('database_import_multivol_redirect', "action=exttools&operation=$operation&do=tools&cp=import&from=server&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes".(!empty($_G['gp_delunzip']) ? '&delunzip=yes' : ''), 'loading', array('volume' => $dumpinfo['volume']));
				} else {
					updatecache();
					cpmsg('database_import_succeed', '', 'succeed');
				}
			} else {
				cpmsg('database_import_format_illegal', '', 'error');
			}
		} elseif(submitcheck('deletesubmit')) {
			if(is_array($_G['gp_delete'])) {
				foreach($_G['gp_delete'] as $filename) {
					$file_path = './data/'.$backupdir.'/'.str_replace(array('/', '\\'), '', $filename);
					if(is_file($file_path)) {
						@unlink($file_path);
					} else {
						$i = 1;
						while(1) {
							$file_path = './data/'.$backupdir.'/'.str_replace(array('/', '\\'), '', $filename.'-'.$i.'.sql');
							if(is_file($file_path)) {
								@unlink($file_path);
								$i++;
							} else {
								break;
							}
						}
					}
				}
				cpmsg('database_file_delete_succeed', '', 'succeed');
			} else {
				cpmsg('database_file_delete_invalid', '', 'error');
			}
		}
	} elseif($cp == 'importzip'){
		if(empty($_G['gp_datafile_server'])) {
			cpmsg('database_import_file_illegal', '', 'error');
		} else {
			$datafile_server = DISCUZ_ROOT.'./data/'.$backupdir.'/'.basename($_G['gp_datafile_server']);
			if(!@file_exists($datafile_server)) {
				cpmsg('database_import_file_illegal', '', 'error');
			}
		}
	
		require_once libfile('class/zip');
		$unzip = new SimpleUnzip();
		$unzip->ReadFile($datafile_server);
	
		if($unzip->Count() == 0 || $unzip->GetError(0) != 0 || !preg_match("/\.sql$/i", $importfile = $unzip->GetName(0))) {
			cpmsg('database_import_file_illegal', '', 'error');
		}
	
		$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", substr($unzip->GetData(0), 0, 256))));
		$confirm = !empty($_G['gp_confirm']) ? 1 : 0;
		if(!$confirm && $identify[1] != $_G['setting']['version']) {
			cpmsg('database_import_confirm', 'action=db&operation=importzip&datafile_server=$datafile_server&importsubmit=yes&confirm=yes', 'form');
		}
	
		$sqlfilecount = 0;
		foreach($unzip->Entries as $entry) {
			if(preg_match("/\.sql$/i", $entry->Name)) {
				$fp = fopen('./data/'.$backupdir.'/'.$entry->Name, 'w');
				fwrite($fp, $entry->Data);
				fclose($fp);
				$sqlfilecount++;
			}
		}
	
		if(!$sqlfilecount) {
			cpmsg('database_import_file_illegal', '', 'error');
		}
	
		$info = basename($datafile_server).'<br />'.$lang['version'].': '.$identify[1].'<br />'.$lang['type'].': '.$lang['db_export_'.$identify[2]].'<br />'.$lang['db_method'].': '.($identify[3] == 'multivol' ? $lang['db_multivol'] : $lang['db_shell']).'<br />';
	
		if(isset($multivol)) {
			$multivol++;
			$datafile_server = preg_replace("/-(\d+)(\..+)$/", "-$multivol\\2", $datafile_server);
			if(file_exists($datafile_server)) {
				cpmsg('database_import_multivol_unzip_redirect', 'action=db&operation=importzip&multivol='.$multivol.'&datafile_vol1='.$datafile_vol1.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes', 'loading', array('multivol' => $multivol));
			} else {
				cpmsg('database_import_multivol_confirm', 'action=db&operation=import&from=server&datafile_server='.$datafile_vol1.'&importsubmit=yes&delunzip=yes', 'form');
			}
		}
	
		if($identify[3] == 'multivol' && $identify[4] == 1 && preg_match("/-1(\..+)$/", $datafile_server)) {
			$datafile_vol1 = $datafile_server;
			$datafile_server = preg_replace("/-1(\..+)$/", "-2\\1", $datafile_server);
			if(file_exists($datafile_server)) {
				cpmsg('database_import_multivol_unzip', 'action=exttools&operation='.$operation.'&do=tools&cp=importzip&multivol=1&datafile_vol1=./data/'.$backupdir.'/'.$importfile.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes', 'form', array('info' => $info));
			}
		}
	
		cpmsg('database_import_unzip', 'action=exttools&operation='.$operation.'&do=tools&cp=import&from=server&datafile_server=./data/'.$backupdir.'/'.$importfile.'&importsubmit=yes&delunzip=yes', 'form', array('info' => $info));

	}
}

echo "<style>.floattopempty {height:20px !important;}</style>";



function runquery($sql) {
	global $_G;
	$tablepre = $_G['config']['db'][1]['tablepre'];
	$dbcharset = $_G['config']['db'][1]['dbcharset'];

	$sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' '.$tablepre, ' '.$tablepre, ' `'.$tablepre, ' '.$tablepre, ' `'.$tablepre), $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				DB::query(createtable($query, $dbcharset));

			} else {
				DB::query($query);
			}

		}
	}
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function dlist($dir,$ver){
	global $dlist;
	
	$mod = preg_match('/(album|forum|portal)/im', $dir,$match);
	$mod = $match[0];
	foreach (glob($dir.'/*') as $filename) {
		if(is_dir($filename)){
			dlist($filename,$ver);
		} else {
			
			$filename = str_replace(DISCUZ_ROOT."/data/attachment/{$mod}/",'',stripslashes($filename));
			if ($mod == 'album') {
				$aid = DB::result_first("SELECT count(picid) FROM ".DB::table('home_pic')." WHERE filepath = '$filename'");
			} elseif ($mod == 'forum') {
				
				if($ver >= 2){
					$aid = 0;
					for($i=0;$i<=9;$i++){
						$aid += DB::result_first("SELECT count(aid) FROM ".DB::table('forum_attachment_'.$i)." WHERE attachment = '$filename'");
					}
				} else {
					$aid += DB::result_first("SELECT count(aid) FROM ".DB::table('forum_attachment')." WHERE attachment = '$filename'");
				}
			} elseif($mod == 'portal') {
				$aid = DB::result_first("SELECT count(attachid) FROM ".DB::table('portal_attachment')." WHERE attachment = '$filename'");
			}
			if($aid == 0 && !strpos($filename,'index.html') && !strpos($filename,'thumb.jpg')){
				$dlist[$filename] = $mod.'/'.$filename;
			}
		}
	}
}

function checkattindex($tablename,$needindex){
	$query = DB::query("SHOW INDEX FROM ".DB::table($tablename));
	while($index = DB::fetch($query)){
		$indexs[] = $index['Column_name'];	
	}
	
	if(!in_array($needindex,$indexs)){
		DB::query("ALTER TABLE ".DB::table($tablename)." ADD INDEX ( `$needindex` ) ");
	}
	return 1;
}


?>