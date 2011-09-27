<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!$_G['uid'] && $_G[gp_mod]!='portal' && $_G[gp_mod]!='feed') showmessage('not_loggedin', NULL, array(), array('login' => 1));
$buser = $_G['cache']['plugin']['mo_weibo_dzx']['mo_weibo_buser'];
$busera = explode(',',$buser);
$bgroup = (array)unserialize($_G['cache']['plugin']['mo_weibo_dzx']['mo_weibo_bgroup']);
if((in_array($_G[uid],$busera) || in_array($_G[groupid],$bgroup)) && $_G[gp_mod]!='portal' && $_G[gp_mod]!='feed') showmessage('forum_access_view_disallow');
$mod = $_G[gp_mod];
$doid = $_G[gp_doid];
$isrefresh = $_G['cache']['plugin']['mo_weibo_dzx']['mo_weibo_refresh'];
$pophours = $_G['cache']['plugin']['mo_weibo_dzx']['mo_weibo_popup'];
if($mod=='portal'){
	$gweibo = $_G['cache']['plugin']['mo_weibo_dzx'];
	$wbcount = $gweibo['mo_weibo_count'];
	$wbspeed = $gweibo['mo_weibo_speed'];
	$col = $_G[gp_col];
	$nextspeed = $wbspeed / $col ;
	$line = $_G[gp_row];
	$type = $gweibo['mo_weibo_type'];
	$scroll = $_G[gp_scroll];
	$buser = $gweibo['mo_weibo_buser'];
	$bgroup = (array)unserialize($gweibo['mo_weibo_bgroup']);
	if($col==1){
		$width_ol='100%';
		$num_ol=1;
	}elseif($col==2){
		$width_ol='49%';
		$num_ol=2;
	}else{
		$width_ol='33%';
		$num_ol=3;
	}
	$sqluser = $buser?'and d.uid not in ('.$buser.') ':'and ';
	if($type==4) $sqlorder = $sqluser."d.dateline> $_G[timestamp]-2592000 order by d.replynum";
	elseif($type==3) $sqlorder = $sqluser."d.dateline > $_G[timestamp]-604800 order by d.replynum";
	elseif($type==2) $sqlorder = $sqluser."d.dateline > $_G[timestamp]-86400 order by d.replynum";
	else $sqlorder = $buser?'and d.uid not in ('.$buser.') order by d.doid':'order by d.doid';
	$count = 0;
	$oheight = $line*60;
	$wbcontent = '';
	$wbcol_list=array();
	$query=DB::query("SELECT d.*,m.groupid from ".DB::table('home_doing')." d, ".DB::table('common_member')." m where d.uid=m.uid ".$sqlorder." DESC limit 0, $wbcount");
	if($scroll==2){
		while($value=DB::fetch($query)){
			$count=$count==$col?0:$count;
			if(!$bgroup || !in_array($value[groupid],$bgroup)){
				$wbcol_list[$count] .= '<li><a href="home.php?mod=space&uid='.$value[uid].'" target="_blank" class="avatar"><img src="'.avatar($value[uid],'small',true).'" align="left" /></a><p><a href="home.php?mod=space&uid='.$value[uid].'" target="_blank" c="1">'.$value[username].'</a>: <a href="home.php?mod=space&do=doing&doid='.$value[doid].'" target="_blank">'.strip_tags($value['message'], '<b><font><img>').'</a></p><p><em class="xg1">'.dgmdate($value['dateline'],'u').'&nbsp;<a href="home.php?mod=space&do=doing&doid='.$value[doid].'" target="_blank">['.lang('plugin/mo_weibo_dzx', 'reply').']['.$value['replynum'].']</a></em></p></li>';
				$count++;
			}
		}
		$mo_script = "mo_weibo('wbc0','mo_wbcl0','".$wbspeed."');";
		for($i=0;$i<$col;$i++){
			$wbcontent .= '<div class="wbc" id="wbc'.$i.'"><ul id="mo_wbcl'.$i.'" style="margin:0; padding:0;">'.$wbcol_list[$i].'</ul></div>';
			if($i>0) $mo_script .= "setTimeout(\"mo_weibo('wbc".$i."','mo_wbcl".$i."','".$wbspeed."')\",".$nextspeed*$i.");";
		}
	}else{
		$wbcontent .= '<ul id="mo_wbc" style="margin:0; padding:0;"><li>';
		$count = 0;
		while($value=DB::fetch($query)){
			if($count>0 && $count%$col==0) $wbcontent .= '</li><li>';
			if(!$bgroup || !in_array($value[groupid],$bgroup)){
				$wbcontent .= '<ol class="wbc"><a href="home.php?mod=space&uid='.$value[uid].'" target="_blank" class="avatar"><img src="'.avatar($value[uid],'small',true).'" align="left" /></a><p><a href="home.php?mod=space&uid='.$value[uid].'" target="_blank" c="1">'.$value[username].'</a>: <a href="home.php?mod=space&do=doing&doid='.$value[doid].'" target="_blank">'.strip_tags($value['message'], '<b><font><img>').'</a></p><p><em class="xg1">'.dgmdate($value['dateline'],'u').'&nbsp;<a href="home.php?mod=space&do=doing&doid='.$value[doid].'" target="_blank">['.lang('plugin/mo_weibo_dzx', 'reply').']['.$value['replynum'].']</a></em></p></ol>';
				$count++;
			}
		}
		$wbcontent .="</li></ul><script type=\"text/javascript\">mo_weibo('mo_wb','mo_wbc','".$wbspeed."')</script>";
	}

	$portaldiy = $wbcontent;
}elseif($mod=='feed'){
	include_once libfile('function/feed');
	$mo_feeds = array();
	$fcount = intval($_GET[fc]);
	$fheight = intval($_GET[fh]);
	$query=DB::query("SELECT * from ".DB::table('home_feed')." order by dateline desc limit $fcount");
	while($value=DB::fetch($query)){
		$mo_feeds[] = mkfeed($value);
	}
}

include_once template('mo_weibo_dzx:'.$mod);
?>