<?php
    if(!defined('IN_DISCUZ')) exit('Access Denied');
	if(empty($_G['uid'])) showmessage('请登录后操作');
	//-------------------------------------------------------------------------------------------------
	function kk_weibo_check() {
		global $action,$uid,$uid_rel;
		if(!in_array($action,Array('add','del'))) showMessage('参数无效');
		if($uid_rel<=0) showMessage('参数无效');		
		if($uid==$uid_rel) showMessage('参数无效');
	}
	function kk_weibo_fetch($rel) {
		global $uid,$table;
		$sql="select uid from {$table} where uid={$uid} and uid_rel={$rel}";
		return DB::fetch_first($sql);
	}	
	function kk_weibo_update($rel) {
		global $uid,$table,$table_stat,$table_stat_s;
		$count=DB::fetch_first("select count(*) as t_count from {$table} where uid_rel={$rel}");
		$row=DB::fetch_first("select uid from {$table_stat} where uid={$rel}");
		$data=Array('uid'=>$rel,'count_fans'=>$count['t_count']);		
		if(empty($row)) DB::insert($table_stat_s,$data); else DB::update($table_stat_s,$data,"uid={$rel}");		
		
		$count=DB::fetch_first("select count(*) as t_count from {$table} where uid={$uid}");
		$row=DB::fetch_first("select uid from {$table_stat} where uid={$uid}");
		$data=Array('uid'=>$uid,'count_attention'=>$count['t_count']);
		if(empty($row)) DB::insert($table_stat_s,$data); else DB::update($table_stat_s,$data,"uid={$uid}");		
	}
	//-------------------------------------------------------------------------------------------------
	$action=$_G['gp_action']; $uid=$_G['uid']; $uid_rel=(int)$_G['gp_rel'];
	kk_weibo_check();
	
	$table=DB::table('kk_weibo'); $table_stat_s='kk_weibo_stat'; $table_stat=DB::table($table_stat_s);	
	$description="";
	if($action=='add') {
		$row=kk_weibo_fetch($uid_rel);
		if(empty($row)) {			
			DB::query("insert into {$table}(uid,uid_rel) values({$uid},{$uid_rel})");
			kk_weibo_update($uid_rel);
		}
		$description="添加关注成功";			
	} else if($action=='del') {		
		$row=kk_weibo_fetch($uid_rel);
		if(!empty($row)) {			
			DB::query("delete from {$table} where uid={$uid} and uid_rel={$uid_rel}");			
			kk_weibo_update($uid_rel);
		}
		$description="取消关注成功";
	}
	
	showmessage($description,
			dreferer(),
			array(),
			array('showmsg' => 1, 'showdialog' => 1, 'locationtime' => 3));	
?>