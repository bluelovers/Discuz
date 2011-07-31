<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
    include 'source/plugin/v63ht/config.inc.php';
    if($_GET['vac'] == 'getht'){

        
        include template('v63ht:getht');
    }
    
    if($_GET['vac'] == 'hd'){
        require_once libfile('function/forum');
        if($_G[uid] ==''){showmessage('请先登录.....');}
        if( addslashes($_POST['msg'])==''){showmessage('请填写回复内容.....');}
        $postdata = array('tid'=>$setting['htid'],'first' => '0','author' => $_G['username'],'authorid' => $_G['uid'],'subject' => '','dateline' => $_G['timestamp'],'message' => addslashes($_POST['msg']),'useip' => $_G['clientip'],'invisible' => '0','anonymous' => '0','usesig' => '0','htmlon' => '0','bbcodeoff' => '0','smileyoff' => '0','parseurloff' => '0','attachment' => '0',);
        insertpost($postdata);
        DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$_G[username]', lastpost='$_G[timestamp]', replies=replies+1 WHERE tid='$setting[htid]'", 'UNBUFFERED');
        
        $fq = DB::query("select fid from ".DB::table('forum_post')." where tid='$setting[htid]'");
        $ff = DB::fetch($fq);    
        $lastpost = "$setting[htid]\t".addslashes($_POST['msg'])."\t$_G[timestamp]\t$_G[username]";
        DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$ff[fid]'",'UNBUFFERED');
        showmessage('参与互动成功,正在跳转......','forum.php?mod=viewthread&tid='.$setting['htid']);
    }
    
    
    
   
?>