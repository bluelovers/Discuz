<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: myapp.class.php 21253 2011-03-21 07:45:04Z zhengqingpeng $
 */

class plugin_myapp{
	function plugin_myapp() {
		global $_G;

		$this->title = $_G['cache']['plugin']['myapp']['showtitle'];
		$this->num = intval($_G['cache']['plugin']['myapp']['shownum']);
	}
}

class plugin_myapp_forum extends plugin_myapp {

	function viewthread_sidebottom_output() {
		global $_G, $postlist;

		if(IS_ROBOT) {
			return array();
		}

		if(!$_G['forum_firstpid']) {
			return array();
		}
		loadcache('myapp');
		$thisApp = '';
		$poster = reset($postlist);
		$userapp = DB::fetch_first("SELECT appid FROM ".DB::table('home_userapp_plying')." WHERE uid='{$poster[authorid]}'");
		if(!empty($userapp['appid'])) {
			$applist = explode(',', $userapp['appid']);
			$i = 0;
			foreach($applist as $appid) {
				if(!empty($_G['cache']['myapp'][$appid])) {
					if($i < $this->num) {
						$thisApp .= '<a href="userapp.php?mod=app&id='.$appid.'&fromtid='.$_G['tid'].'"'.(isset($_G['cache']['myapp'][$appid]) ? ' title="'.$_G['cache']['myapp'][$appid]['appname'].'"' : '').' target="_blank">'.
						'<img class="authicn vm" src="http://appicon.manyou.com/logos/'.$appid.'" style="width:40px;height:40px;margin-left:0px;"/></a>';
						$i++;
					} else {
						break;
					}
				}
			}
		}

		$thisApp = $thisApp ? '<p>'.$this->title.'</p><p class="avt" style="margin-left:10px;">'.$thisApp.'</p>' : '';
		return array($thisApp);
	}

}
class plugin_myapp_userapp extends plugin_myapp {
	function userapp_update() {
		global $_G;

		if(!empty($_G['gp_id']) && is_numeric($_G['gp_id'])) {
			$applist = array();
			$userapp = DB::fetch_first("SELECT appid FROM ".DB::table('home_userapp_plying')." WHERE uid='$_G[uid]'");
			if(!empty($userapp['appid'])) {
				$applist = explode(',', $userapp['appid']);
				if(!empty($applist)) {
					$applist = array_diff($applist, array(''));
					$key = array_search($_G['gp_id'], $applist);
					if($key !== false) {
						unset($applist[$key]);
					}
					array_unshift($applist, $_G['gp_id']);
					while(count($applist) > $this->num) {
						array_pop($applist);
					}
				}
			}
			if(empty($applist)) {
				$applist = array($_G['gp_id']);
			}
			if(!empty($applist)) {
				$appstr = implode(',', $applist);
				DB::insert('home_userapp_plying', array('uid' => $_G['uid'], 'appid' => daddslashes($appstr)), false, true);
			}
		}
	}
}
?>