<?php
/*
	Advertisement Centre Shop List For Discuz! X2 by sw08
	廣告商城 廣告商品設置
	最後修改:2011-7-18 13:03:20
*/
!defined('IN_DISCUZ') && exit('Access Denied');
!defined('IN_ADMINCP') && exit('Access Denied');
DEFINE('OFFSET_DELIMETER', "\t");

$page	= $_G['gp_page'] ? dhtmlspecialchars($_G['gp_page']) : 1;
$does	= $_G['gp_does'] ? dhtmlspecialchars($_G['gp_does']) : '';
$aid	= $_G['gp_aid'] ? dhtmlspecialchars($_G['gp_aid']) : 0;
$discuz_uid		= $_G['uid'];
$tpp = $_G['tpp'];
$adminid		= $_G['adminid'];
$timestamp		= TIMESTAMP;
$timeoffset		= $_G['setting']['timeoffset'];
$dateformat		= $_G['setting']['dateformat'];
$timeformat		= $_G['setting']['timeformat'];

if(!$does){

if(!$_G['gp_submit']){
	
	echo '<script type="text/JavaScript">
	var rowtypedata = [[
		[1,"", ""],
		[1,\'<input type="text" name="orderss[]" size="3" value="0">\', ""],
		[1,"", ""],		
		[1,\'<input type="text" name="name[]" size="15">\', ""],
		[1,\''.catagorysel('', 0).'\', ""],
		[1,\'<input type="text" name="price[]" size="5" value="0"> '.selexts('', 0).'\', ""],
		[1,"", ""],
		[1,"", ""],
		[1,"", ""],		
	]]
	</script>';

	showformheader('plugins&operation=config&identifier=admarket_dzx&pmod=adadmin');
	showtips("<li>新添加的廣告只有在完成詳細的設置後，才能把狀態修改爲“出售中”或“拍賣中”</li><li>修改狀態會影響廣告的可見性，在這裏刪除廣告信息的時候，也會同步刪除掉對應的關聯鏈接和站點廣告</li><li>請不要隨便修改使用中廣告位的類型和屬性，否則會容易出錯</li>");
	showtableheader("廣告列表");
	showsubtitle(array("刪除?", "顯示順序", "綁定編號", "廣告名稱", "類型", "售價", "當前狀態","持有人", "詳細設置"));

		$count = DB::result_first("SELECT count(*) FROM ".DB::table('advmarket')." ORDER by orders ASC");
		$multi = multi($count, $tpp, $page, ADMINSCRIPT."?action=plugins&operation=config&identifier=admarket_dzx&pmod=adadmin");

    $query = DB::query("SELECT * FROM ".DB::table('advmarket')." ORDER by orders ASC LIMIT ".(($page - 1) * $tpp).",{$tpp}");
    while($lists = DB::fetch($query)) {	
			showtablerow('', array(' ',' ', ' ', ' ', ' ',' ',' ', ' '), array(
				'<input type="checkbox" class="checkbox" name="deletes['.$lists['id'].']" value="'.$lists['id'].'" />',			
				'<input type="text" name="orders['.$lists['id'].']" value="'.$lists['orders'].'" size="3" />',
				
				'<input type="hidden" name="bids['.$lists['id'].']" value="'.$lists['bid'].'" size="3" />'.
				'<input type="hidden" name="catagories['.$lists['id'].']" value="'.$lists['catagory'].'" size="3" />'.
				$lists['bid'],
				
				'<input type="text" name="names['.$lists['id'].']" value="'.$lists['name'].'" size="15" />',
				catagorysel($lists['id'], $lists['catagory']),							
				'<input type="text" name="prices['.$lists['id'].']" value="'.$lists['price'].'" size="5" /> '.selexts($lists['id'], $lists['sellext']),				
			  statssel($lists['id'], $lists['stats']),
			  $lists['buyuid'] ? '<a href="home.php?mod=space&uid='.$lists['buyuid'].'" />'.$lists['buyusername'].'</a>' : '系統',
			  '<a href="admin.php?action=plugins&operation=config&identifier=admarket_dzx&pmod=adadmin&does=edit&aid='.$lists['id'].'">詳細</a>',
			));	
    }

  echo '<tr><td></td><td colspan="8"><div><a href="#addrow" name="addrow" onclick="addrow(this, 0)" class="addtr">添加新商品</a></div></td></tr>';
	showsubmit('submit', "提交", 'del', '', $multi);
	showtablefooter();
	showformfooter();
	
}elseif($_G['adminid']=='1' && $_G['gp_formhash']==FORMHASH){
	
	$deleteids = dimplode($_G['gp_deletes']);
	
	$deleteaids = $deleterids = '-1';
	
	foreach($_G['gp_deletes'] as $ids => $values){
		if($ids){
			if($_G['gp_catagories'][$ids] == 0){	
				$deleteaids .=	",".$_G['gp_bids'][$ids];		
			}else{
				$deleterids .= ",".$_G['gp_bids'][$ids];
			}
		}
	}
	
	DB::query("DELETE FROM ".DB::table('common_advertisement')." WHERE advid IN ($deleteaids)");
	DB::query("DELETE FROM ".DB::table('common_relatedlink')." WHERE id IN ($deleterids)");
	
	if($deleteids)DB::query("DELETE FROM ".DB::table('advmarket')." WHERE id IN ($deleteids)");

	if(is_array($_G['gp_name'])){ 
		foreach($_G['gp_name'] as $id => $value){	
			
			 $name[$id] = dhtmlspecialchars($_G['gp_name'][$id]);
			 $orderss[$id] = intval($_G['gp_orderss'][$id]);
			 $sellextnew[$id] = intval($_G['gp_sellext'][$id]);
			 $price[$id] = intval($_G['gp_price'][$id]);
			 $catagory[$id] = intval($_G['gp_catagory'][$id]);
				    	
		   if($name[$id])DB::insert('advmarket', array('catagory' => $catagory[$id], 'stats' => 0,'pubdateline' => $timestamp,'orders' => $orderss[$id], 'name' => $name[$id], 'price' => $price[$id], 'sellext' => $sellextnew[$id]));		    	
		}
  }	
	
	foreach($_G['gp_orders'] as $id => $title) {		
		
			$name[$id] = dhtmlspecialchars($_G['gp_names'][$id]);
			$orderss[$id] = intval($_G['gp_orders'][$id]);
			$sellextnew[$id] = intval($_G['gp_sellextnew'][$id]);
			$price[$id] = intval($_G['gp_prices'][$id]);	
			$stats[$id] = intval($_G['gp_statsnew'][$id]);
			$catagory[$id] = intval($_G['gp_catagorynew'][$id]);	

		if($stats[$id] != 4){
			if($_G['gp_catagories'][$id] == 0){	
				DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE advid ='".$_G['gp_bids'][$id]."'");					
			}else{
				DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='0' WHERE id ='".$_G['gp_bids'][$id]."'");			
			}
		}else{
			if($_G['gp_catagories'][$id] == 0){	
				DB::query("UPDATE ".DB::table('common_advertisement')." SET available='1' WHERE advid ='".$_G['gp_bids'][$id]."'");					
			}		
		}
		  
			DB::query("UPDATE ".DB::table('advmarket')." SET catagory='$catagory[$id]',stats='$stats[$id]',name='$name[$id]', orders='$orderss[$id]',sellext='$sellextnew[$id]',price='$price[$id]' WHERE id='$id'");
	}	
	
	 updatecache('relatedlink');
	 updatecache('advs');
   updatecache('setting');
	cpmsg('廣告列表編輯成功', 'action=plugins&operation=config&identifier=admarket_dzx&pmod=adadmin','succeed');
}

}elseif($does == 'edit' && $aid){
	
	$query = DB::query("SELECT * FROM ".DB::table('advmarket')." WHERE id='$aid' LIMIT 1");
	$data = DB::fetch($query);		
	
	if(!$_G['gp_submit']){

	$lang['name'] = '廣告名稱';
	$lang['name_comment'] = '僅作爲標示，不在廣告中顯示';
	$lang['desc'] = '廣告描述';
	$lang['desc_comment'] = '描述您的廣告位，支持HTML代碼';	
	$lang['allowedit'] = '允許編輯投放範圍';
	$lang['allowedit_comment'] = '選擇“是”，用戶可以修改投放的版區、群組、頻道和樓層';		
	$lang['catagory'] = '廣告類型';
	$lang['verify'] = '審核狀態';
	$lang['verify_comment'] = '未通過審核的廣告不會被顯示';	
	$lang['bid'] = '綁定廣告編號';
	$lang['bid_comment'] = '該項設置沒有特殊情況請不要修改，設置爲“0”相當于解除綁定';			
	$lang['pubusername'] = '賣家/拍賣者';
	$lang['pubusername_comment'] = '請填寫用戶名，留空表示發布者爲系統';	
	$lang['buyusername'] = '持有人';
	$lang['buyusername_comment'] = '請填寫用戶名，留空表示無人持有';	
	$lang['paypolicy'] = '廣告收費方式';
	$lang['paypolicy_comment'] = '請選擇方式，這些方式將影響下面的選項';			
	$lang['minbuy'] = '最少購買天數/次數';
	$lang['minbuy_comment'] = '購買該廣告位的用戶必須最少購買該天數/次數';	
	$lang['maxbuy'] = '最多購買天數/次數';
	$lang['maxbuy_comment'] = '購買該廣告位的用戶最多一次購買該天數/次數不能超過該值';		
	$lang['clicktime'] = '每人/每IP記錄點擊最小間隔時間(分鍾)';
	$lang['clicktime_comment'] = '對于同一用戶或同一IP，只有當相鄰兩次的點擊時間間隔超過該值時，點擊次數才會被計算';
	$lang['maxpayday'] = '每人/每IP每日最多有效記錄點擊次數';
	$lang['maxpayday_comment'] = '當同一用戶或同一IP在當日點擊該廣告的次數超過該值的時候，系統不再統計廣告點擊次數';
	$lang['showway'] = '展現方式';
	$lang['showway_comment'] = '強烈建議HTML代碼只出售給高級用戶，此外HTML和FLASH的模式可能會無法計算點擊次數';	
	$lang['redirectlink'] = '重定向鏈接';
	$lang['redirectlink_comment'] = '這裏填寫的是廣告原始的指向鏈接';		
	$lang['showplace'] = '投放範圍';
	$lang['showplace_comment'] = '請注意，某些投放範圍只針對特殊位置的廣告有效';							
	$lang['restcount'] = '剩余次數';
	$lang['restcount_comment'] = '該廣告位還剩余的點擊次數，次數不足時需要購買';		
	$lang['expire'] = '有效期';
	$lang['expire_comment'] = '該廣告位的有效期，時間不足時需要重新購買，時間格式爲2011-01-01 00:00:00';	
	$lang['totalfee'] = '剩余可發放積分';
	$lang['totalfee_comment'] = '用戶點擊廣告獲得的積分將從這裏扣，數額不足時點擊廣告不再獎勵積分';
	$lang['paycount'] = '結算單位次數';
	$lang['paycount_comment'] = '當廣告的點擊次數達到或超過該值時，可以以整數倍來結算點擊次數並獲得對應的積分';			
	$lang['allowbuygroup'] = '允許購買的用戶組';
	$lang['allowbuygroup_comment'] = '選中的用戶組才允許購買該廣告，全不選視爲均允許';	
	$lang['allowrefundgroup'] = '允許退款的用戶組';
	$lang['allowrefundgroup_comment'] = '選中的用戶組在購買廣告後允許退款，全不選視爲均允許';	
	$lang['allowsellgroup'] = '允許出售廣告的用戶組';
	$lang['allowsellgroup_comment'] = '選中的用戶組允許把自己的廣告出售到二手市場中，全不選視爲均允許';	
	$lang['allowautiongroup'] = '允許拍賣廣告的用戶組';
	$lang['allowautiongroup_comment'] = '被選中的用戶組允許把自己的廣告進行拍賣，全不選視爲均允許';	
	$lang['count'] = '點擊次數';
	$lang['count_comment'] = '該廣告累計被點擊的次數';	
	$lang['usecount'] = '可結算次數';
	$lang['usecount_comment'] = '該處的次數允許結算積分，結算後該次數會相應的減少';		
	
	showformheader('plugins&operation=config&identifier=admarket_dzx&pmod=adadmin&does=edit&aid='.$aid);
	showtips("<li>這裏只對廣告的銷售參數進行修改，真正廣告顯示的內容並不會被修改，如果需要修改，請到運營——網站廣告處修改</li>");
	showtableheader("詳細設置 - ".$data['name']);
	showsetting('name', 'namenew', $data['name'], 'text');
	showsetting('desc', 'descnew', $data['desc'], 'textarea');
	  showsetting('catagory', array('catagorynew', array(
		  array(0, cplang('網站廣告')),
		  array(1, cplang('關聯鏈接')),
	  )), $data['catagory'], 'mradio');	
	  
	showsubtitle(array("廣告類型:", ""));
  showtablerow('', array('',''), array(
			  typesel('', $data['type']),
				'可選擇廣告類型，對于關聯鏈接的廣告來說，該設置無效',										
	));	  
	  

    
  showsetting('verify', 'verifynew', $data['verify'], 'radio');      
  showsetting('allowedit', 'alloweditnew', $data['allowedit'], 'radio');   
  showsetting('bid', 'bidnew', $data['bid'], 'text'); 
  
  showsetting('pubusername', 'pubusernamenew', $data['pubusername'], 'text');
  showsetting('buyusername', 'buyusernamenew', $data['buyusername'], 'text');

	  showsetting('paypolicy', array('paypolicynew', array(
		  array(0, cplang('按點擊次數收費')),
		  array(1, cplang('按時間收費')),
	  )), $data['paypolicy'], 'mradio');	

	showsubtitle(array("每天/每次售價:", ""));
  showtablerow('', array('',''), array(
			  '<input type="text" name="pricenew" value="'.$data['price'].'" size="25" /> '.selexts('', $data['sellext']),
				'該廣告位每天/每次購買的單位售價，不支持浮點數',										
	));	
	  
	showsetting('minbuy', 'minbuynew', $data['minbuy'], 'text');
	showsetting('maxbuy', 'maxbuynew', $data['maxbuy'], 'text'); 	
	showsetting('clicktime', 'clicktimenew', $data['clicktime'], 'text');
	showsetting('maxpayday', 'maxpaydaynew', $data['maxpayday'], 'text');
	showsetting('restcount', 'restcountnew', $data['restcount'], 'text');
	showsetting('expire', 'expirenew', ($data['expire'] ? gmdate("Y-m-d H:i:s" , $data['expire'] + $timeoffset * 3600) : ''), 'text');
	
	showsetting('allowbuygroup', '', '', '<select name="allowbuygroupnew[]" multiple="multiple" size="10">'.showgroup($data['allowbuygroup']).'</select>');
	showsetting('allowrefundgroup', '', '', '<select name="allowrefundgroupnew[]" multiple="multiple" size="10">'.showgroup($data['allowrefundgroup']).'</select>');
	showsetting('allowsellgroup', '', '', '<select name="allowsellgroupnew[]" multiple="multiple" size="10">'.showgroup($data['allowsellgroup']).'</select>');
	showsetting('allowautiongroup', '', '', '<select name="allowautiongroupnew[]" multiple="multiple" size="10">'.showgroup($data['allowautiongroup']).'</select>');				
	
	showsubtitle(array("點擊用戶獎勵積分:", ""));
  showtablerow('', array('',''), array(
			  '<input type="text" name="clickpaynew" value="'.$data['clickpay'].'" size="25" /> '.clickexts('', $data['clickext']),
				'注冊會員每有效點擊一次該廣告，系統獎勵的積分，該項目可以不設置，但是設置了會吸引更多會員點擊廣告',										
	));	
	showsetting('totalfee', 'totalfeenew', $data['totalfee'], 'text');
	
	showsubtitle(array("廣告商結算積分:", ""));
  showtablerow('', array('',''), array(
			  '<input type="text" name="payfeenew" value="'.$data['payfee'].'" size="25" /> '.payexts('', $data['payext']),
				'每當産生一個結算數量的有效點擊次數後，廣告商可以獲得該數量的結算積分，該積分可以用于提現用途',										
	));			
	showsetting('paycount', 'paycountnew', $data['paycount'], 'text');
  showsetting('count', 'countnew', $data['count'], 'text');
  showsetting('usecount', 'usecountnew', $data['usecount'], 'text');	
	showsetting('redirectlink', 'redirectlinknew', $data['redirectlink'], 'text');
	
	  showsetting('showway', array('showwaynew', array(
		  array(1, cplang('HTML代碼')),
		  array(2, cplang('文字')),
		  array(3, cplang('圖片')),
		  array(4, cplang('FLASH')),		  
	  )), $data['showway'], 'mradio');	
	  
	  showsetting('showplace', array('showplacenew', array(
		  array(1, cplang('門戶')),
		  array(2, cplang('論壇')),
		  array(3, cplang('群組')),
		  array(4, cplang('空間')),	
		  array(5, cplang('搜索結果')),	 
		  array(6, cplang('注冊登錄')), 
	  )), $data['showplace'], 'mradio');	  		

	showsubmit('submit', "提交", '', '', $multi);
	showtablefooter();
	showformfooter();			
		
	}elseif($_G['adminid']=='1' && $_G['gp_formhash']==FORMHASH){
		
	  $name = dhtmlspecialchars($_G['gp_namenew']);
	  $typenew = $_G['gp_typenew'];
	  $catagory = $_G['gp_catagorynew'];
	  $allowedit = $_G['gp_alloweditnew'];
    $desc = $_G['gp_descnew'];	  
	  $bid = intval($_G['gp_bidnew']);
	  $pubusername = dhtmlspecialchars($_G['gp_pubusernamenew']);
	  $buyusername = dhtmlspecialchars($_G['gp_buyusernamenew']);
	  $price = intval($_G['gp_pricenew']);
	  $sellext = $_G['gp_sellext'][0];
	  
	  $clickext = $_G['gp_clickext'][0];
	  $payext = $_G['gp_payext'][0];
	  
	  $minbuy = intval($_G['gp_minbuynew']);
	  $maxbuy = intval($_G['gp_maxbuynew']);
	  $clicktime = intval($_G['gp_clicktimenew']);
	  $maxpayday = intval($_G['gp_maxpaydaynew']);
	  $restcount = intval($_G['gp_restcountnew']);
	  $expire = $_G['gp_expirenew'] ? strtotime($_G['gp_expirenew']) : '';  
    $allowbuygroup = implode(",", $_G['gp_allowbuygroupnew']); 
    $allowrefundgroup = implode(",", $_G['gp_allowrefundgroupnew']); 
    $allowsellgroup = implode(",", $_G['gp_allowsellgroupnew']); 
    $allowautiongroup = implode(",", $_G['gp_allowautiongroupnew']); 
    $redirectlink = $_G['gp_redirectlinknew'];
    $clickpay = intval($_G['gp_clickpaynew']);
    $payfee = intval($_G['gp_payfeenew']);
    $paycount = intval($_G['gp_paycountnew']);
    $totalfee = intval($_G['gp_totalfeenew']); 
    $count = intval($_G['gp_countnew']);
    $usecount = intval($_G['gp_usecountnew']);        

			if($catagory == 0){							
				switch($_G['gp_showplacenew']){
					case 1:$targets = "portal";break;
					case 2:$targets = "forum";break;
					case 3:$targets = "group";break;
					case 4:$targets = "home";break;
					case 5:$targets = "search";break;
					case 6:$targets = "member";break;
				}					
				DB::query("UPDATE ".DB::table('common_advertisement')." SET targets='$targets' WHERE advid ='$bid'");					
			}else{
				$extent = pow(2, 4 - $_G['gp_showplacenew']);
				DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='$extent' WHERE id ='$bid'");			
			}
			
		if($_G['gp_verifynew'] == 1){
			if($catagory == 0){	
				DB::query("UPDATE ".DB::table('common_advertisement')." SET available='0' WHERE advid ='$bid'");					
			}else{
				DB::query("UPDATE ".DB::table('common_relatedlink')." SET extent='0' WHERE id ='$bid'");			
			}
		}else{
			if($catagory == 0){	
				DB::query("UPDATE ".DB::table('common_advertisement')." SET available='1' WHERE advid ='$bid'");					
			}		
		}			
	  	  
		$getpuid = DB::fetch_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".$pubusername."' LIMIT 1");
		$pubuid = $getpuid['uid'];
    if($pubusername && !$pubuid)cpmsg('對不起，不存在該用戶，請返回');	  
		$getbuid = DB::fetch_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='".$buyusername."' LIMIT 1");
		$buyuid = $getbuid['uid'];
    if($buyusername && !$buyuid)cpmsg('對不起，不存在該用戶，請返回');	
    
    if(!$pubuid){
      $pubdateline = $timestamp;
    }else{
    	$pubdateline = $data['pubdateline'];
    }
    
    if(!$buyuid){
     $buydateline = 0;    
    }else{
     $buydateline = $data['buydateline'];
    }
	  
	  DB::query("UPDATE ".DB::table('advmarket')." SET `desc`='$desc', allowedit='$allowedit', count='$count', usecount='$usecount', payext='$payext', clickext='$clickext', showplace='$_G[gp_showplacenew]', showway='$_G[gp_showwaynew]', redirectlink='$redirectlink', totalfee='$totalfee', paycount='$paycount', payfee='$payfee', clickpay='$clickpay', allowbuygroup='$allowbuygroup',allowrefundgroup='$allowrefundgroup',allowsellgroup='$allowsellgroup',allowautiongroup='$allowautiongroup', expire='$expire', restcount='$restcount', maxpayday='$maxpayday', clicktime='$clicktime', maxbuy='$maxbuy', minbuy='$minbuy', sellext='$sellext', price='$price', paypolicy='$_G[gp_paypolicynew]', buydateline='$buydateline', pubdateline='$pubdateline', buyuid='$buyuid', buyusername='$buyusername',pubuid='$pubuid', pubusername='$pubusername', bid='$bid', verify='$_G[gp_verifynew]', catagory='$catagory', type='$typenew', name='$name' WHERE id='$aid'");	
		
		updatecache('relatedlink');
		 updatecache('advs');
updatecache('setting');
		cpmsg('廣告參數設置成功', 'action=plugins&operation=config&identifier=admarket_dzx&pmod=adadmin','succeed');
		
	}	
	
}else{
	cpmsg('ERROR!');
}

function selexts($id = '', $value = 0){
	
global $_G;
	
	if($id){
	  $data = '<select name="sellextnew['.$id.']">';
	}else{
		$data = '<select name="sellext[]">';
	}
	
	foreach($_G['setting']['extcredits'] as $eid => $credits){		
    $data .= '<option value="'.$eid.'" '.($value == $eid ? 'selected' : '').'>'.$_G['setting']['extcredits'][$eid]['title'].'</option>';
	}
	
	$data .= '</select>';
	
	return $data;
}

function clickexts($id = '', $value = 0){
	
global $_G;
	
	if($id){
	  $data = '<select name="clickextnew['.$id.']"><option value="0" '.($value == 0 ? 'selected' : '').'>無</option>';
	}else{
		$data = '<select name="clickext[]"><option value="0" '.($value == 0 ? 'selected' : '').'>無</option>';
	}
	
	foreach($_G['setting']['extcredits'] as $eid => $credits){		
    $data .= '<option value="'.$eid.'" '.($value == $eid ? 'selected' : '').'>'.$_G['setting']['extcredits'][$eid]['title'].'</option>';
	}
	
	$data .= '</select>';
	
	return $data;
}

function payexts($id = '', $value = 0){
	
global $_G;
	
	if($id){
	  $data = '<select name="payextnew['.$id.']"><option value="0" '.($value == 0 ? 'selected' : '').'>無</option>';
	}else{
		$data = '<select name="payext[]"><option value="0" '.($value == 0 ? 'selected' : '').'>無</option>';
	}
	
	foreach($_G['setting']['extcredits'] as $eid => $credits){		
    $data .= '<option value="'.$eid.'" '.($value == $eid ? 'selected' : '').'>'.$_G['setting']['extcredits'][$eid]['title'].'</option>';
	}
	
	$data .= '</select>';
	
	return $data;
}

function statssel($id = '', $value = 0){
	if($id){
	  $data = '<select name="statsnew['.$id.']">';
	}else{
		$data = '<select name="stats[]">';
	}
	
	for($i=0;$i<=4;$i++){
		
	 switch ($i){
     case 1: $sel = '鎖定';break;
     case 2: $sel = '出售中';break;
     case 3: $sel = '拍賣中';break; 
     case 4: $sel = '使用中';break;
     case 0: $sel = '不可用';break;                             
	 }		
		
	   $data .= '<option value="'.$i.'" '.($value == $i ? 'selected' : '').'>'.$sel.'</option>';
	}
	
	$data .= '</select>';
	
	return $data;
}

function catagorysel($id = '', $value = 0){
	if($id){
	  $data = '<select name="catagorynew['.$id.']">';
	}else{
		$data = '<select name="catagory[]">';
	}
	
	for($i=0;$i<=1;$i++){
		
	 switch ($i){
     case 1: $sel = '關聯鏈接';break;
     case 0: $sel = '網站廣告';break;                             
	 }		
		
	   $data .= '<option value="'.$i.'" '.($value == $i ? 'selected' : '').'>'.$sel.'</option>';
	}
	
	$data .= '</select>';
	
	return $data;
}

function typesel($id = '', $value = 0){
	global $_G;
	
		$data = '<select name="typenew">';
	
	for($i=0;$i<=16;$i++){
		
	 switch ($i){
	 	 case 0: $sel = '無';break;
     case 1: $sel = '搜索 右側廣告';break;
     case 2: $sel = '論壇/群組 帖間通欄廣告';break;
     case 3: $sel = '論壇 分類間廣告';break; 
     case 4: $sel = '全局 頁頭二級導航欄廣告';break;
     case 5: $sel = '門戶/論壇/群組/空間 格子廣告';break; 
     case 6: $sel = '論壇/群組 帖子列表帖位廣告';break;
     case 7: $sel = '論壇/群組 帖內廣告';break;
     case 8: $sel = '全局 頁頭通欄廣告';break; 
     case 9: $sel = '全局 頁尾通欄廣告';break;   
     case 10: $sel = '全局 右下角廣告';break;
     case 11: $sel = '空間 日志廣告';break;
     case 12: $sel = '門戶 文章列表廣告';break;
     case 13: $sel = '全局 對聯廣告';break; 
     case 14: $sel = '全局 漂浮廣告';break;
     case 15: $sel = '空間 動態廣告';break; 
     case 16: $sel = '門戶 文章廣告';break;                                   
	 }		
		
	   $data .= '<option value="'.$i.'" '.($value == $i ? 'selected' : '').'>'.$sel.'</option>';
	}
	
    $query = DB::query("SELECT * FROM ".DB::table('common_advertisement_custom')." ORDER by id ASC");
    while($lists = DB::fetch($query)) {	
    	$data .= '<option value="'.($lists['id'] + 16).'" '.($value == (16 + $lists['id']) ? 'selected' : '').'>自定義廣告 '.$lists['name'].'</option>';
    }	
	
	$data .= '</select>';
	
	return $data;
}

function showgroup($value){
	global $_G,$lang;

	$groupselect = array();
	$usergroupid = explode(",",$value);
	$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." WHERE groupid NOT IN (4,5,6,7,8) ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
	while($group = DB::fetch($query)) {
		$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
		$groupselect[$group['type']] .= "<option value=\"$group[groupid]\" ".(in_array($group['groupid'], $usergroupid) ? 'selected' : '').">$group[grouptitle]</option>\n";
	}
	$groupselect = '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
		($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
		($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
		'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup>';

  return $groupselect;

}
?>