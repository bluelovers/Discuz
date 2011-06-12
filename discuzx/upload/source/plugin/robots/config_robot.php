<?php
if(!defined('IN_DISCUZ')) { exit('Access Denied'); }
//@date_default_timezone_set("Asia/Shanghai");

global $_G;
loadcache('plugin');
$robot_set = $_G['cache']['plugin']['robots'];
$robot_set['allow_uid'] = '';//例如:$robot_set['allow_uid'] = '1,2,3'; 允許uid為1,2,3的用戶使用這個插件.用英文分號,分隔. 留空為允許可以登錄後台的用戶.
if(empty($robot_set['robot_perpage'])){$robot_set['robot_perpage'] = 10;}
if(empty($robot_set['message_perpage'])){$robot_set['message_perpage'] = 20;}
if(empty($robot_set['thread_credittype'])){$robot_set['thread_credittype'] = 'extcredits2';}
	else{
	$robot_set['thread_credittype'] = 'extcredits'.$robot_set['thread_credittype'];}
if(empty($robot_set['post_credittype'])){$robot_set['post_credittype'] = 'extcredits2';}else{
	$robot_set['post_credittype'] = 'extcredits'.$robot_set['post_credittype'];}
if(strpos($robot_set['views_addnum'],'rand') === 0){
			$views_addnum = substr(trim($robot_set['views_addnum']), 5, -1);
			$views_addnum_arr = explode(',', $views_addnum);
			$robot_set['views_addnum'] = 'rand';
			$robot_set['views_addnum_min'] = intval($views_addnum_arr[0]);
			$robot_set['views_addnum_max'] = intval($views_addnum_arr[1]);
}else{
		$robot_set['views_addnum'] = intval($robot_set['views_addnum']);
}

?>