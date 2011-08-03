<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$processname = 'dsu_amu_call,e';
$ywzx = discuz_process::islocked($processname, 500);
if(!$ywzx) {
	$adds=strip_tags($_G['gp_adds']);
	$_G['gp_keywords'] = str_replace("'","",$_G['gp_keywords']);
	$_G['gp_keywords'] = str_replace('"',"",$_G['gp_keywords']);
	$key=strip_tags($_G['gp_keywords'])?strip_tags($_G['gp_keywords']):mt_rand(1,9);
	$lists = array();
	if($key && submitcheck('callmesubmit') && $_G['uid']){
		$len = strlen($key);
		$sql1="SELECT fusername FROM ".DB::table('home_friend')." WHERE uid = '".$_G['uid']."' AND fusername LIKE '".$key."%' LIMIT 0 ,8";
		$query = DB::query($sql1);
		while ($value = DB::fetch($query)){
			$value = $value['fusername'];		
			$lists[] = $value;
			$listnames[] = $value['fusername'];
		}
		$i = 8 - count($listnames);
		if($i>0){
			$sql2="SELECT username FROM ".DB::table('common_member')." WHERE username LIKE '".$key."%' LIMIT 0 ,".$i;
		}
		$query = DB::query($sql2);
		while ($value = DB::fetch($query)){
			if(!in_array($value['username'],$listnames)){
				$value = $value['username'];
				$lists[] = $value;
			}
		}	
	}
}
discuz_process::unlock($processname);
include template('amucallme_dzx:ajax');

?>
