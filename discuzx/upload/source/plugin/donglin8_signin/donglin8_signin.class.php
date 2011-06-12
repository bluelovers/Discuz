<?php

/*
	每日抢楼签到 for DX 1.5
	Powered by Donglin8.Com 2010.10
*/

if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
}
class threadplugin_donglin8_signin {
	
	var $name = '抢楼签到';					//主题类型名称
	var $iconfile = 'static/image/common/gs.gif';				//发布主题链接中的前缀图标
	var $buttontext = '发布抢楼签到主题';		//发帖时按钮文字
	
	var $identifier = 'donglin8_signin';
	var $isadmin;
	function threadplugin_donglin8_signin() {
		
		global $_G;	
		loadcache('plugin');
		$config=$_G['cache']['plugin']['donglin8_signin'];	
		$plugin = $_DPLUGIN[$this->identifier];
		$this->isadmin = $adminid == 3 ? $forum['ismoderator'] : ($adminid > 0);

		if (!$plugin || !is_array($plugin)) {
			include DISCUZ_ROOT.'./data/cache/plugin_'.$this->identifier.'.php';
			$plugin = $_DPLUGIN[$this->identifier];
		}
		
		$this->plugin = $plugin;
		$this->directory = './source/plugin/'.$plugin['directory'];
		$this->imgurl = $this->directory.'donglin8_signin/images/';

		if (!$config['donglin8_list_tpp']) $config['donglin8_list_tpp'] = 50;
		

	}
	
	function newthread($fid) {
		$return = '';
		return $return;
	}
	
	//发帖处理
	function newthread_submit($fid) {
		global $_G;
		loadcache('plugin');
		$config=$_G['cache']['plugin']['donglin8_signin'];	
		$todayzero = strtotime('today');
		$todayend = $todayzero + 86399;
		$signin_begint = $config['donglin8_begin'] * 3600 + $todayzero;
		
		if ($_G['timestamp'] < $signin_begint && $_G['timestamp'] > $todayzero) {
			showmessage('未到抢楼时间，今日抢楼时间为 '.$config["donglin8_begin"].'：00 整，请返回耐心等待。', NULL, 'HALTED');
		}

		if (db::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE fid = '$_G[fid]' AND dateline >= '$signin_begint' AND dateline < '$todayend'")) {
			showmessage('对不起，已有人先你抢到今日楼主了，请等明日继续。', 'forum.php?mod=forumdisplay&fid='.$fid);
		}
        
	}
	
	//楼主加分
	function newthread_submit_end($fid) {
		global $_G;
		loadcache('plugin');
		$config=$_G['cache']['plugin']['donglin8_signin'];	
		if (in_array($config['donglin8_extcreditx'], range(1,8))) {
		$ext1 = "extcredits".$config["donglin8_extcreditx"];
		$ext2 = $config['donglin8_bonus_1st'];
		DB::query("UPDATE ".DB::table('common_member_count')." SET ".$ext1. "= ".$ext1."+ ".$ext2." WHERE uid='$_G[uid]'");
		}
	}

	//签到奖励名单
	function viewthread($tid) {
		global $_G;
		loadcache('plugin');
		$config=$_G['cache']['plugin']['donglin8_signin'];	
		
		$i = 1;
    $bonuslist = array();
    $query = db::query("SELECT p.authorid, p.author, gs.bdateline, gs.bonused FROM ".DB::table('forum_post')." AS p
      LEFT JOIN ".DB::table('forum_donglin8_signin')." AS gs ON(gs.pid = p.pid)
      WHERE p.tid = '$tid' AND gs.bonused > 0 AND p.first = 0 GROUP BY p.authorid ORDER BY gs.bdateline LIMIT 0, ".$config['donglin8_list_tpp']);
    while ($value = db::fetch($query)) {
        $value['dateline'] = gmdate("y-m-d H:i", $value['bdateline'] + $timeoffset * 3600);
        $bonuslist[$i++] = $value;
    }
    $imgurl = $this->imgurl;

    $return = '';
	include template('donglin8_signin:list');
    return $return;

	}

}

?>