<?php

/* 背景商店插件参赛版 For Discuz X2.0
   $Id:shop.func.php 2011-07-20 Rufey_Lau
 */
 
if(!defined('IN_DISCUZ')&&!defined('IN_BKSHOP')){
	exit('Access Denied!');
}

//积分函数
function credit($_this = ''){
	global $_G;
	foreach($_G['setting']['extcredits'] as $id => $val){
		if($_this == $id){	
			$selected = 'selected="selected"';	
		}else{
			$selected = '';
		}
		$str .= "<option value=\"{$id}\" {$selected}>".$val['title']."</option>";
	}
	return $str;
}

//积分名称获取函数
function getcreditname($id, $echo = TRUE){
	global $_G;
	$return = $_G['setting']['extcredits'][$id]['title'];
	if($echo){
		echo $return;
	}else{
		return $return;
	}
}

//中文字符串限制输出函数
function limitstr($str, $lenmax){
	$strlen = strlen($str);
	if($lenmax > $strlen){
		return $str;
	}else{
		$character = 0;
		for($i = 0; $i < $lenmax; $i++){
			if(ord(substr($str, $i, 1)) <= 128){
				$character++;
			}
		}
		if(($lenmax%2 == 1)&&($character%2 == 0)){
			$lenmax++;
		}
		if(($lenmax%2 == 0)&&($character%2 == 1)){
			$lenmax++;
		}
		$return = substr($str, 0, $lenmax);
		return $return;
	}
}

//自动列表创建函数
function datalist($table, $condition = '', $rows = 10, $ispage = TRUE, $orderby = '', $pageurl = '', $order = 'desc', $startpage = 0){
	global $_G;
	$result = array();
	if($ispage){
		$page = max(1, $_G['gp_page']);
		$start = ($page - 1) * $rows;
		$limit = "limit $start,$rows";
	}else{
		$limit = '';
	}
	if($orderby!==''){
		$theorder = "order by `$orderby` $order";
	}else{
		$theorder = '';
	}
	$query = DB::query("SELECT * FROM ".DB::table($table)." $condition $theorder $limit");
	while($fetch = DB::fetch($query)){
		$result[] = $fetch;	
	}
	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table($table)." $condition");
	$multi = multi($count, $rows, $page, $pageurl);
	$return = array($result,$multi);
	return $return;
}

function fetch($bid){
	$query = DB::fetch_first("SELECT * FROM ".DB::table('bkshop')." WHERE `id` = '$bid'");
	return $query;
}

//用户配置函数
function config($switch = 1, $repeat = 0, $level = 3, $vertical = 1){
	$true = 'selected="selected"';
	if($switch == 1){
		$on = $true;
		$off = '';
	}else{
		$on = '';
		$off = $true;
	}
	echo '<p>开启背景&#65306;<select name="switch" style="width:80px;">
	<option value="1" '.$on.'>开</option>
	<option value="0" '.$off.'>关</option>
	</select></p>';
	if($repeat == 1){
		$repeat = $true;
		$norepeat = '';
		$repeatx = '';
		$repeaty = '';
	}elseif($repeat == 0){
		$repeat = '';
		$norepeat = $true;
		$repeatx = '';
		$repeaty = '';
	}elseif($repeat == 2){
		$repeat = '';
		$norepeat = '';
		$repeatx = $true;
		$repeaty = '';
	}else{
		$repeat = '';
		$norepeat = '';
		$repeatx = '';
		$repeaty = $true;
	}
	echo '<p>背景重复&#65306;<select name="repeat" style="width:80px;">
	<option value="1" '.$repeat.'>是</option>
	<option value="0" '.$norepeat.'>否</option>
	<option value="2" '.$repeatx.'>横向</option>
	<option value="3" '.$repeaty.'>纵向</option>	
	</select></p>';
	if($level == 1){
		$left = $true;
		$center = '';
		$right = '';
	}elseif($level == 2){
		$left = '';
		$center = $true;
		$right = '';
	}else{
		$left = '';
		$center = '';
		$right = $true;
	}
	echo '<p>水平位置&#65306;<select name="level" style="width:80px;">
	<option value="1" '.$left.'>左</option>
	<option value="2" '.$center.'>中</option>
	<option value="3" '.$right.'>右</option>
	</select></p>';	
	if($vertical == 1){
		$up = $true;
		$middle = '';
		$down = '';
	}elseif($vertical == 2){
		$up = '';
		$middle = $true;
		$down = '';
	}else{
		$up = '';
		$middle = '';
		$down = $true;
	}
	echo '<p>垂直位置&#65306;<select name="vertical" style="width:80px;">
	<option value="1" '.$up.'>上</option>
	<option value="2" '.$middle.'>中</option>
	<option value="3" '.$down.'>下</option>
	</select>
	</p>';	
}

?>