<?php

/**
 * @author Lampcn工作室
 * @link http://www.lampcn.net
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_lnp_taskbar_dzx {
	function __construct() {
		
	}
	
	function global_footer() {
		global $_G;
		$return = '';
		$staticurl = STATICURL;
		$lnpdata = $_G['cache']['plugin']['lnp_taskbar_dzx'];
		$toplist_h = $lnpdata['toplist_h'] ? 'height:'.$lnpdata['toplist_h'].'px;' : '';
		if(getcookie('lnp_topen') == 1 || (getcookie('lnp_topen') != -1 && $lnddata['openbar'])) {
			$disopen = '';
			$disclose = ' style="display:none;" ';
		} else {
			$disclose = '';
			$disopen = ' style="display:none;" ';
		}
		if(!$lnpdata['close_myapp'] && $_G['setting']['my_app_status']) {
			$defaultapp = $this->_getDefaultApp();
			$myuserapp = $this->_getMyUserApp();
		}
		$flashpm = '';
		if($_G['uid'] > 0) {
			if(!$lnpdata['close_oluser']) {
				$ollist =  $this->_getOnlineFriend();
				$olnum = $ollist[0];
				$olliststr = $ollist[1];
			}
			$pmnew = $_G['member']['newpm'] ? 'havenew' : '';
			$pmnewicon = $pmnew ? '<span title="您有新消息" class="lnp_newmsg"></span>' : '';
			$notenew = $_G['member']['newprompt'] ? 'havenew' : '';
			$notenum = $_G['member']['newprompt'] ? "({$_G['member']['newprompt']})" : '';
			$notenewicon = $notenew ? '<span title="您有新提醒" class="lnp_newmsg"></span>' : '';
			$flashpm = $lnpdata['flash_pm'] ? "setInterval('lnpmsg()', 1000);" : '';
		}
		
		include 'template/global_footer.htm';
		return $return;
	}
	
	function _getDefaultApp() {
		global $_G;
		loadcache('userapp');
		$rst = '';
		if($_G['cache']['userapp']) {
			foreach($_G['cache']['userapp'] as $value) {
				$value['icon'] = empty($value['icon']) ? 'src="http://appicon.manyou.com/icons/'.$value['appid'].'"' : 'src="'.$value['icon'].'" onerror="this.onerror=null;this.src=\'http://appicon.manyou.com/icons/'.$value['appid'].'\';"';
				$rst .= '<li><a href="userapp.php?mod=app&id='.$value['appid'].'" title="'.$value['appname'].'"><img '.$value['icon'].' alt="'.$value['appname'].'" />'.$value['appname'].'</a></li>';
			}
		}
		return $rst;
	}
	
	function _getMyUserApp() {
		global $_G;
		if($_G['uid'] < 1) {
			return '';
		}
		getuserapp();
		$rst = '';
		if($_G['my_menu']) {
			foreach($_G['my_menu'] as $value) {
				$value['icon'] = empty($value['icon']) ? 'src="http://appicon.manyou.com/icons/'.$value['appid'].'"' : 'src="'.$value['icon'].'" onerror="this.onerror=null;this.src=\'http://appicon.manyou.com/icons/'.$value['appid'].'\';"';
				$rst .= '<li><a href="userapp.php?mod=app&id='.$value['appid'].'" title="'.$value['appname'].'"><img '.$value['icon'].' alt="'.$value['appname'].'" />'.$value['appname'].'</a></li>';
			}
		}
		return $rst;
	}
	
	function _getOnlineNum() {
		global $_G;
		
		$olnum = 0;
		$user = array();
		space_merge($user, 'field_home');
			
		if($_G['uid'] > 0 && $user['feedfriend']) {
			$olnum = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_session')." WHERE uid IN ({$user['feedfriend']})"), 0);
			$olnum = $olnum ? $olnum : 0;
		}
		return $olnum;
	}
	
	function _getOnlineFriend() {
		global $_G;
		$staticurl = STATICURL;
		$olnum = 0;
		$list = array();
		$liststr = '';
		$user = array();
		space_merge($user, 'field_home');
			
		if($_G['uid'] > 0 && $user['feedfriend']) {
			$olnum = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_session')." WHERE uid IN ({$user['feedfriend']})"), 0);
			$olnum = $olnum ? $olnum : 0;
		}
		if($olnum && $user['feedfriend']) {
			$query = DB::query("SELECT uid,username,invisible FROM ".DB::table("common_session")." WHERE uid IN ({$user['feedfriend']}) AND invisible='0' ORDER BY lastactivity DESC LIMIT 20");
			while($value = DB::fetch($query)) {
				$liststr .= <<<EOT
				<li><img class="lnp_olicon" title="在线" src="{$staticurl}image/common/ol.gif" /><a title="和 {$value['username']} 聊天" href="home.php?mod=space&amp;uid={$value['uid']}" onclick="showWindow('showMsgBox{$value['uid']}', 'home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_{$value['uid']}&touid={$value['uid']}&pmid=0&daterange=2', 'get', 0)" ><img alt="{$value['username']}" src="{$staticurl}image/common/user_online.gif">{$value['username']}</a></li>
EOT;
			}
		}
		return array($olnum,$liststr);
	}
}