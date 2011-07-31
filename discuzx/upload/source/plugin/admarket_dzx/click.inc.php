<?php

/*
	Advertisement Centre Click Manage Program For Discuz! X2 by sw08
	廣告商城 點擊服務程序
	最後修改:2011-7-20 11:40:55
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADVMARKET')) {
	exit('Access Denied');
}

$clickaid = intval($_G['gp_clickaid']);
$discuz_uid		= $_G['uid'];
$username		= $_G['username'];
$groupid = $_G['groupid'];
$adminid		= $_G['adminid'];
$timestamp		= TIMESTAMP;
$timeoffset		= $_G['setting']['timeoffset'];
$dateformat		= $_G['setting']['dateformat'];
$timeformat		= $_G['setting']['timeformat'];
$var	= $_G['cache']['plugin']['admarket_dzx'];
$isadmin = in_array($discuz_uid, explode(",",$var['admin'])) ? TRUE : FALSE;

if(!empty($_SERVER['HTTP_CLIENT_IP'])){
  $userip = $_SERVER['HTTP_CLIENT_IP'];
}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
  $userip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
  $userip = $_SERVER['REMOTE_ADDR'];
}

if(!$var['open'] && !$isadmin)showmessage('操作錯誤','plugin.php?id=admarket_dzx:admarket');
if(!$clickaid)showmessage('操作錯誤','plugin.php?id=admarket_dzx:admarket');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE stats='4' AND expire > $timestamp AND redirectlink!='' AND id='$clickaid'");
		if(!($clickdata = DB::fetch($query)))showmessage('操作錯誤','plugin.php?id=admarket_dzx:admarket');	

    if(($clickdata['paypolicy'] == 0 && !$clickdata['restcount']) || ($clickdata['paypolicy'] == 1 && $clickdata['expire'] < $timestamp))showmessage('操作錯誤','plugin.php?id=admarket_dzx:admarket');
    
    $countnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='3' AND dateline>=$timestamp-$timestamp%86400 AND (uid='$discuz_uid' OR username='$userip') AND extra='$clickaid'");
    
    $query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='3' AND dateline>=$timestamp - $clickdata[clicktime]*60 AND (uid='$discuz_uid' OR username='$userip') AND extra='$clickaid'");
    if(!($checkdata = DB::fetch($query)) && $countnum < $clickdata['maxpayday'] && $clickdata['buyuid'] != $discuz_uid){
    	DB::fetch_first("UPDATE ".DB::table('advmarket')." SET count=count+1,usecount=usecount+1 WHERE id='".$clickaid."'");

      if($clickdata['paypolicy'] == 0 && $clickdata['restcount']){
    	  DB::fetch_first("UPDATE ".DB::table('advmarket')." SET restcount=restcount-1 WHERE id='".$clickaid."'");
      }

    	if($discuz_uid && $clickdata['clickext'] && $clickdata['totalfee'] >= $clickdata['clickpay'] && $clickdata['totalfee']){
    		 DB::fetch_first("UPDATE ".DB::table('advmarket')." SET totalfee=totalfee-clickpay WHERE id='".$clickaid."'");    		 
    		 updatemembercount($discuz_uid, array("extcredits{$clickdata['clickext']}" => $clickdata['clickpay']), true,'',0);
    		 notification_add($discuz_uid, "廣告商城信息", "恭喜，您獲得了{$clickdata['clickpay']}{$_G[setting][extcredits][$clickdata[clickext]][title]}點擊廣告的積分紅包", '', 1);
    	}    	
    	
    	$clickusename = $username ? $username : '來自'.$userip.'的遊客';
    	DB::insert('advmarket_operatelog', array('action' => "您的$clickdata[name]被{$clickusename}進行了一次有效點擊",'typeid' => 3, 'dateline' => $timestamp, 'uid' => $clickdata['buyuid'], 'username' => $clickdata['buyusername']));
    	DB::insert('advmarket_operatelog', array('extra' => "$clickaid", 'action' => "點擊$clickdata[name]，並獲得{$clickdata['clickpay']}{$_G[setting][extcredits][$clickdata[clickext]][title]}點擊廣告的積分紅包",'typeid' => 3, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $userip));    	
    }
    
    header("location: ".$clickdata['redirectlink']);
    
?>