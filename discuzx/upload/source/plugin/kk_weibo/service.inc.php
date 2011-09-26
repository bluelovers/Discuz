<?php
    if(!defined('IN_DISCUZ')) exit('Access Denied');
	if(empty($_G['uid'])) showmessage('请登录后操作');
	$kk_weibo=$_G['cache']['plugin']['kk_weibo'];
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
	function kk_weibo_sendnotify($to_uid,$tpl,$param) {		
		foreach($param as $key=>$value) $tpl=str_replace('{'.$key.'}',$value,$tpl);
		notification_add($to_uid,9696,$tpl,'',1);
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
			if($kk_weibo['send_notify']) {
				$from_user=getuserbyuid($uid);
				kk_weibo_sendnotify($uid_rel,$kk_weibo['send_notify_tpl'],Array(
					'uid'		=> $uid,
					'username' 	=> $from_user['username'],
					'url_fans'	=> '/plugin.php?id=kk_weibo:weibo&view=fans',
				));
			}
			$description="添加关注成功。";
		}else {
			$description="您之前已经关注过 他/她 了，不能重覆关注。";
		}
				
	} else if($action=='del') {		
		$row=kk_weibo_fetch($uid_rel);
		if(!empty($row)) {			
			DB::query("delete from {$table} where uid={$uid} and uid_rel={$uid_rel}");			
			kk_weibo_update($uid_rel);
			$description="取消关注成功。";
		}else {
			$description="您都还没有关注 他/她，谈何取消呢？";
		}
		
	}
	
	showmessage($description,
			dreferer(),
			array(),
			array('showmsg' => 1, 'showdialog' => 1, 'locationtime' => 3));	
?>