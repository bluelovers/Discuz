<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!isset($_G['cache']['plugin'])){
	loadcache('plugin');
}

@extract($_G['cache']['plugin']['active']);

$activeclass=parconfig($activeclass);
//print_r($activeclass);

$montharray=array();
$lastmonthnum=10;
$thismonthbegin=strtotime(date('Y-m-01',$_G['timestamp']));
$montharray[0]=$thismonthbegin;
for($i=1;$i<$lastmonthnum;$i++){
	$montharray[$i]=strtotime("-$i month",$thismonthbegin);
}
$lastmonthago=end($montharray);

$appurl=$_G['siteurl']."plugin.php?id=active:index";
	$where=$pageadd='';
	$cid=intval($_G['gp_c']);
	if($cid){
		$where="where cid='$cid'";
		$pageadd="&c=$cid";
	}
	if(isset($_G['gp_m'])){
		$where=monthwhere($_G['gp_m']);
		$pageadd="&m={$_G['gp_m']}";
	}
	if($_G['gp_stitle']){
		$stitle=addslashes($_G['gp_stitle']);
		$where="where title like '%$stitle%'";
		$stitleenc=urlencode($stitle);
		$pageadd="&stitle=$stitleenc";
	}
	//echo $where;
	$page=$_G['page'];
	$begin=($page-1)*$indexpagenum;
	$manylist=array();
	$rs=DB::query("SELECT * FROM ".DB::table('plugin_active')." $where ORDER BY end desc LIMIT $begin , $indexpagenum");
	while ($rw=DB::fetch($rs)){
		$manylist[]=$rw;
	}
	$allnum=DB::result_first("SELECT count(*) FROM ".DB::table('plugin_active')." $where");
	$pagenav=multi($allnum,$indexpagenum,$page,$appurl."&p=$p".$pageadd);
//print_r($manylist);

$allactivenum=DB::result_first("SELECT count(*) FROM ".DB::table('plugin_active'));

include(template('active:index'));

function parconfig($str){
$return=array();
$array=explode("\n",str_replace("\r","",$str));
foreach ($array as $v){
   $t=explode("=",$v);
   $t[0]=trim($t[0]);
   $return[$t[0]]=$t[1];
}
return $return;
} 
function classnum($cid){
	return DB::result_first("SELECT count(*) FROM ".DB::table('plugin_active')." WHERE `cid` ='$cid'");
}
function monthnum($m){
	global $montharray,$lastmonthago;
	$begin=$montharray[$m-1];
	$end=$montharray[$m];
	if($m<0){
		echo $where;
		$where="WHERE `begin`<$lastmonthago";
	}elseif($m==0){
		$where="WHERE `begin`>=$end";
	}else{
		$where="WHERE `begin`>=$end and `begin`<$begin";
	}
	return DB::result_first("SELECT count(*) FROM ".DB::table('plugin_active')." $where");
}
function monthwhere($m){
	global $montharray,$lastmonthago;
	$begin=$montharray[$m-1];
	$end=$montharray[$m];
	if($m<0){
		echo $where;
		$where="WHERE `begin`<$lastmonthago";
	}elseif($m==0){
		$where="WHERE `begin`>=$end";
	}else{
		$where="WHERE `begin`>=$end and `begin`<$begin";
	}
	return $where;
}
?>
