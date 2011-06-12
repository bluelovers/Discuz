<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class plugin_ddu_dev_dzx {
	function _ddu_dev_dzx_get_user_dev($user){
		$sql = DB::query("SELECT * FROM `".DB::table('ddu_dev_dzx')."` WHERE `user`='".$user."'");
		$row = DB::fetch($sql);
		if(is_array($row)){
			$ddu_dev_get_user_dev = true;
		}else{
			$ddu_dev_get_user_dev = false;
		}
		return $ddu_dev_get_user_dev;
	}
}
class plugin_ddu_dev_dzx_forum extends plugin_ddu_dev_dzx{
	function viewthread_sidebottom_output() {
		global $postlist;
		$return = array();
		if(is_array($postlist)) {
			foreach($postlist as $key => $val) {
				$img = '<p><a href="plugin.php?id=ddu_dev_dzx:dev&user='.$val['username'].'" target="_blank"><img src="source/plugin/ddu_dev_dzx/img/devuser.png" /></a></p>';
				$_DDU_DEV[$val['username']] = $this->_ddu_dev_get_user_dev_dzx($val['username']);
				$pgid = $_DDU_DEV[$val['username']];
				if($pgid) {
					$return[] = $img;
				} else {
					$return[] = '';
				}
			}
		}
		return $return;
	}
}
?>