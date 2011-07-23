<?php

/* 背景商店插件参赛版 For Discuz X2.0
   $Id:shop.class.php 2011-07-19 Rufey_Lau
 */
 
if(!defined('IN_DISCUZ')){
	exit('Access Denied!');
}

class plugin_bkshop_dzx{
	function getposition($level, $vertical){
		switch($level){
			case 1 : $condition1 = 'left'; break;
			case 2 : $condition1 = 'center'; break;
			case 3 : $condition1 = 'right'; break;
		}
		switch($vertical){
			case 1 : $condition2 = 'top'; break;
			case 2 : $condition2 = 'center'; break;
			case 3 : $condition2 = 'bottom'; break;
		}
		$condition = $condition1." ".$condition2;
		return $condition;
	}
	function getrepeat($value){
		switch($value){
			case '0' : $return = 'no-repeat'; break;
			case '1' : $return = 'repeat'; break;
			case '2' : $return = 'repeat-x'; break;
			case '3' : $return = 'repeat-y'; break;
		}
		return $return;
	}
	function viewthread_posttop_output(){
		global $_G, $post,$postlist;
		//自动删除过期背景任务
		$task = DB::query("SELECT * FROM ".DB::table('bkshop_buy')."");
		while($tasks = DB::fetch($task)){
			$overdue = ($tasks['days'] * 86400) + $tasks['date'];
			if($_G['timestamp'] > $overdue){
				DB::query("DELETE FROM ".DB::table('bkshop_buy')." WHERE `id` = '$tasks[id]'");
			}
		}
		foreach($postlist as $k => $v){
			$user = DB::fetch_first("SELECT * FROM ".DB::table('bkshop_users')." WHERE `uid` = '$v[authorid]'");
			$inuse = DB::fetch_first("SELECT * FROM ".DB::table('bkshop_buy')." WHERE `bid` = '$user[used]'");
			if($inuse&&$user['switch']&&in_array($_G['groupid'], unserialize($_G['cache']['plugin']['bkshop_dzx']['GROUPS']))){
				$style = DB::fetch_first("SELECT * FROM ".DB::table('bkshop')." WHERE `id` = '$user[used]'");
					$position = $this->getposition($user['level'], $user['vertical']);
					$repeat = $this->getrepeat($user['repeat']);
					include template('bkshop_dzx:thread');
					$output[] = $return;
			}else{
					$output[] = '';
			}
		}
		return $output;
	}
}
class plugin_bkshop_dzx_forum extends plugin_bkshop_dzx{
}

?>