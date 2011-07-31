<?php

/*
	Advertisement Centre Shop List For Discuz! X2 by sw08
	廣告商城 前台程序
	最後修改:2011-7-21 22:31:18
*/
define('IN_ADVMARKET', TRUE);
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('usergroups');
include './source/function/function_cache.php';

$navtitle = '自助廣告商城';

$aid = intval($_G['gp_aid']);
$clickaid = intval($_G['gp_clickaid']);
$do = dhtmlspecialchars($_G['gp_do']);
$opaction	= $_G['gp_opaction'] ? dhtmlspecialchars($_G['gp_opaction']) : 'index';
$orderby	= $_G['gp_orderby'] ? dhtmlspecialchars($_G['gp_orderby']) : 'dateline';
$page	= $_G['gp_page'] ? dhtmlspecialchars($_G['gp_page']) : 1;
$discuz_uid		= $_G['uid'];
$username		= $_G['username'];
$tpp = $_G['tpp'];
$groupid = $_G['groupid'];
$adminid		= $_G['adminid'];
$timestamp		= TIMESTAMP;
$timeoffset		= $_G['setting']['timeoffset'];
$dateformat		= $_G['setting']['dateformat'];
$timeformat		= $_G['setting']['timeformat'];
$var	= $_G['cache']['plugin']['admarket_dzx'];
$isadmin = in_array($discuz_uid, explode(",",$var['admin'])) ? TRUE : FALSE;
$extcredits[1] = getuserprofile('extcredits1');
$extcredits[2] = getuserprofile('extcredits2');
$extcredits[3] = getuserprofile('extcredits3');
$extcredits[4] = getuserprofile('extcredits4');
$extcredits[5] = getuserprofile('extcredits5');
$extcredits[6] = getuserprofile('extcredits6');
$extcredits[7] = getuserprofile('extcredits7');
$extcredits[8] = getuserprofile('extcredits8');

if($clickaid){
	include('click.inc.php');
	exit();
}

if(!$discuz_uid)showmessage('to_login', 'member.php?mod=logging&action=login', array(), array('showmsg' => true, 'login' => 1));
if(!$var['open'] && !$isadmin)showmessage('對不起，自助廣告商城正在維護中，僅管理員能訪問，請返回');

if(!$opaction || $opaction == 'index'){
	
	$location = '首頁';
	$adminlist = '';
	foreach(explode(",",$var['admin']) as $uid){
	  $users = DB::fetch_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='".$uid."' LIMIT 1");
	  $adminlist .= '<a href="home.php?mod=space&uid='.$uid.'">'.$users['username'].'</a> ';
  }		

}elseif($opaction == 'new'){
	
	  $location = '廣告商店';
	
  if(!$do){
 
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket')." WHERE stats IN (2,3) AND pubuid='0' order by orders ASC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=new');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE stats IN (2,3) AND pubuid='0' order by orders ASC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {
			
			$buy['pubdateline'] = $buy['pubdateline'] ? gmdate($dateformat, $buy['pubdateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['pubdateline'] + $timeoffset * 3600) : '-';
			$buy['buydateline'] = $buy['buydateline'] ? gmdate($dateformat, $buy['buydateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['buydateline'] + $timeoffset * 3600) : '-';

	    $buy['stat'] = statsconvert($buy['stats']);	
      $buy['type'] = typeconvert($buy['type']);
	 
			$buylist[] = $buy;
		}	
		
	}elseif($do == 'buy'){
		
		$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid'");
		$shopdata = DB::fetch($query);		
			$edate = $shopdata['pubdateline'] + $var['recyleday']*86400;
			$pdate = $shopdata['pubdateline'];
			$bdate = $shopdata['buydateline'];
			$shopdata['edateline'] = $shopdata['pubdateline'] ? gmdate($dateformat, $shopdata['pubdateline'] + $timeoffset * 3600 + $var['recyleday']*86400).' '.gmdate($timeformat,$shopdata['pubdateline'] + $timeoffset * 3600 + $var['recyleday']*86400) : '-';		
			$shopdata['buydateline'] = $shopdata['buydateline'] ? gmdate($dateformat, $shopdata['buydateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$shopdata['buydateline'] + $timeoffset * 3600) : '-';
			$shopdata['pubdateline'] = $shopdata['pubdateline'] ? gmdate($dateformat, $shopdata['pubdateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$shopdata['pubdateline'] + $timeoffset * 3600) : '-';

	    $shopdatas['type'] = $shopdata['type'];
	    $shopdatas['showway'] = $shopdata['showway'];
	    $shopdatas['showplace'] = $shopdata['showplace'];
	    
	    $shopdata['stat'] = statsconvert($shopdata['stats']);	
      $shopdata['type'] = typeconvert($shopdata['type']);
			$shopdata['showway'] = showwayconvert($shopdata['showway']);
			$shopdata['showplace'] = showplaceconvert($shopdata['showplace']);
			
			$allowbuygroup = explode(",",$shopdata['allowbuygroup']);
			$allowrefundgroup = explode(",",$shopdata['allowrefundgroup']);
			$allowsellgroup = explode(",",$shopdata['allowsellgroup']);
			$allowautiongroup = explode(",",$shopdata['allowautiongroup']);	
			
		if(!submitcheck('submit')){
			
			if($shopdata['stats'] != 2 && $shopdata['stats'] != 3)showmessage('對不起，該廣告位不允許購買，請返回');
			if($shopdata['pubuid'])showmessage('對不起，該廣告位不允許購買，請返回');
			
		}else{
			
			$payday = intval($_G['gp_payday']);
			
			if(!$payday)showmessage('數量填寫有誤，請返回');
			
			if($shopdata['stats'] == 2){
				
				if($payday < $shopdata['minbuy'] || $payday > $shopdata['maxbuy'])showmessage('對不起，您的購買數量有誤，請返回');
				if(!in_array($groupid, $allowbuygroup) && $shopdata['allowbuygroup'])showmessage('對不起，您所在的用戶組不允許購買該廣告，請返回');		
				if($extcredits[$shopdata['sellext']] < $payday * $shopdata['price'])showmessage('對不起，您的積分不足，請返回');
				
				if($shopdata['paypolicy'] == 1 ){
				  $sqladd = "expire=$timestamp+86400*$payday,";
				}else{		
					$sqladd = "restcount='$payday',expire=$timestamp+86400*90,";			
				}
				
				if($shopdata['catagory'] == 0 && !$shopdata['bid']){
					
	        DB::query("INSERT INTO ".DB::table('common_advertisement')." (available,type,title,targets)
	         	VALUES ('1', '".placeconvert($shopdatas['type'])."', '$shopdata[name]', '".wayconvert($shopdatas['showplace'])."')");
	        $newbid = DB::insert_id();						
					$sqladd .= "bid='$newbid',";
					
				}elseif($shopdata['catagory'] == 1 && !$shopdata['bid']){
					
	        $extent = pow(2, 4 - $shopdatas['showplace']);
	        DB::query("INSERT INTO ".DB::table('common_relatedlink')." (name, extent)
	         	VALUES ('$shopdata[name]', '$extent')");
	        $newbid = DB::insert_id();		
	        $sqladd .= "bid='$newbid',";
	        			
				}
				
				if(in_array($groupid, is_array(unserialize($var['noverify'])) ? unserialize($var['noverify']) : array(unserialize($var['noverify'])))){$verify='0';}else{$verify='1';};
				
				updatemembercount($discuz_uid, array("extcredits{$shopdata['sellext']}" => -$shopdata['price']*$payday), true,'',0);
				$cost = $shopdata['price']*$payday;
				DB::insert('advmarket_operatelog', array('action' => "花費$cost{$_G[setting][extcredits][$shopdata[sellext]][title]}購買$shopdata[name]",'typeid' => 1, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));
				
				DB::fetch_first("UPDATE ".DB::table('advmarket')." SET $sqladd verify='$verify', stats='4',buyuid='$discuz_uid',buyusername='$username',buydateline='$timestamp' WHERE id='".$aid."'");
				
			}else{
				
				if(!in_array($groupid, $allowbuygroup) && $shopdata['allowbuygroup'])showmessage('對不起，您所在的用戶組不允許購買該廣告，請返回');		
				if($extcredits[$shopdata['sellext']] < $payday || $shopdata['price'] >= $payday)showmessage('對不起，您的積分不足或您的出價不夠，請返回');
				if($timestamp > $edate)showmessage('對不起，拍賣已經結束，請返回');				
				if(in_array($groupid, is_array(unserialize($var['noverify'])) ? unserialize($var['noverify']) : array(unserialize($var['noverify'])))){$verify='0';}else{$verify='1';};
			
				updatemembercount($shopdata['buyuid'], array("extcredits{$shopdata['sellext']}" => $shopdata['price']), true,'',0);			
				updatemembercount($discuz_uid, array("extcredits{$shopdata['sellext']}" => -$payday), true,'',0);
				
				if($shopdata['buyuid'])notification_add($shopdata['buyuid'], "廣告商城信息", "很遺憾，您在$shopdata[name]的拍賣出價被其他人超過，系統已經將$shopdata[price]{$_G[setting][extcredits][$shopdata[sellext]][title]}返還給您", '', 1);
			
				DB::insert('advmarket_operatelog', array('action' => "花費$shopdata[price]{$_G[setting][extcredits][$shopdata[sellext]][title]}出價競拍$shopdata[name]",'typeid' => 2, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));

  			DB::fetch_first("UPDATE ".DB::table('advmarket')." SET price='$payday', verify='$verify', buyuid='$discuz_uid',buyusername='$username',buydateline='$timestamp' WHERE id='".$aid."'");		
				
			}
			
			updatecache('relatedlink');
			updatecache('advs');
      updatecache('setting');
			showmessage('感謝你的支持，現在轉回出售列表','plugin.php?id=admarket_dzx:admarket&opaction=new');
			
		}
		
	}

}elseif($opaction == 'secondhand'){
	
	  $location = '二手市場';

  if(!$do){
 
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket')." WHERE stats IN (2,3) AND pubuid!='0' order by orders ASC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=secondhand');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE stats IN (2,3) AND pubuid!='0' order by orders ASC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {
			
			$buy['pubdateline'] = $buy['pubdateline'] ? gmdate($dateformat, $buy['pubdateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['pubdateline'] + $timeoffset * 3600) : '-';
			$buy['buydateline'] = $buy['buydateline'] ? gmdate($dateformat, $buy['buydateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['buydateline'] + $timeoffset * 3600) : '-';

	    $buy['stat'] = statsconvert($buy['stats']);	
      $buy['type'] = typeconvert($buy['type']);
	 
			$buylist[] = $buy;
		}	
		
	}elseif($do == 'buy'){
		
		$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid'");
		$shopdata = DB::fetch($query);		
			$edate = $shopdata['pubdateline'] + $var['recyleday']*86400;
			$pdate = $shopdata['pubdateline'];
			$bdate = $shopdata['buydateline'];
			$shopdata['edateline'] = $shopdata['pubdateline'] ? gmdate($dateformat, $shopdata['pubdateline'] + $timeoffset * 3600 + $var['recyleday']*86400).' '.gmdate($timeformat,$shopdata['pubdateline'] + $timeoffset * 3600 + $var['recyleday']*86400) : '-';		
			$shopdata['buydateline'] = $shopdata['buydateline'] ? gmdate($dateformat, $shopdata['buydateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$shopdata['buydateline'] + $timeoffset * 3600) : '-';
			$shopdata['pubdateline'] = $shopdata['pubdateline'] ? gmdate($dateformat, $shopdata['pubdateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$shopdata['pubdateline'] + $timeoffset * 3600) : '-';

	    $shopdata['stat'] = statsconvert($shopdata['stats']);	
      $shopdata['type'] = typeconvert($shopdata['type']);
			$shopdata['showway'] = showwayconvert($shopdata['showway']);
			$shopdata['showplace'] = showplaceconvert($shopdata['showplace']);
			
			$allowbuygroup = explode(",",$shopdata['allowbuygroup']);
			$allowrefundgroup = explode(",",$shopdata['allowrefundgroup']);
			$allowsellgroup = explode(",",$shopdata['allowsellgroup']);
			$allowautiongroup = explode(",",$shopdata['allowautiongroup']);	
			
		if(!submitcheck('submit')){
			
			if($shopdata['stats'] != 2 && $shopdata['stats'] != 3)showmessage('對不起，該廣告位不允許購買，請返回');
			if(!$shopdata['pubuid'])showmessage('對不起，該廣告位不允許購買，請返回');
			
		}else{
			
			$payday = intval($_G['gp_payday']);
			
			if(!$payday)showmessage('數量填寫有誤，請返回');
			
			if($shopdata['stats'] == 2){
				
				if($payday < $shopdata['minbuy'] || $payday > $shopdata['maxbuy'])showmessage('對不起，您的購買數量有誤，請返回');
				if(!in_array($groupid, $allowbuygroup) && $shopdata['allowbuygroup'])showmessage('對不起，您所在的用戶組不允許購買該廣告，請返回');		
				if($extcredits[$shopdata['sellext']] < $payday * $shopdata['price'])showmessage('對不起，您的積分不足，請返回');
				
				if($shopdata['paypolicy'] == 1 ){
				  $sqladd = "expire=$timestamp+86400*$payday,";
				}else{		
					$sqladd = "restcount='$payday',expire=$timestamp+86400*90,";			
				}
				
				if(in_array($groupid, is_array(unserialize($var['noverify'])) ? unserialize($var['noverify']) : array(unserialize($var['noverify'])))){$verify='0';}else{$verify='1';};
				
				updatemembercount($discuz_uid, array("extcredits{$shopdata['sellext']}" => -$shopdata['price']*$payday), true,'',0);
				
				updatemembercount($shopdata['pubuid'], array("extcredits{$shopdata['sellext']}" => $shopdata['price']*$payday), true,'',0);
				
				$cost = $shopdata['price']*$payday;
				notification_add($shopdata['pubuid'], "廣告商城信息", "恭喜，您出售的$shopdata[name]被購買，您獲得了$cost{$_G[setting][extcredits][$shopdata[sellext]][title]}", '', 1);
				
				DB::insert('advmarket_operatelog', array('action' => "花費$cost{$_G[setting][extcredits][$shopdata[sellext]][title]}購買$shopdata[name]",'typeid' => 1, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));
				DB::insert('advmarket_operatelog', array('action' => "出售的$shopdata[name]被{$username}以$cost{$_G[setting][extcredits][$shopdata[sellext]][title]}購買",'typeid' => 1, 'dateline' => $timestamp, 'uid' => $shopdata['pubuid'], 'username' => $shopdata['pubusername']));
				
				DB::fetch_first("UPDATE ".DB::table('advmarket')." SET $sqladd verify='$verify', stats='4',pubuid='0',pubusername='',buyuid='$discuz_uid',buyusername='$username',buydateline='$timestamp' WHERE id='".$aid."'");
				
			}else{
				
				if(!in_array($groupid, $allowbuygroup) && $shopdata['allowbuygroup'])showmessage('對不起，您所在的用戶組不允許購買該廣告，請返回');		
				if($extcredits[$shopdata['sellext']] < $payday || $shopdata['price'] >= $payday)showmessage('對不起，您的積分不足或您的出價不夠，請返回');
				if($timestamp > $edate)showmessage('對不起，拍賣已經結束，請返回');				
				if(in_array($groupid, is_array(unserialize($var['noverify'])) ? unserialize($var['noverify']) : array(unserialize($var['noverify'])))){$verify='0';}else{$verify='1';};
			
				updatemembercount($shopdata['buyuid'], array("extcredits{$shopdata['sellext']}" => $shopdata['price']), true,'',0);			
				updatemembercount($discuz_uid, array("extcredits{$shopdata['sellext']}" => -$payday), true,'',0);
				
				if($shopdata['buyuid'])notification_add($shopdata['buyuid'], "廣告商城信息", "很遺憾，您在$shopdata[name]的拍賣出價被其他人超過，系統已經將$shopdata[price]{$_G[setting][extcredits][$shopdata[sellext]][title]}返還給您", '', 1);
			  notification_add($shopdata['pubuid'], "廣告商城信息", "您拍賣的$shopdata[name]有新的出價，出價爲$payday{$_G[setting][extcredits][$shopdata[sellext]][title]}", '', 1);
			
				DB::insert('advmarket_operatelog', array('action' => "拍賣的$shopdata[name]被{$username}以$payday{$_G[setting][extcredits][$shopdata[sellext]][title]}出價競拍",'typeid' => 2, 'dateline' => $timestamp, 'uid' => $shopdata['pubuid'], 'username' => $shopdata['pubusername']));
				DB::insert('advmarket_operatelog', array('action' => "花費$payday{$_G[setting][extcredits][$shopdata[sellext]][title]}出價競拍$shopdata[name]",'typeid' => 2, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));

  			DB::fetch_first("UPDATE ".DB::table('advmarket')." SET price='$payday', verify='$verify',buyuid='$discuz_uid',buyusername='$username',buydateline='$timestamp' WHERE id='".$aid."'");		
				
			}
			
			showmessage('感謝你的支持，現在轉回出售列表','plugin.php?id=admarket_dzx:admarket&opaction=secondhand');
			
		}
		
	}

}elseif($opaction == 'myad'){
	  
	  $location = '我的廣告位';
	  
	  if(!$do){
	  	
	  $count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket')." WHERE stats IN (1,4) AND buyuid='$discuz_uid' order by orders ASC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=myad');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE stats IN (1,4) AND buyuid='$discuz_uid' order by orders ASC LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {
			
			$buy['pubdateline'] = $buy['pubdateline'] ? gmdate($dateformat, $buy['pubdateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['pubdateline'] + $timeoffset * 3600) : '-';
			$buy['buydateline'] = $buy['buydateline'] ? gmdate($dateformat, $buy['buydateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['buydateline'] + $timeoffset * 3600) : '-';
	    $buy['stat'] = statsconvert($buy['stats']);	
      $buy['type'] = typeconvert($buy['type']);
	 
			$buylist[] = $buy;
		}	
	  	
	  }elseif($do == 'refund'){
	  	
	  	$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid'");
		  $shopdata = DB::fetch($query);
			$allowbuygroup = explode(",",$shopdata['allowbuygroup']);
			$allowrefundgroup = explode(",",$shopdata['allowrefundgroup']);
			$allowsellgroup = explode(",",$shopdata['allowsellgroup']);
			$allowautiongroup = explode(",",$shopdata['allowautiongroup']);	
	  	
	  	if(!submitcheck('submit')){
	  	  
	  	  if($shopdata['buyuid'] != $discuz_uid)showmessage('對不起，你無權進行該操作');
	  	  if($shopdata['stats'] != 1 && $shopdata['stats'] != 4)showmessage('對不起，你無權進行該操作');
	  		if(!in_array($groupid, $allowrefundgroup) && $shopdata['allowrefundgroup'])showmessage('對不起，您所在的用戶組不允許退款，請返回');
	  		
	  	}else{

			    if($shopdata['catagory'] == 0){	
			    	DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE advid ='".$shopdata['bid']."'");					
			    }else{
				    DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='0' WHERE id ='".$shopdata['bid']."'");			
			    }
	  		
	  		  if($shopdata['clickext'])updatemembercount($discuz_uid, array("extcredits{$shopdata['clickext']}" => $shopdata['totalfee']), true,'',0);
	  		
	  		  DB::insert('advmarket_operatelog', array('action' => "退款$shopdata[name]，並獲得了$shopdata[totalfee]{$_G[setting][extcredits][$shopdata[clickext]][title]}",'typeid' => 1, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));
	  		  
	  		  DB::query("UPDATE ".DB::table('advmarket')." SET stats = '0',pubuid = '0',pubusername = '',buyuid = '0',buyusername = '',buydateline = '0',totalfee = '0',paycount = '0',count = '0',usecount = '0',restcount = '0',expire = '0',redirectlink = '' WHERE id ='$aid'");
	  		
	  		  updatecache('relatedlink');
	  		  updatecache('advs');
          updatecache('setting');
	  		  showmessage('退款成功，現在轉回列表','plugin.php?id=admarket_dzx:admarket&opaction=myad');
	  		
	  	}
	  	
	  }elseif($do == 'fill'){
	  	
	  	$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid'");
		  $shopdata = DB::fetch($query);
			$allowbuygroup = explode(",",$shopdata['allowbuygroup']);
			$allowrefundgroup = explode(",",$shopdata['allowrefundgroup']);
			$allowsellgroup = explode(",",$shopdata['allowsellgroup']);
			$allowautiongroup = explode(",",$shopdata['allowautiongroup']);	
	  	
	  	if(!submitcheck('submit')){	 

	  	  if($shopdata['buyuid'] != $discuz_uid)showmessage('對不起，你無權進行該操作');
	  	  if($shopdata['stats'] != 1 && $shopdata['stats'] != 4)showmessage('對不起，你無權進行該操作');	  		
	  		if(!$shopdata['clickext'])showmessage('對不起，該廣告不支持充值功能，請返回');
	  		 			  		
	  	}else{
	  		
	  		  $fillnum = intval($_G['gp_fillnum']);
	  		  $clickone = intval($_G['gp_clickone']);
	  		
	  		  if($extcredits[$shopdata['clickext']] < $fillnum)showmessage('對不起，您的積分不足，請返回');
	  		  if($clickone > $fillnum + $shopdata['totalfee'])showmessage('對不起，每人獎勵的積分設置錯誤，請返回');
	  		
	  		  updatemembercount($discuz_uid, array("extcredits{$shopdata['clickext']}" => -$fillnum), true,'',0);
	  		
	  		  if($fillnum)DB::insert('advmarket_operatelog', array('action' => "爲$shopdata[name]充值了$fillnum{$_G[setting][extcredits][$shopdata[clickext]][title]}",'typeid' => 1, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));
	  		  
	  		  DB::query("UPDATE ".DB::table('advmarket')." SET clickpay='$clickone', totalfee=totalfee+$fillnum WHERE id ='$aid'");
	  		
	  		  showmessage('充值成功，現在轉回列表','plugin.php?id=admarket_dzx:admarket&opaction=myad');
	  		
	  	}
	  	
	  }elseif($do == 'pay'){
	  	
	  	$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid'");
		  $shopdata = DB::fetch($query);
			$allowbuygroup = explode(",",$shopdata['allowbuygroup']);
			$allowrefundgroup = explode(",",$shopdata['allowrefundgroup']);
			$allowsellgroup = explode(",",$shopdata['allowsellgroup']);
			$allowautiongroup = explode(",",$shopdata['allowautiongroup']);	
			$expire = $shopdata['expire'];
			$shopdata['expire'] = $shopdata['expire'] ? gmdate($dateformat, $shopdata['expire'] + $timeoffset * 3600).' '.gmdate($timeformat,$shopdata['expire'] + $timeoffset * 3600) : '-';
	  	
	  	if(!submitcheck('submit')){	 	  		
	  		
	  	  if($shopdata['buyuid'] != $discuz_uid)showmessage('對不起，你無權進行該操作');
	  	  if($shopdata['stats'] != 1 && $shopdata['stats'] != 4)showmessage('對不起，你無權進行該操作');	  		
	  		 			  		
	  	}else{
	  		
	  		  $paynum = intval($_G['gp_paynum']);
	  		
	  		  if($paynum < $shopdata['minbuy'] || $paynum > $shopdata['maxbuy'] || !$paynum)showmessage('對不起，購買數量有誤，請返回');
	  		
	  		  if($extcredits[$shopdata['sellext']] < $paynum*$shopdata['price'])showmessage('對不起，您的積分不足，請返回');
	  		
	  		  updatemembercount($discuz_uid, array("extcredits{$shopdata['sellext']}" => -$paynum*$shopdata['price']), true,'',0);
	  		
	  		  $cost = $paynum*$shopdata['price'];
	  		  if($cost)DB::insert('advmarket_operatelog', array('action' => "爲$shopdata[name]花費$cost{$_G[setting][extcredits][$shopdata[sellext]][title]}續費",'typeid' => 1, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));
	  		  
	  		  if($shopdata['paypolicy'] == 1){
	  		  	if($expire + $paynum*86400 > $timestamp){  
	  		      DB::query("UPDATE ".DB::table('advmarket')." SET stats='4', expire=expire+$paynum*86400 WHERE id ='$aid'");
	  		      
	  		      if($shopdata['catagory'] == 1){
	  		        DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='".pow(2, 4-$shopdata['showplace'])."' WHERE id ='".$shopdata['bid']."'");
              }else{
	  		        DB::query("UPDATE ".DB::table('common_advertisement')." SET available='1' WHERE advid ='".$shopdata['bid']."'");
	  		      }
	  		    
	  		    }else{
	  		    	DB::query("UPDATE ".DB::table('advmarket')." SET stats='1', expire=expire+$paynum*86400 WHERE id ='$aid'");
	  		    }
	  		  }else{ 		    
	  		      DB::query("UPDATE ".DB::table('advmarket')." SET stats='4', restcount=restcount+$paynum, expire=$timestamp+$var[recyleday]*86400 WHERE id ='$aid'");	  		  	

              if($shopdata['catagory'] == 1){
	  		        DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='".pow(2, 4-$shopdata['showplace'])."' WHERE id ='".$shopdata['bid']."'");
              }else{
	  		        DB::query("UPDATE ".DB::table('common_advertisement')." SET available='1' WHERE advid ='".$shopdata['bid']."'");
	  		      }
    
    		  }		  
	  		  
	  		  updatecache('relatedlink');
	  		  updatecache('advs');
          updatecache('setting');
	  		  showmessage('續費成功，現在轉回列表','plugin.php?id=admarket_dzx:admarket&opaction=myad');
	  		
	  	}	  	
	  	
	  }elseif($do == 'count'){
	  	
	  	$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid'");
		  $shopdata = DB::fetch($query);
			$allowbuygroup = explode(",",$shopdata['allowbuygroup']);
			$allowrefundgroup = explode(",",$shopdata['allowrefundgroup']);
			$allowsellgroup = explode(",",$shopdata['allowsellgroup']);
			$allowautiongroup = explode(",",$shopdata['allowautiongroup']);	
	  	
	  	if(!submitcheck('submit')){	 
	  		
	  	  if($shopdata['buyuid'] != $discuz_uid)showmessage('對不起，你無權進行該操作');
	  	  if($shopdata['stats'] != 1 && $shopdata['stats'] != 4)showmessage('對不起，你無權進行該操作');
	  	  if(!$shopdata['payext'] || !$shopdata['paycount'])showmessage('對不起，該廣告不支持結算，請返回');	  		
	  			  		 			  		
	  	}else{
	  		
	  		  $countnum = intval($_G['gp_countnum']);
	  		 	
	  		 	if(!$countnum)showmessage('對不起，結算次數填寫有誤，請返回');
	  		 	if($countnum%$shopdata['paycount'] != 0)showmessage('對不起，結算次數必須是結算單位的整數倍，請返回');
	  		 	if($countnum > $shopdata['usecount'])showmessage('對不起，可用結算次數不足，請返回');
	  		 		
	  		 	$getpay = $countnum / $shopdata['paycount'] * $shopdata['payfee'];
	  		  updatemembercount($discuz_uid, array("extcredits{$shopdata['payext']}" => $getpay), true,'',0);
	  		
	  		  DB::insert('advmarket_operatelog', array('action' => "結算$shopdata[name]得到$getpay{$_G[setting][extcredits][$shopdata[payext]][title]}，結算次數爲$countnum",'typeid' => 4, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));
	  		  
	  		  DB::query("UPDATE ".DB::table('advmarket')." SET usecount = usecount - $countnum WHERE id ='$aid'");	  		  	
	  		
	  		  showmessage('結算成功，現在轉回列表','plugin.php?id=admarket_dzx:admarket&opaction=myad');
	  		
	  	}	  	
	  	
	  }elseif($do == 'sell'){
	  	
	  	$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid'");
		  $shopdata = DB::fetch($query);
			$allowbuygroup = explode(",",$shopdata['allowbuygroup']);
			$allowrefundgroup = explode(",",$shopdata['allowrefundgroup']);
			$allowsellgroup = explode(",",$shopdata['allowsellgroup']);
			$allowautiongroup = explode(",",$shopdata['allowautiongroup']);	
	  	
	  	if(!submitcheck('submit')){	 
	  		
	  	  if($shopdata['buyuid'] != $discuz_uid)showmessage('對不起，你無權進行該操作');
	  	  if($shopdata['stats'] != 1 && $shopdata['stats'] != 4)showmessage('對不起，你無權進行該操作');	
  		  if(!in_array($groupid, $allowsellgroup) && $shopdata['allowsellgroup'] && !in_array($groupid, $allowautiongroup) && $shopdata['allowautiongroup'])showmessage('對不起，該廣告不允許賣出，請返回');
	  			  		 			  		
	  	}else{
	  		
	  		  $newprice = intval($_G['gp_newprice']);
	  		  $chooseway = intval($_G['gp_chooseway']);
	  		 	
	  		 	if(!$chooseway)showmessage('對不起，請選擇賣出的方式，請返回');
	  		 	if(!$newprice)showmessage('對不起，價格填寫有誤，請返回');	  		 		
	  			  		  
	  		  if($chooseway == 1){
	  		     DB::query("UPDATE ".DB::table('advmarket')." SET stats = '2',pubuid = '$discuz_uid',pubusername = '$username',pubdateline = '$timestamp',buyuid = '0',buyusername = '',buydateline = '0',totalfee = '0',paycount = '0',count = '0',usecount = '0',restcount = '0',expire = '0',redirectlink = '',price='$newprice' WHERE id ='$aid'");	  		  	
	  		     DB::insert('advmarket_operatelog', array('action' => "賣出$shopdata[name]，標注的單位價格爲$newprice{$_G[setting][extcredits][$shopdata[sellext]][title]}",'typeid' => 1, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));
          }elseif($chooseway == 2){
             DB::query("UPDATE ".DB::table('advmarket')." SET stats = '3',pubuid = '$discuz_uid',pubusername = '$username',pubdateline = '$timestamp',buyuid = '0',buyusername = '',buydateline = '0',totalfee = '0',paycount = '0',count = '0',usecount = '0',restcount = '0',expire = '0',redirectlink = '',price='$newprice' WHERE id ='$aid'");	
             DB::insert('advmarket_operatelog', array('action' => "賣出$shopdata[name]，起拍價爲$newprice{$_G[setting][extcredits][$shopdata[sellext]][title]}",'typeid' => 2, 'dateline' => $timestamp, 'uid' => $discuz_uid, 'username' => $username));
          }
          
			    if($shopdata['catagory'] == 0){	
			    	DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE advid ='".$shopdata['bid']."'");					
			    }else{
				    DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='0' WHERE id ='".$shopdata['bid']."'");			
			    }
	  		  
	  		  updatecache('relatedlink');
	  		  updatecache('advs');
          updatecache('setting');
	  		  showmessage('賣出的廣告位已經加到二手市場，現在轉回列表','plugin.php?id=admarket_dzx:admarket&opaction=myad');
	  		
	  	}	  	
	  	
	  }elseif($do == 'edit'){
	  	
	  	loadcache(array('forums', 'grouptype', 'portalcategory'));
	  	
	  	$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid'");
		  $shopdata = DB::fetch($query);
		  
	  	if($shopdata['catagory'] == 1){
	  	  $query = DB::query("SELECT * FROM ".DB::table('common_relatedlink')." WHERE id ='".$shopdata['bid']."'");
		    $linkdata = DB::fetch($query);	
		  }else{
	  	  $query = DB::query("SELECT * FROM ".DB::table('common_advertisement')." WHERE advid ='".$shopdata['bid']."'");
		    $advdata = DB::fetch($query);	
		    $advdata['parameters'] = unserialize($advdata['parameters']);		 		     	  
		  }
		  
	    $stype = $shopdata['type'];
	    $shopdata['stat'] = statsconvert($shopdata['stats']);	
      $shopdata['type'] = typeconvert($shopdata['type']);		  
	    $shopdata['sp'] = showplaceconvert($shopdata['showplace']);	
      $shopdata['sw'] = showwayconvert($shopdata['showway']);
	  	
	  	if(!submitcheck('submit')){	 
	  		
	  	  if($shopdata['buyuid'] != $discuz_uid)showmessage('對不起，你無權進行該操作');
	  	  if($shopdata['stats'] == 1)showmessage('對不起，該廣告位已經被鎖定，需要續費才能繼續使用，請返回');	

	      $channalsel = '<select name="channalsnew[]" '.($shopdata['allowedit'] ? '' : 'disabled').'>';	      	
	     	if(!$shopdata['allowedit'])$channalsel .= '<option value="0" '.(empty($advdata['parameters']['extra']['category']) ? 'selected' : '').'>所有頻道</option>';
	     	
	     	if(in_array(placeconvert($stype), explode(",","subnavbanner,headerbanner,footerbanner,cornerbanner,couplebanner,float")))$channalsel .= '<option value="-1" '.(in_array(-1, $advdata['parameters']['extra']['category']) ? 'selected' : '').'>首頁</option>';
	     	
	     	foreach($_G['cache']['portalcategory'] as $category) {	     		
			     $channalsel .= '<option value="'.$category['catid'].'" '.(in_array($category['catid'], $advdata['parameters']['extra']['category']) ? 'selected' : '').'> '.$category['catname'].'</option>';
		    }		
		    $channalsel .= '</select>';

	      $forumsel = '<select name="fidsnew[]" '.($shopdata['allowedit'] ? '' : 'disabled').'>';	      	
	     	if(!$shopdata['allowedit'])$forumsel .= '<option value="0" '.(empty($advdata['parameters']['extra']['fids']) ? 'selected' : '').'>所有版區</option>';
	     	
	     	if(in_array(placeconvert($stype), explode(",","subnavbanner,headerbanner,footerbanner,cornerbanner,couplebanner,float")))$forumsel .= '<option value="-1" '.(in_array(-1, $advdata['parameters']['extra']['fids']) ? 'selected' : '').'>首頁</option>';
	     	if(in_array(placeconvert($stype), explode(",","headerbanner,footerbanner")))$forumsel .= '<option value="-2" '.(in_array(-2, $advdata['parameters']['extra']['fids']) ? 'selected' : '').'>Archiver</option>';
	     	
	     	foreach($_G['cache']['forums'] as $forumfid => $forum) {	     		
			     $forumsel .= '<option value="'.$forumfid.'" '.(in_array($forumfid, $advdata['parameters']['extra']['fids']) ? 'selected' : '').'> '.($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name'].'</option>';
		    }		
		    $forumsel .= '</select>';
		    
	      $groupssel = '<select name="groupsnew[]" '.($shopdata['allowedit'] ? '' : 'disabled').'>';	      	
	     	if(!$shopdata['allowedit'])$groupssel .= '<option value="0" '.(empty($advdata['parameters']['extra']['groups']) ? 'selected' : '').'>所有群組分類</option>';
	     	
	     		if(in_array(placeconvert($stype), explode(",","subnavbanner,headerbanner,footerbanner,cornerbanner,couplebanner,float")))$groupssel .= '<option value="-1" '.(in_array(-1, $advdata['parameters']['extra']['groups']) ? 'selected' : '').'>首頁</option>';
	     	
	     	foreach($_G['cache']['grouptype']['first'] as $forumfid => $forum) {	     		
			     $groupssel .= '<option value="'.$forumfid.'" '.(in_array($forumfid, $advdata['parameters']['extra']['groups']) ? 'selected' : '').'> '.$forum['name'].'</option>';	    
			     if($forum['secondlist']) {
				      foreach($forum['secondlist'] as $sgid) {	
              $groupssel .= '<option value="'.$sgid.'" '.(in_array($sgid, $advdata['parameters']['extra']['groups']) ? 'selected' : '').'> '.str_repeat('&nbsp;', 4).$_G['cache']['grouptype']['second'][$sgid]['name'].'</option>';				      		
				      }
				   }		    
		    }		
		    $groupssel .= '</select>';		    
	  	  
	      $floorsel = '<select name="pnumbersnew[]" '.($shopdata['allowedit'] ? '' : 'disabled').'>';	      	
	     	if(!$shopdata['allowedit'])$floorsel .= '<option value="0" '.(empty($advdata['parameters']['extra']['pnumber']) ? 'selected' : '').'>所有樓層</option>';
	     	for($i = 1;$i <= $_G['ppp'];$i++) {
			     $floorsel .= '<option value="'.$i.'" '.(in_array($i, $advdata['parameters']['extra']['pnumber']) ? 'selected' : '').'>> #'.$i.'樓</option>';
		    }		
		    $floorsel .= '</select>';	  	  
	  			  		 			  		
	  	}else{
          
			    if($shopdata['catagory'] == 0){	
	
	          $advnew = $_G['gp_advnew'];
					  $advstyle = styleconvert($shopdata['showway']);
					  $advnew['style'] = styleconvert($shopdata['showway']);
					  
					  if($shopdata['showway'] != 1 && in_array($groupid, is_array(unserialize($var['noverify'])) ? unserialize($var['noverify']) : array(unserialize($var['noverify'])))){$verify='0';$available = '1';}else{$verify='1';$available = '0';};
			    	$redirectlink = $var['headlink'].'plugin.php?id=admarket_dzx:admarket&clickaid='.$shopdata['id'];

			      if(placeconvert($stype) == 'threadlist'){
			      	$parameters['extra']['mode'] = $_G['gp_mode'];
			      }elseif(placeconvert($stype) == 'cornerbanner'){		      
			        $parameters['extra']['animator'] = $_G['gp_animator'];
			      }else{
			      	$parameters['extra']['position'] = $_G['gp_position'];
			      }

	      	if($shopdata['showplace'] == 1) {
	      		if($shopdata['allowedit']){	      		
			        $parameters['extra']['category'] = (is_array($_G['gp_channalsnew']) && in_array(0, $_G['gp_channalsnew'])) ? array() : $_G['gp_channalsnew'];
			      }else{
			      	$parameters['extra']['category'] = is_array($advdata['parameters']['extra']['category']) ? $advdata['parameters']['extra']['category'] : array();
			      }
		      }elseif($shopdata['showplace'] == 2) {
		      	if($shopdata['allowedit']){	
			        $parameters['extra']['fids'] = (is_array($_G['gp_fidsnew']) && in_array(0, $_G['gp_fidsnew'])) ? array() : $_G['gp_fidsnew'];
			        if(placeconvert($stype) == 'thread')$parameters['extra']['pnumber'] = (is_array($_G['gp_pnumbersnew']) && in_array(0, $_G['gp_pnumbersnew'])) ? array() : $_G['gp_pnumbersnew'];
			      }else{
			      	$parameters['extra']['fids'] = is_array($advdata['parameters']['extra']['fids']) ? $advdata['parameters']['extra']['fids'] : array();
			      	if(placeconvert($stype) == 'thread')$parameters['extra']['pnumber'] = is_array($advdata['parameters']['extra']['pnumber']) ? $advdata['parameters']['extra']['pnumber'] : array();
			      }			        
		      }elseif($shopdata['showplace'] == 3) {
		      	if($shopdata['allowedit']){	
	         		$parameters['extra']['groups'] = (is_array($_G['gp_groupsnew']) && in_array(0, $_G['gp_groupsnew'])) ? array() : $_G['gp_groupsnew'];
	         		if(placeconvert($stype) == 'thread')$parameters['extra']['pnumber'] = (is_array($_G['gp_pnumbersnew']) && in_array(0, $_G['gp_pnumbersnew'])) ? array() : $_G['gp_pnumbersnew'];
			      }else{
			      	$parameters['extra']['groups'] = is_array($advdata['parameters']['extra']['groups']) ? $advdata['parameters']['extra']['groups'] : array();
			      	if(placeconvert($stype) == 'thread')$parameters['extra']['pnumber'] = is_array($advdata['parameters']['extra']['pnumber']) ? $advdata['parameters']['extra']['pnumber'] : array();
			      }
	      	}	
			      
			      if(placeconvert($stype) == 'custom')$parameters['extra']['customid'] = $stype - 16;
            if(placeconvert($stype) == 'threadlist'){
		          $parameters['extra']['tid'] = intval($_G['gp_advnewtid']);
		          $parameters['extra']['threadurl'] = dstripslashes($_G['gp_advnewthreadurl']);
	          }


            $advnew['targets'] = wayconvert($shopdata['showplace']);

	        	foreach($advnew[$advstyle] as $key => $val) {
			          $advnew[$advstyle][$key] = dstripslashes($val);
		        }
 
 
            $rlink = $advnew[$advstyle]['link'];
            $advnew[$advstyle]['link'] = $redirectlink;
            $advnew['displayorder'] = isset($advnew['displayorder']) ? implode("\t", $advnew['displayorder']) : '';
		        $advnew['code'] = encodeadvcode($advnew);

		        $extra = $type != 'custom' ? '' : '&customid='.$parameters['extra']['customid'];

		        $advnew['parameters'] = addslashes(serialize(array_merge(is_array($parameters) ? $parameters : array(), array('style' => $advstyle), $advstyle == 'code' ? array() : $advnew[$advstyle], array('html' => $advnew['code']), array('displayorder' => $advnew['displayorder']))));
		        $advnew['code'] = addslashes($advnew['code']);
			    	
			    	DB::query("UPDATE ".DB::table('common_advertisement')." SET targets='".$advnew['targets']."', type='".placeconvert($stype)."', parameters='$advnew[parameters]', code='$advnew[code]', available='$available' WHERE advid ='".$shopdata['bid']."'");					
			    	DB::query("UPDATE ".DB::table('advmarket')." SET verify='$verify', redirectlink='$rlink' WHERE id ='".$shopdata['id']."'");
			    	
			    }else{
			    	
			    	$linkname = dhtmlspecialchars($_G['gp_name']);
			    	$link = $_G['gp_link'];			    			    	
			    	$redirectlink = $var['headlink'].'plugin.php?id=admarket_dzx:admarket&clickaid='.$shopdata['id'];
			    	
			    	if(!$linkname || !$link)showmessage('對不起，您沒有填寫相應的信息，請返回');
			    	
				    DB::query("UPDATE ".DB::table('common_relatedlink')." SET name='$linkname',url='$redirectlink' WHERE id ='".$shopdata['bid']."'");
				    DB::query("UPDATE ".DB::table('advmarket')." SET redirectlink='$link' WHERE id ='".$shopdata['id']."'");			
			    
			    }
	  		  
	  		  updatecache('relatedlink');
	  		  updatecache('advs');
          updatecache('setting');
	  		  showmessage('廣告位已經成功編輯，現在轉回列表','plugin.php?id=admarket_dzx:admarket&opaction=myad');
	  		
	  	}	  	
	  	
	  }

}elseif($opaction == 'mybuylog'){
	
		$location = '廣告買賣記錄';
		
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='1' AND uid='$discuz_uid' order by dateline DESC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=mybuylog');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='1' AND uid='$discuz_uid' order by dateline DESC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {			
			$buy['dateline'] = $buy['dateline'] ? gmdate($dateformat, $buy['dateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['dateline'] + $timeoffset * 3600) : '-';
			$buylist[] = $buy;
		}
	
}elseif($opaction == 'myautionlog'){
	
		$location = '拍賣記錄';
		
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='2' AND uid='$discuz_uid' order by dateline DESC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=myautionlog');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='2' AND uid='$discuz_uid' order by dateline DESC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {			
			$buy['dateline'] = $buy['dateline'] ? gmdate($dateformat, $buy['dateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['dateline'] + $timeoffset * 3600) : '-';
			$buylist[] = $buy;
		}
	
}elseif($opaction == 'myclicklog'){
	
		$location = '點擊記錄';
		
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='3' AND uid='$discuz_uid' order by dateline DESC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=myclicklog');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='3' AND uid='$discuz_uid' order by dateline DESC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {			
			$buy['dateline'] = $buy['dateline'] ? gmdate($dateformat, $buy['dateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['dateline'] + $timeoffset * 3600) : '-';
			$buylist[] = $buy;
		}
		
}elseif($opaction == 'mypaylog'){
	
		$location = '結算記錄';
		
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='4' AND uid='$discuz_uid' order by dateline DESC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=mypaylog');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='4' AND uid='$discuz_uid' order by dateline DESC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {			
			$buy['dateline'] = $buy['dateline'] ? gmdate($dateformat, $buy['dateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['dateline'] + $timeoffset * 3600) : '-';
			$buylist[] = $buy;
		}
			
			
}elseif($opaction == 'allbuylog'){
	
		$location = '廣告買賣記錄';
		
		if(!$isadmin)showmessage('對不起，您無權進行該操作，請返回');
		
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='1' order by dateline DESC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=allbuylog');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='1' order by dateline DESC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {			
			$buy['dateline'] = $buy['dateline'] ? gmdate($dateformat, $buy['dateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['dateline'] + $timeoffset * 3600) : '-';
			$buylist[] = $buy;
		}
	
}elseif($opaction == 'allautionlog'){
	
		$location = '拍賣記錄';
		
		if(!$isadmin)showmessage('對不起，您無權進行該操作，請返回');
		
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='2' order by dateline DESC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=allautionlog');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='2' order by dateline DESC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {			
			$buy['dateline'] = $buy['dateline'] ? gmdate($dateformat, $buy['dateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['dateline'] + $timeoffset * 3600) : '-';
			$buylist[] = $buy;
		}
	
}elseif($opaction == 'allclicklog'){
	
		$location = '點擊記錄';
		
		if(!$isadmin)showmessage('對不起，您無權進行該操作，請返回');
		
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='3' order by dateline DESC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=allclicklog');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='3' order by dateline DESC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {			
			$buy['dateline'] = $buy['dateline'] ? gmdate($dateformat, $buy['dateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['dateline'] + $timeoffset * 3600) : '-';
			$buylist[] = $buy;
		}
		
}elseif($opaction == 'allpaylog'){
	
		$location = '結算記錄';
		
		if(!$isadmin)showmessage('對不起，您無權進行該操作，請返回');
		
		$count = DB::query("SELECT COUNT(*) FROM ".DB::table('advmarket_operatelog')." WHERE typeid='4' order by dateline DESC");
		$multipage = multi(DB::result($count, 0), $tpp, $page, 'plugin.php?id=admarket_dzx:admarket&opaction=allpaylog');

		$query = DB::query("SELECT * FROM ".DB::table('advmarket_operatelog')." WHERE typeid='4' order by dateline DESC	LIMIT ".(($page - 1) * $tpp).", $tpp");
		while($buy = DB::fetch($query)) {			
			$buy['dateline'] = $buy['dateline'] ? gmdate($dateformat, $buy['dateline'] + $timeoffset * 3600).' '.gmdate($timeformat,$buy['dateline'] + $timeoffset * 3600) : '-';
			$buylist[] = $buy;
		}			
	
}else{
	showmessage('錯誤操作，請返回');
}

include template('admarket_dzx:admarket');

function showplaceconvert($showplace){

			switch ($showplace){
				case 1 : $showplace = '門戶'; break;
				case 2 : $showplace = '論壇'; break;
				case 3 : $showplace = '群組'; break;
				case 4 : $showplace = '空間'; break;				
				case 5 : $showplace = '搜索結果'; break;
				case 6 : $showplace = '注冊登錄'; break;						
			}
			
			return $showplace;
}

function showwayconvert($showway){
		
			switch ($showway){
				case 1 : $showway = 'HTML代碼'; break;
				case 2 : $showway = '文字'; break;
				case 3 : $showway = '圖片'; break;
				case 4 : $showway = 'FLASH'; break;				
			}
	 
	 return $showway;
}

function statsconvert($stats){
		
			switch ($stats){
				case 0 : $stats = '不可用'; break;
				case 1 : $stats = '鎖定'; break;
				case 2 : $stats = '出售中'; break;
				case 3 : $stats = '拍賣中'; break;
				case 4 : $stats = '使用中'; break;				
			}
	 
	 return $stats;
}

function typeconvert($type){
		
		switch ($type){
	 	 case 0: $type = '無';break;
     case 1: $type = '搜索 右側廣告';break;
     case 2: $type = '論壇/群組 帖間通欄廣告';break;
     case 3: $type = '論壇 分類間廣告';break; 
     case 4: $type = '全局 頁頭二級導航欄廣告';break;
     case 5: $type = '門戶/論壇/群組/空間 格子廣告';break; 
     case 6: $type = '論壇/群組 帖子列表帖位廣告';break;
     case 7: $type = '論壇/群組 帖內廣告';break;
     case 8: $type = '全局 頁頭通欄廣告';break; 
     case 9: $type = '全局 頁尾通欄廣告';break;   
     case 10: $type = '全局 右下角廣告';break;
     case 11: $type = '空間 日志廣告';break;
     case 12: $type = '門戶 文章列表廣告';break;
     case 13: $type = '全局 對聯廣告';break; 
     case 14: $type = '全局 漂浮廣告';break;
     case 15: $type = '空間 動態廣告';break; 
     case 16: $type = '門戶 文章廣告';break;    
     default: $type = '自定義廣告位';break;                               
	 }
	 
	 return $type;
}

function placeconvert($type1){
		
		switch ($type1){
	 	 case 0: $type = 'none';break;
     case 1: $type = 'search';break;
     case 2: $type = 'interthread';break;
     case 3: $type = 'intercat';break; 
     case 4: $type = 'subnavbanner';break;
     case 5: $type = 'text';break; 
     case 6: $type = 'threadlist';break;
     case 7: $type = 'thread';break;
     case 8: $type = 'headerbanner';break; 
     case 9: $type = 'footerbanner';break;   
     case 10: $type = 'cornerbanner';break;
     case 11: $type = 'blog';break;
     case 12: $type = 'articlelist';break;
     case 13: $type = 'couplebanner';break; 
     case 14: $type = 'float';break;
     case 15: $type = 'feed';break; 
     case 16: $type = 'article';break;    
     default: $type = 'custom';break;                               
	 }

	 return $type;
}

function wayconvert($showplace1){

			switch ($showplace1){
				case 1 : $showplace = 'portal'; break;
				case 2 : $showplace = 'forum'; break;
				case 3 : $showplace = 'group'; break;
				case 4 : $showplace = 'home'; break;				
				case 5 : $showplace = 'search'; break;
				case 6 : $showplace = 'member'; break;						
			}
			
			return $showplace;
}

function styleconvert($showplace2){

			switch ($showplace2){
				case 1 : $showplace = 'code'; break;
				case 2 : $showplace = 'text'; break;
				case 3 : $showplace = 'image'; break;
				case 4 : $showplace = 'flash'; break;							
			}
			
			return $showplace;
}

function encodeadvcode($advnew) {
	switch($advnew['style']) {
		case 'code':
			$advnew['code'] = $advnew['code']['html'];
			break;
		case 'text':
			$advnew['code'] = '<a href="'.$advnew['text']['link'].'" target="_blank" '.($advnew['text']['size'] ? 'style="font-size: '.$advnew['text']['size'].'"' : '').'>'.$advnew['text']['title'].'</a>';
			break;
		case 'image':
			$advnew['code'] = '<a href="'.$advnew['image']['link'].'" target="_blank"><img src="'.$advnew['image']['url'].'"'.($advnew['image']['height'] ? ' height="'.$advnew['image']['height'].'"' : '').($advnew['image']['width'] ? ' width="'.$advnew['image']['width'].'"' : '').($advnew['image']['alt'] ? ' alt="'.$advnew['image']['alt'].'"' : '').' border="0"></a>';
			break;
		case 'flash':
			$advnew['code'] = '<embed width="'.$advnew['flash']['width'].'" height="'.$advnew['flash']['height'].'" src="'.$advnew['flash']['url'].'" type="application/x-shockwave-flash" wmode="transparent"></embed>';
			break;
	}
	return $advnew['code'];
}
?>