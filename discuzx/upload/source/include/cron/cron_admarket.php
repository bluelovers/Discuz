<?php

/*
	Advertisement Centre Shop List For Discuz! X2 by sw08
	廣告商城 計劃任務
	最後修改:2011-7-26 16:07:11
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
loadcache('plugin');

if(!defined('IN_ADMINCP')){
require './source/function/function_cache.php';
}

$timeoffset	= $_G['setting']['timeoffset'];
$dateformat	= $_G['setting']['dateformat'];
$timeformat	= $_G['setting']['timeformat'];
$timestamp = time() + $timeoffset * 3600;
$vars	= $_G['cache']['plugin']['admarket_dzx'];

$timestamp = $timestamp - ($timestamp%86400);

if($vars['open']){

	    $query = DB::query("SELECT * FROM ".DB::table('advmarket')."");
		  WHILE($shopdata = DB::fetch($query)){	  		
          
         if($shopdata['stats'] == 4 && ($shopdata['expire'] < $timestamp || ($shopdata['restcount'] == 0 && $shopdata['paypolicy'] == 0))){
			    
			    DB::query("UPDATE ".DB::table('advmarket')." SET stats = '1',expire='$timestamp' WHERE id ='$shopdata[id]'");	
			    notification_add($shopdata['buyuid'], "廣告商城信息", "對不起，您的$shopdata[name]已經到期或者剩余次數不足，現已被鎖定，請及時續費以便重新開啓", '', 1);
			    
			    if($shopdata['catagory'] == 0){	
			    	DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE advid ='".$shopdata['bid']."'");					
			    }else{
				    DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='0' WHERE id ='".$shopdata['bid']."'");			
			    }
			    
			   }
			   
			   if($shopdata['stats'] == 1 && $shopdata['expire'] + $vars['recyleday'] * 86400 < $timestamp){
			    
			    DB::query("UPDATE ".DB::table('advmarket')." SET stats = '0',pubuid = '0',pubusername = '',buyuid = '0',buyusername = '',buydateline = '0',totalfee = '0',paycount = '0',count = '0',usecount = '0',restcount = '0',expire = '0',redirectlink = '' WHERE id ='$shopdata[id]'");	
			    notification_add($shopdata['buyuid'], "廣告商城信息", "對不起，您的$shopdata[name]由于長期處于欠費狀態，現已被系統回收", '', 1);
			    
			    if($shopdata['catagory'] == 0){	
			    	DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE advid ='".$shopdata['bid']."'");					
			    }else{
				    DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='0' WHERE id ='".$shopdata['bid']."'");			
			    }
			    
			   }	
			   
			   if(($shopdata['stats'] == 2 || $shopdata['stats'] == 3) && $shopdata['pubdateline'] + $vars['recyleday'] * 86400 < $timestamp && $shopdata['pubuid'] && !$shopdata['buyuid']){
			    
			    DB::query("UPDATE ".DB::table('advmarket')." SET stats = '0',pubuid = '0',pubusername = '',buyuid = '0',buyusername = '',buydateline = '0',totalfee = '0',paycount = '0',count = '0',usecount = '0',restcount = '0',expire = '0',redirectlink = '' WHERE id ='$shopdata[id]'");	
			    notification_add($shopdata['pubuid'], "廣告商城信息", "對不起，您出售或拍賣的$shopdata[name]由于長期無人應拍或購買，現已被系統回收", '', 1);
			    
			    if($shopdata['catagory'] == 0){	
			    	DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE advid ='".$shopdata['bid']."'");					
			    }else{
				    DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='0' WHERE id ='".$shopdata['bid']."'");			
			    }
			    
			   }	
			   
			   if($shopdata['stats'] == 3 && $shopdata['pubdateline'] + $vars['recyleday'] * 86400 < $timestamp && !$shopdata['pubuid'] && !$shopdata['buyuid']){
			    
			    DB::query("UPDATE ".DB::table('advmarket')." SET expire=$timestamp+$vars[recyleday]*86400,pubdateline=pubdateline+$vars[recyleday]*86400 WHERE id ='$shopdata[id]'");	
			    
			   }
			   
			   //二手競拍
			   if($shopdata['stats'] == 3 && $shopdata['pubdateline'] + $vars['recyleday'] * 86400 < $timestamp && $shopdata['pubuid'] && $shopdata['buyuid']){
			    
			    updatemembercount($shopdata['pubuid'], array("extcredits{$shopdata['sellext']}" => $shopdata['price']), true,'',0);
			    DB::query("UPDATE ".DB::table('advmarket')." SET stats='1',pubuid='0',pubusername='',buyuid='$shopdata[buyuid]',buyusername='$shopdata[buyusername]',buydateline='$timestamp' WHERE id ='$shopdata[id]'");	
			    notification_add($shopdata['buyuid'], "廣告商城信息", "恭喜，您競拍的$shopdata[name]已經結束，您是最高出價人，請到“我的廣告”處查看", '', 1);
			    notification_add($shopdata['pubuid'], "廣告商城信息", "恭喜，您拍賣的$shopdata[name]已經結束，您獲得了{$shopdata[price]}{$extcredits[$shopdata[sellext]][title]}", '', 1);
			    
			    DB::insert('advmarket_operatelog', array('action' => "花費{$shopdata[price]}{$_G[setting][extcredits][$shopdata[sellext]][title]}競拍$shopdata[name]，並以最高價獲得",'typeid' => 2, 'dateline' => $shopdata['buydateline'], 'uid' => $shopdata['buyuid'], 'username' => $shopdata['buyusername']));
			    DB::insert('advmarket_operatelog', array('action' => "拍賣的$shopdata[name]被$shopdata[pubusername]以{$shopdata[price]}{$_G[setting][extcredits][$shopdata[sellext]][title]}成交",'typeid' => 2, 'dateline' => $shopdata['buydateline'], 'uid' => $shopdata['pubuid'], 'username' => $shopdata['pubusername']));
			    
			   }	
			   
			   //系統競拍
			   if($shopdata['stats'] == 3 && $shopdata['pubdateline'] + $vars['recyleday'] * 86400 < $timestamp && !$shopdata['pubuid'] && $shopdata['buyuid']){
			    
				  if($shopdata['catagory'] == 0 && !$shopdata['bid']){
					
	         DB::query("INSERT INTO ".DB::table('common_advertisement')." (available,type,title,targets)
	         	VALUES ('1', '".placeconvert($shopdatas['type'])."', '$shopdata[name]', '".wayconvert($shopdatas['showplace'])."')");
	         $newbid = DB::insert_id();						
					 $sqladd = "bid='$newbid',";
					 	
				  }elseif($shopdata['catagory'] == 1 && !$shopdata['bid']){
					
	         $extent = pow(2, 4 - $shopdatas['showplace']);
	         DB::query("INSERT INTO ".DB::table('common_relatedlink')." (name, extent)
	         	VALUES ('$shopdata[name]', '$extent')");
	          $newbid = DB::insert_id();		
	          $sqladd = "bid='$newbid',";			
				  }

			    DB::query("UPDATE ".DB::table('advmarket')." SET $sqladd stats='1',pubuid='0',pubusername='',buyuid='$shopdata[buyuid]',buyusername='$shopdata[buyusername]',buydateline='$timestamp',expire='$timestamp+$vars[recyleday]*86400' WHERE id ='$shopdata[id]'");	
          notification_add($shopdata['buyuid'], "廣告商城信息", "恭喜，您競拍的$shopdata[name]已經結束，您是最高出價人，請到“我的廣告”處查看", '', 1);
			    DB::insert('advmarket_operatelog', array('action' => "花費{$shopdata[price]}{$_G[setting][extcredits][$shopdata[sellext]][title]}競拍$shopdata[name]，並以最高價獲得",'typeid' => 2, 'dateline' => $shopdata['buydateline'], 'uid' => $shopdata['buyuid'], 'username' => $shopdata['buyusername']));
			    
			   }			   		   			   		   		   	
			    
			}

      updatecache('advs');
      updatecache('setting');
      updatecache('relatedlink');
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
?>