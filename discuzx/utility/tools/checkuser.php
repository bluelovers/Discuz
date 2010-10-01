<?php

@set_time_limit(0);

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init();	
	
if(empty($_G['gp_do'])) {
	showmessage('本工具將刪除用戶中重複的錯誤數據，請確認您的數據庫已經進行了備份？<br /><a href="checkuser.php?do=yes">我確認已備份數據，繼續操作</a>');
} else {
	$query = DB::query("SELECT username FROM ".DB::table('common_member')." GROUP BY username HAVING COUNT(username) > 1");
	
	$message = $uids = array();
	while($member = DB::fetch($query)) {
		$squery = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username='".addslashes($member['username'])."' ORDER BY uid");		
		if(DB::num_rows($squery) > 1) {
			$message[] = $member['username'];
			$k = 0;
			while($smember = DB::fetch($squery)) {
				if($k) {					
					$uids[] = $smember['uid'];
				}
				$k++;
			}
		}
	}
	
	if($uids) {
		//require_once libfile('function/delete');
		deletemember(implode(',', $uids), 0);
		loaducenter();
		uc_user_delete($uids);
		fixindex();
		showmessage('以下重複用戶已處理：<br />'.implode(', ', $message), '', array(), array('alert' => 'right'));
	} else {
		fixindex();
		showmessage('操作完成，無重複用戶！', '', array(), array('alert' => 'right'));
	}
}

function deletemember($uids, $other = 1) {
	$numdeleted = DB::result_first("SELECT count(*) FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
	DB::query("DELETE FROM ".DB::table('common_member_field_forum')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_field_home')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_count')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_log')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_profile')." WHERE uid IN ($uids)", 'UNBUFFERED');
	//DB::query("DELETE FROM ".DB::table('common_member_verify')." WHERE uid IN ($uids)", 'UNBUFFERED');
	//DB::query("DELETE FROM ".DB::table('common_member_verify_info')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_status')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_validate')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_member_magic')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_domain')." WHERE id IN ($uids) AND idtype='home'", 'UNBUFFERED');

	DB::query("DELETE FROM ".DB::table('forum_access')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('forum_moderator')." WHERE uid IN ($uids)", 'UNBUFFERED');

	if($other) {
		deleteattach("uid IN ($uids)");
		deletepost("authorid IN ($uids)", true, false);		
	}	

	//note 刪除空間信息
	//feed
	DB::query("DELETE FROM ".DB::table('home_feed')." WHERE uid IN ($uids) OR (id IN ($uids) AND idtype='uid')", 'UNBUFFERED');

	//note 記錄
	$doids = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE uid IN ($uids)");
	while ($value = DB::fetch($query)) {
		$doids[$value['doid']] = $value['doid'];
	}
	
	DB::query("DELETE FROM ".DB::table('home_doing')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 刪除記錄回復
	$delsql = !empty($doids) ? "doid IN (".dimplode($doids).") OR " : "";
	DB::query("DELETE FROM ".DB::table('home_docomment')." WHERE $delsql uid IN ($uids)", 'UNBUFFERED');

	//note 分享
	DB::query("DELETE FROM ".DB::table('home_share')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 相冊數據
	DB::query("DELETE FROM ".DB::table('home_album')." WHERE uid IN ($uids)", 'UNBUFFERED');
	
	//note 刪除積分記錄
	DB::query("DELETE FROM ".DB::table('common_credit_rule_log')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('common_credit_rule_log_field')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 刪除通知
	DB::query("DELETE FROM ".DB::table('home_notification')." WHERE (uid IN ($uids) OR authorid IN ($uids))", 'UNBUFFERED');

	//note 刪除打招呼
	DB::query("DELETE FROM ".DB::table('home_poke')." WHERE (uid IN ($uids) OR fromuid IN ($uids))", 'UNBUFFERED');

	//note 刪除圖片附件
	$query = DB::query("SELECT filepath, thumb, remote FROM ".DB::table('home_pic')." WHERE uid IN ($uids)");
	while ($value = DB::fetch($query)) {
		deletepicfiles($value);
	}

	//note 數據
	DB::query("DELETE FROM ".DB::table('home_pic')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note blog
	//note 數據刪除
	DB::query("DELETE FROM ".DB::table('home_blog')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('home_blogfield')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 評論
	DB::query("DELETE FROM ".DB::table('home_comment')." WHERE (uid IN ($uids) OR authorid IN ($uids) OR (id IN ($uids) AND idtype='uid'))", 'UNBUFFERED');

	//note 訪客
	DB::query("DELETE FROM ".DB::table('home_visitor')." WHERE (uid IN ($uids) OR vuid IN ($uids))", 'UNBUFFERED');

	//note class
	DB::query("DELETE FROM ".DB::table('home_class')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 好友
	DB::query("DELETE FROM ".DB::table('home_friend')." WHERE (uid IN ($uids) OR fuid IN ($uids))", 'UNBUFFERED');

	//note 刪除腳印
	DB::query("DELETE FROM ".DB::table('home_clickuser')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//刪除邀請記錄
	DB::query("DELETE FROM ".DB::table('common_invite')." WHERE (uid IN ($uids) OR fuid IN ($uids))", 'UNBUFFERED');

	//note 刪除郵件隊列
	DB::query("DELETE FROM ".DB::table('common_mailcron').", ".DB::table('common_mailqueue')." USING ".DB::table('common_mailcron').", ".DB::table('common_mailqueue')." WHERE ".DB::table('common_mailcron').".touid IN ($uids) AND ".DB::table('common_mailcron').".cid=".DB::table('common_mailqueue').".cid", 'UNBUFFERED');

	//note 漫遊邀請
	DB::query("DELETE FROM ".DB::table('common_myinvite')." WHERE (touid IN ($uids) OR fromuid IN ($uids))", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('home_userapp')." WHERE uid IN ($uids)", 'UNBUFFERED');
	DB::query("DELETE FROM ".DB::table('home_userappfield')." WHERE uid IN ($uids)", 'UNBUFFERED');

	//note 排行榜
	DB::query("DELETE FROM ".DB::table('home_show')." WHERE uid IN ($uids)", 'UNBUFFERED');

	manyoulog('user', $uids, 'delete');//note Manyou Log

	require_once libfile('function/forum');
	foreach(explode(',', $uids) as $uid) {
		my_thread_log('deluser', array('uid' => $uid));
	}

	DB::query("DELETE FROM ".DB::table('common_member')." WHERE uid IN ($uids)", 'UNBUFFERED');
	return $numdeleted;
}

function fixindex() {
	DB::query("ALTER TABLE ".DB::table('common_member')." DROP INDEX `username`", 'SILENT');
	DB::query("ALTER TABLE ".DB::table('common_member')." ADD UNIQUE `username` (`username`)", 'SILENT');
}

?>