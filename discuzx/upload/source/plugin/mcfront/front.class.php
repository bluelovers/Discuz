<?php
if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
	}

class plugin_mcfront {

}

//全局脚本嵌入点类
class plugin_mcfront_forum extends plugin_mcfront {
	function viewthread_sidebottom() {
		global $_G, $postlist;
		$euid = $_G['thread']['authorid'];
		$arr[] = '<a href="forum.php?mod=modcp&action=plugin&op=tools&id=mcfront:medalcp&euid='.$euid.'">管理该用户勋章</a>';
		return $arr;
	}
}

?>