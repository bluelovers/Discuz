<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class plugin_milu_robots {

	function global_header() {
		global $_G;
		require_once 'robotsCore.class.php';
		$set = $_G['cache']['plugin']['milu_robots'];
		$robots_list = (array)unserialize($set['robotsType']);
		$miluRobots->add_log();
	}

}

class plugin_milu_robots_common extends plugin_milu_robots {

	function global_header() {
		require_once 'robotsCore.class.php';
	}

}

?>
