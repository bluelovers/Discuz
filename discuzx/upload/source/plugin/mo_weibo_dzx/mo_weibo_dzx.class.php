<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_mo_weibo_dzx {

	var $wbshow = 0;

	function plugin_mo_weibo_dzx() {
		global $_G;

		$gweibo = $_G['cache']['plugin']['mo_weibo_dzx'];
		$lbtn=$_G[config][output][language]=='zh_cn'?'release':'release_tc';
		$this->wbshow = (array)unserialize($gweibo['mo_weibo_show']);
	if(!in_array(4,$this->wbshow)){
		$this->wbcount = $gweibo['mo_weibo_count'];
		$this->wbspeed = $gweibo['mo_weibo_speed'];
		$this->col = $gweibo['col'];
		$nextspeed = $this->wbspeed / $this->col ;
		$this->line = $gweibo['mo_weibo_line'];
		$this->type = $gweibo['mo_weibo_type'];
		$this->scroll = $gweibo['mo_weibo_scroll'];
		$this->buser = $gweibo['mo_weibo_buser'];
		$this->bgroup = (array)unserialize($gweibo['mo_weibo_bgroup']);
		if($this->col==1){
			$width_ol='100%';
			$num_ol=1;
		}elseif($this->col==2){
			$width_ol='49%';
			$num_ol=2;
		}else{
			$width_ol='33%';
			$num_ol=3;
		}
		$sqluser = $this->buser?'and d.uid not in ('.$this->buser.') ':'and ';
		if($this->type==4) $sqlorder = $sqluser."d.dateline> $_G[timestamp]-2592000 order by d.replynum";
		elseif($this->type==3) $sqlorder = $sqluser."d.dateline > $_G[timestamp]-604800 order by d.replynum";
		elseif($this->type==2) $sqlorder = $sqluser."d.dateline > $_G[timestamp]-86400 order by d.replynum";
		else $sqlorder = $this->buser?'and d.uid not in ('.$this->buser.') order by d.doid':'order by d.doid';
		$count = 0;
		$oheight = $this->line*60;
		$wbcontent = '<script type="text/javascript" src="source/plugin/mo_weibo_dzx/mo_weibo_dzx.js"></script><style type="text/css">#mo_wb{height:'.$oheight.'px; overflow:hidden; width:99%;} #mo_wb .wbc{height:'.$oheight.'px; width:'.$width_ol.'; float:left; overflow:hidden;} #mo_wb li{width:100%; padding:5px 0; height:48px; overflow:hidden;} #mo_wb li p{line-height:20px; height:20px; padding:2px 5px; overflow:hidden;} #mo_wb .avatar img{height:42px; padding:2px; border:1px #ccc solid;} #mood_mystatus{line-height:24px; width:380px; margin:10px 0;} a:hover{text-decoration:none;} .momoodfm{margin:15px;} .momoodfm textarea{width:310px; padding:5px; height:50px;} .momoodfm .moodfm_f{padding:5px; line-height:24px; vertical-align:middle;} .facel{width:260px; top:0; padding:0 6px 6px 0;} .facel img{margin:6px 0 0 6px;}.moodfm_btn{padding-left: 5px; background: url(static/image/common/mood_input_btn.png) no-repeat 5px 0;} .moodfm_btn button {width: 58px; height: 58px; cursor: pointer; opacity: 0; filter: alpha(opacity=0);}</style>';
		$wbcontent .= '<div id="mo_wb">';
		$wbcol_list=array();
		$query=DB::query("SELECT d.*,m.groupid from ".DB::table('home_doing')." d, ".DB::table('common_member')." m where d.uid=m.uid ".$sqlorder." DESC limit 0, $this->wbcount");
		if($this->scroll==2){
			while($value=DB::fetch($query)){
				$count=$count==$this->col?0:$count;
				if(!$this->bgroup || !in_array($value[groupid],$this->bgroup)){
					$wbcol_list[$count] .= '<li><a href="home.php?mod=space&uid='.$value[uid].'" target="_blank" class="avatar"><img src="'.avatar($value[uid],'small',true).'" align="left" /></a><p><a href="home.php?mod=space&uid='.$value[uid].'" target="_blank" c="1">'.$value[username].'</a>: <a href="home.php?mod=space&do=doing&doid='.$value[doid].'">'.strip_tags($value['message'], '<b><font><img>').'</a></p><p><em class="xg1">'.dgmdate($value['dateline'],'u').'&nbsp;<a href="plugin.php?id=mo_weibo_dzx&mod=reply&doid='.$value[doid].'" onclick="showWindow(\'mo_weibo_dzx\',this.href)">['.lang('plugin/mo_weibo_dzx', 'reply').']['.$value['replynum'].']</a></em></p></li>';
					$count++;
				}
			}
			$mo_script = "mo_weibo('wbc0','mo_wbcl0','".$this->wbspeed."');";
			for($i=0;$i<$this->col;$i++){
				$wbcontent .= '<div class="wbc" id="wbc'.$i.'"><ul id="mo_wbcl'.$i.'">'.$wbcol_list[$i].'</ul></div>';
				if($i>0) $mo_script .= "setTimeout(\"mo_weibo('wbc".$i."','mo_wbcl".$i."','".$this->wbspeed."')\",".$nextspeed*$i.");";
			}
			$wbcontent .= '</div><script type="text/javascript">'.$mo_script.'</script>';
		}else{
			$wbcontent .= '<ul id="mo_wbc"><li>';
			$count = 0;
			while($value=DB::fetch($query)){
				if($count>0 && $count%$this->col==0) $wbcontent .= '</li><li>';
				if(!$this->bgroup || !in_array($value[groupid],$this->bgroup)){
					$wbcontent .= '<ol class="wbc"><a href="home.php?mod=space&uid='.$value[uid].'" target="_blank" class="avatar"><img src="'.avatar($value[uid],'small',true).'" align="left" /></a><p><a href="home.php?mod=space&uid='.$value[uid].'" target="_blank" c="1">'.$value[username].'</a>: <a href="home.php?mod=space&do=doing&doid='.$value[doid].'">'.strip_tags($value['message'], '<b><font><img>').'</a></p><p><em class="xg1">'.dgmdate($value['dateline'],'u').'&nbsp;<a href="plugin.php?id=mo_weibo_dzx&mod=reply&doid='.$value[doid].'" onclick="showWindow(\'mo_weibo_dzx\',this.href)">['.lang('plugin/mo_weibo_dzx', 'reply').']['.$value['replynum'].']</a></em></p></ol>';
					$count++;
				}
			}
			$wbcontent .="</li></ul></div><script type=\"text/javascript\">mo_weibo('mo_wb','mo_wbc','".$this->wbspeed."')</script>";
		}
		
		$wbtc=DB::result_first("SELECT count(*) from ".DB::table('home_doing'));
		if($_G['uid']){
			$wbmc=DB::result_first("SELECT count(*) from ".DB::table('home_doing')." where uid='$_G[uid]'");
			$wbmcr=', '.lang('plugin/mo_weibo_dzx', 'wb_me').$wbmc.lang('plugin/mo_weibo_dzx', 'wb_end');
		}

		return '<div class="fl bm"><div class="bm cl bmw flg"><div class="bm_h"><a href="plugin.php?id=mo_weibo_dzx&mod=doing" onclick=showWindow(\'mo_weibo_dzx\',this.href) style="margin-left:10px; display:block; height:100%; width:71px; background:url(source/plugin/mo_weibo_dzx/images/'.$lbtn.'.png) no-repeat 0 50%; float:right;"></a><span class="y banzu"><em>'.lang('plugin/mo_weibo_dzx', 'total').$wbtc.lang('plugin/mo_weibo_dzx', 'wb_end').$wbmcr.'</em></span><h2><a>'.$gweibo['mo_weibo_title'].'</a></h2></div><div class="bm_c" style="height:'.$oheight.'px; overflow:hidden;">'.$wbcontent.'</div></div></div>';
	}
	}
	function global_footer() {
		global $_G;

		$buser = explode(',',$_G['cache']['plugin']['mo_weibo_dzx']['admin']);
		$bgroup = (array)unserialize($_G['cache']['plugin']['mo_weibo_dzx']['mo_weibo_bgroup']);
		if($_G['cache']['plugin']['mo_weibo_dzx']['mo_weibo_popup']>0 && $_COOKIE['mo_weibo_dzx']!=1 && $_G[uid] && !in_array($_G[uid],$buser) && !in_array($_G[groupid],$bgroup)){
			$nowshort=date("Y-m-d");
			$lastwbt=DB::result_first("SELECT dateline from ".DB::table('home_doing')." WHERE uid=".$_G[uid]." order by dateline desc limit 1");
			$lastwb=date('Y-m-d',$lastwbt);
			if($nowshort>$lastwb){
				return '<style type="text/css">.momoodfm{margin:15px;} .momoodfm textarea{width:310px!important; padding:5px; height:50px;} .momoodfm .moodfm_f{padding:5px; line-height:24px; vertical-align:middle;} .facel{width:260px; top:0; padding:0 6px 6px 0;} .facel img{margin:6px 0 0 6px;}.moodfm_btn{padding-left: 5px; background: url(static/image/common/mood_input_btn.png) no-repeat 5px 0;} .moodfm_btn button {width: 58px; height: 58px; cursor: pointer; opacity: 0; filter: alpha(opacity=0);}</style><script type="text/javascript">onload=function(){showWindow(\'mo_weibo_dzx\',\'plugin.php?id=mo_weibo_dzx&mod=doing\');}</script>';
			}
		}
	}
}

class plugin_mo_weibo_dzx_forum extends plugin_mo_weibo_dzx {

	function index_top_output() {
		if(in_array(1,$this->wbshow)) {
			$mywb = parent::plugin_mo_weibo_dzx();
			return $mywb;
		}
	}
	
	function forumdisplay_top_output(){
		if(in_array(2,$this->wbshow)) {
			$mywb = parent::plugin_mo_weibo_dzx();
			return $mywb;
		}
	}
	function viewthread_top_output(){
		if(in_array(3,$this->wbshow)) {
			$mywb = parent::plugin_mo_weibo_dzx();
			return $mywb;
		}
	}
}

?>