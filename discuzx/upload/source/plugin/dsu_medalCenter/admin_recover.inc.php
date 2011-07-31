<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: admin_recover.inc.php 59 2011-07-18 10:12:02Z chuzhaowei@gmail.com $
*/
(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) && exit('Access Denied');

$limit = 30;
//PRINT_R($_POST);
$query = DB::query("SELECT medalid,name,image,expiration FROM ".DB::table('forum_medal')." WHERE available ='1' ORDER BY displayorder");
while($medal = DB::fetch($query)){
	$medalarray[$medal['medalid']]= $medal;
}

$_G['cache']['dsuMedalCenter'] = $medalarray;
if($_G['gp_showlogs']=='yes'){
	$_G['gp_medals'] = intval($_G['gp_medals']);
	if(!$_G['gp_ok'] && $_G['gp_username'] && $_G['gp_medals']){
		$url = "admin.php?action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_recover&showlogs=yes&username=".$_G['gp_username']."&medals=".$_G['gp_medals'];
		$search_condition = array_merge($_GET, $_POST);

		foreach($search_condition as $k => $v) {
			if(in_array($k, array('action', 'operation', 'formhash', 'submit', 'page', 'identifier', 'pmod', 'medals')) || $v === '') {
				unset($search_condition[$k]);
			}
		}
		$usernames = searchmembers($search_condition);
		if($usernames){
			if($_G['gp_medals'] == -1){$mdcon = " (medals !='' OR medals ='\t')";}
			if($_G['gp_medals'] != -1 && $_G['gp_medals']){$mdcon = " (medals='".$_G['gp_medals']."' OR medals LIKE '".$_G['gp_medals']."\t%' OR medals LIKE '%\t".$_G['gp_medals']."')";}
			if($_G['gp_medals'] != -1 && $_G['gp_medals'] && $medalarray[$_G['gp_medals']]['expiration']){$mdcon = " (medals LIKE '".$_G['gp_medals']."|%' OR medals LIKE '".$_G['gp_medals']."|%\t%' OR medals LIKE '%\t".$_G['gp_medals']."|%')";}
			//ECHO $mdcon;
			$conditions = 'uid IN ('.dimplode($usernames).')';
			$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_field_forum')." WHERE ".$conditions." AND".$mdcon);
			$page = max(1, intval($_G['gp_page']));
			$start_limit = ($page - 1) * $limit;
			$multipage = multi($num, $limit, $page, $url);
			$sql="SELECT uid, medals FROM ".DB::table('common_member_field_forum')." WHERE ".$conditions." AND".$mdcon." ORDER BY uid ASC LIMIT ".$start_limit." ,".$limit;
			$querygg=DB::query($sql);
			while ($value=DB::fetch($querygg)){
				if($value['uid']){$cmffs[$value['uid']] = $value;$uids[] = $value['uid'];}
			}
			$uids = 'uid IN ('.dimplode($uids).')';
			$sql2="SELECT * FROM ".DB::table('common_member')." WHERE ".$uids;
			$querygg2=DB::query($sql2);
			while ($value2=DB::fetch($querygg2)){
				$cmffs[$value2['uid']]['username'] = $value2['username'];
			}
		}
	

	$cmffs = dstripslashes($cmffs);

		showformheader("plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_recover", '', 'configform');
		showtips('<li>用户名输入框可使用通配符 *，多个值之间用半角逗号“,”分隔。</li><li>当用户名输入&nbsp;*&nbsp;可以删除所有会员的某一个或所有勋章。</li><li></li>');
		showtableheader('您确定删除以下会员的勋章吗？');
		showsubtitle(array('', '会员名', '要删除的勋章'));
		IF($num){
			foreach ($cmffs as $id => $result){
			showtablerow('', array(' ', ' ', ' ', ' '), array(
					'',
					$result['username'],
					mdshow($_G['gp_medals'],$result['medals'],$medalarray,$result['uid']),
				));
			}
		}else{
			echo '<tr><td colspan="15" >没有符合条件的会员</td></tr>';
		}
		showsubmit('submit', 'submit', '', '', $multipage);
		showformfooter();
		showtablefooter();
	}
}elseif(submitcheck('submit') && $_G['gp_medals']){
	$_G['gp_medals'] = dstripslashes($_POST['medals']);
	foreach ($_G['gp_medals'] as $id => $result){
		$mdout[$id]['uid'] = $uids[] = $id;
		$mdout[$id]['medals'] = $result;
	}
	$conditions = 'uid IN ('.dimplode($uids).')';
	$sql="SELECT * FROM ".DB::table('common_member_field_forum')." WHERE ".$conditions;
	$querygg=DB::query($sql);
	while ($value=DB::fetch($querygg)){
		if($value['uid']){
			$medals_old = explode("\t", $value['medals']);
			$medals_rec = $mdout[$value['uid']]['medals'];
			$medals_new = implode('\t', array_filter(array_diff($medals_old,$medals_rec)));
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET medals='{$medals_new}' WHERE uid='{$value['uid']}'");
			foreach ($medals_rec as $id => $result){
				$cdb_medallog['uid'] = $value['uid'];
				$cdb_medallog['medalid'] = $result;
				$cdb_medallog['type'] = '-1';
				$cdb_medallog['dateline'] = $_G['timestamp'];
				DB::insert('forum_medallog',$cdb_medallog);
			}
		}
	}	
	cpmsg('成功回收！', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_recover', 'succeed');
}else{
	
	$mdsel = md2seled($medalarray);
	showformheader("plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_recover", '', 'configform');
	showtips('<li>用户名输入框可使用通配符 *，多个值之间用半角逗号“,”分隔。</li><li>当用户名输入&nbsp;*&nbsp;可以删除所有会员的某一个或所有勋章。</li><li></li>');
	showtableheader('勋章回收');
	showsetting("用户名 ", 'username', '*', 'text');
	echo '<tr><td colspan="2" class="td27">回收勋章 :</td></tr><tr class="noborder"><td class="vtop rowform">'.$mdsel.'</td><td class="vtop tips2"><input name="showlogs" value="yes" type="hidden"></td></tr>';
	showsubmit('submit', "确定");
	showformfooter();
	showtablefooter();
}


function md2seled($array){
	$select_out = '<select name="medals">';
	$select_out .='<option value="-1">所有勋章</option>' ;
	foreach($array as $i => $value){
		$select_out .='<option value="'.$i.'">'.$value['name'].'</option>' ;
	}
	$select_out .= '</select>';
	return $select_out;
}
function mdshow($k,$str,$array,$uid){
	$strs = explode("\t", $str);
	if($k == '-1'){
		foreach($strs as $i => $value){
			if (strstr($value, '|')){$values = explode("|", $value);$id = $values[0];}else{$id = $value;} 
			if($array[$id]['name']){
				$show_out .='<img style="vertical-align:middle" src="static/image/common/'.$array[$id]['image'].'">&nbsp;'.$array[$id]['name'].'&nbsp;&nbsp;<input name="medals['.$uid.'][]" value="'.$value.'" type="hidden">' ;
			}			
		}
	}elseif($k != '-1' && in_array($k,$strs)){
		$show_out ='<img style="vertical-align:middle" src="static/image/common/'.$array[$k]['image'].'">&nbsp;'.$array[$k]['name'].'&nbsp;&nbsp;<input name="medals['.$uid.'][]" value="'.$k.'" type="hidden">' ;
	}
	return $show_out;
}

function searchmembers($condition, $limit=2000, $start=0) {
	include_once libfile('class/membersearch');
	$ms = new membersearch();
	return $ms->search($condition, $limit, $start);
}
?>