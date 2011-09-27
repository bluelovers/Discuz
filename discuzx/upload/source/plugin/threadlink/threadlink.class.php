<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-06 10:09
 *	Filename: threadlink.class.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_threadlink{

}

class plugin_threadlink_forum extends plugin_threadlink {
	var $postfooter = array();	//
	var $allowgroups = array();	//允许使用此功能的用户组
	var $member = array();
	var $addbasehtml = '';
	var $tid = 0;

	function plugin_threadlink_forum() {
		global $_G;

		$this->tid = $_G['tid'];
		$this->member = $_G['member'];
		$this->allowgroups = unserialize($_G['cache']['plugin']['threadlink']['allowgroups']);
	}

	function viewthread_title_extra_output($p) {
		if(in_array($this->member['groupid'], $this->allowgroups)) {
		} else {
			return '';
		}
	}
	function viewthread_postfooter_output() {
		global $postlist;
		if(in_array($this->member['groupid'], $this->allowgroups)) {
			$this->addbasehtml = '<a href="plugin.php?id=threadlink:mod&action=addbase&tid='.$this->tid.'" style="background:url(source/plugin/threadlink/image/addbase.png) no-repeat 4px 50%;" onclick="showWindow(\'threadlink\', this.href, \'get\', 1);doane(e);">'.pl('addbase').'</a>';
			foreach($postlist as $k => $v) {
				$a[] = ($v['first'] ? $this->addbasehtml : '').'<a href="plugin.php?id=threadlink:mod&action=addlink&pid='.$v['pid'].'" style="background:url(source/plugin/threadlink/image/addlink.png) no-repeat 4px 50%;" onclick="showWindow(\'threadlink\', this.href, \'get\', 1);doane(e);">'.pl('addlink').'</a>';
			}
			$this->postfooter = $a;
		}
		return $this->postfooter;
	}


	function viewthread_postbottom_output() {
		global $postlist;

		reset($postlist);
		$post = current($postlist);
		$return = '';
		$links = array();
		if($this->tid && $post['first']) {
			if($base = DB::fetch_first("SELECT * FROM ".DB::table('threadlink_base')." WHERE tid='{$this->tid}'")) {
				$query = DB::query("SELECT * FROM ".DB::table('threadlink_link')." WHERE tid='{$this->tid}' LIMIT {$base[maxrow]}");
				while($link = DB::fetch($query)) {
					$link['pic'] = $link['aid'] ? getforumimg($link['aid'], 0, $base['picwidth'], $base['picheight']) : $link['pic'];
					$links[] = $link;
				}
				if($links) {
					include template('threadlink:'.$base['tltpl']);
				}
			}
		} else {
			$return = '';
		}
		return array($return);
	}
}


function pl($str) {
	return lang('plugin/threadlink', $str);
}
?>
