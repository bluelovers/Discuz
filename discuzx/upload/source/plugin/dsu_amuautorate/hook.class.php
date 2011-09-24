<?php

class plugin_dsu_amuautorate{
}

class plugin_dsu_amuautorate_forum extends plugin_dsu_amuautorate {
	
	function post_amurate_output($a){
		global $_G, $pid, $thread;
		loadcache('plugin');
		$this -> vars = $_G['cache']['plugin']['dsu_amuautorate'];
		$rates = array(0,1,0.6,0.2);
		if($a['message'] == 'post_reply_succeed'){
			$posttable = getposttablebytid($thread['tid']);
			$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='{$thread['tid']}'");
			$replyrates = ceil($rates[$thread['replies']] * $this -> vars['rate']);
			if($thread['replies'] < 4 && $replyrates && $thread['authorid'] <> $_G['uid'] && $_G['uid'] > 1){
				updatemembercount($_G['uid'], array("extcredits{$this -> vars['extcredit']}" => $replyrates ),  1, 'PRC', $pid);
				DB::query("UPDATE ".DB::table($posttable)." SET rate=rate+($replyrates), ratetimes=ratetimes+1 WHERE pid='$pid'");
				DB::query("INSERT INTO ".DB::table('forum_ratelog')." (pid, uid, username, extcredits, dateline, score, reason)	VALUES ('{$pid}', '1', 'root', '{$this -> vars['extcredit']}', 'P$_G[timestamp]}', '{$replyrates}', '抢楼奖励')", 'UNBUFFERED');
				
			}
		}
 	}

}
?>