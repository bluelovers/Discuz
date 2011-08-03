<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


class plugin_amucallme_dzx{

	function plugin_amucallme_dzx(){
		global $_G;
		$this->mvars = $_G['cache']['plugin']['amucallme_dzx'];
		$this->fids=(array)unserialize($this->mvars['fids']);
		$this->gids = (array)unserialize($this->mvars['gids']);
	}

	function searchmembers($condition, $limit=100, $start=0) {
		include_once libfile('class/membersearch');
		$ms = new membersearch();
		return $ms->search($condition, $limit, $start);
	}

	function amucallme_dzx_output($a){
		global $_G;
		if($_G['uid'] && in_array($_G['fid'],$this->fids)){
			$turl = "forum.php?mod=redirect&goto=findpost&ptid={$a['values']['tid']}&pid={$a['values']['pid']}";
			$url = $_G["siteurl"].$turl;$msg = $this->message;
			$reply = $_G["siteurl"]."forum.php?mod=post&action=reply&tid={$a['values']['tid']}&repquote={$a['values']['pid']}";
			if(!$msg){
				if(!function_exists('discuzcode')) {
					include libfile('function/discuzcode');
				}
				$msg = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.cutstr(strip_tags(discuzcode($_G['gp_message'], 1, 0)),100,'...');
			}
			$sendmsg = lang('plugin/amucallme_dzx','sendmsg',array('username' => $_G['username'],'url' => $url,'reply' => $reply,'message' => $msg));
			$cmcost=array();
			if(file_exists('./data/plugindata/amucallme_dzx.data.php')){
				require_once DISCUZ_ROOT.'./data/plugindata/amucallme_dzx.data.php';
				$data_f2a = dstripslashes($data_f2a);
				$cmcost = $data_f2a[$_G['groupid']];
				$cmcost['cost'] = $cmcost['cost']*'-1';
			}
			$max = 0;
			 if($cmcost['extcredits'] && $cmcost['cost']){$max = intval($_G['member']["extcredits{$cmcost['extcredits']}"]/$cmcost['cost']);}else{$max = 100;}
			if($a['values']['tid'] && $a['values']['pid'] && $max){
				foreach($this->usernames as $key => $val){
					 if($val && $_G['uid'] <> $val && $max){
						updatemembercount($_G['uid'], array("extcredits{$cmcost['extcredits']}" => $cmcost['cost']), true,'',0);
						notification_add($val, $_G['uid'], $sendmsg, '', 0);
						$max--;
					 }
				}
				foreach($this->gusernames as $key => $val){
					 if($val && $_G['uid'] <> $val && $max){
						updatemembercount($_G['uid'], array("extcredits{$cmcost['extcredits']}" => $cmcost['cost']), true,'',0);
						notification_add($val, $_G['uid'], $sendmsg, '', 0);
						$max--;
					 }
				}
			}
		}
	}




}

class plugin_amucallme_dzx_forum extends plugin_amucallme_dzx {

	function post_middle_output($a) {
		global $_G;
		$str = $css = '';
		if($_G['uid']){
			$str = '<script language="javascript">';
			$str .= '$("e_adv_s1").innerHTML += \'<div class="b1r" id="amucallme_dzx_tag"><a href="plugin.php?id=amucallme_dzx:callme&adds=e_iframe" onclick="showWindow(\\\'amucallme_dzx_add\\\', this.href);" title="'.lang('plugin/amucallme_dzx','callme').'">'.lang('plugin/amucallme_dzx','callme').'</a></div>\';';
			$str .= '</script>';
			$css = '<style>#amucallme_dzx_tag {border:1px solid #F5F5F5;float:left;overflow:hidden;}';
			$css .= '#amucallme_dzx_tag a {background:url("source/plugin/amucallme_dzx/images/a1.png") no-repeat scroll 0 0 transparent;background-position:center  1px;border:1px solid #F5F5F5;float:left;overflow:hidden;}';
			$css .= '#amucallme_dzx_tag a:hover {background-color:#FFFFFF;border-color:#0099CC;text-decoration:none;}</style>';
		}
		return $str.$css;
	}

	function viewthread_postfooter_output(){		
		global $_G,$postlist;
		$shows = array();
		if($_G['uid']){
			foreach ($postlist as $value){
				$author = strip_tags($value["author"]);
				$username = strip_tags($_G['username']);
				if($author <> $username){
					$authors[] = $author;
					$shows[]= "<a class='add_callme' href='javascript:;' onclick='seditor_insertunit(\"fastpost\", \"[@]".$author."[/@]\");window.scrollTo(0,99999);'>".lang('plugin/amucallme_dzx','callme')."</a>";
				}else{$shows[]= '';}
			}
			if(array_unique($authors)){
				$authors = serialize(array_unique($authors));
				dsetcookie('amucallme_dzx_'.$this->tp, base64_encode($authors), 600);
				dsetcookie('amucallme_dzx_lz'.$this->tp, base64_encode($_G['forum_thread']['author']), 600);
			}
			//var_dump($shows);
		}
		return (array)$shows;
	}

	function viewthread_fastpost_ctrl_extra() {
		global $_G;
		$str = $css = '';
		if($_G['uid']){
			$this->tp = $_G['tid'].'-'.$_G['page'];
			$str = '<div class="amucallme_dzx_tag"><a href="plugin.php?id=amucallme_dzx:callme&adds=fastpostmessage&tp='.$this->tp.'" title="'.lang("plugin/amucallme_dzx","callme").'" onclick="showWindow(\'amucallme_dzx_add\', this.href);">'.lang("plugin/amucallme_dzx","callme").'</a></div>';
			$css = '<style>';
			$css .= '.amucallme_dzx_tag a {background:url("source/plugin/amucallme_dzx/images/a1.png") no-repeat scroll 0 0 transparent;float:left;height:20px;line-height:20px;margin:4px 4px 0px 4px;overflow:hidden;text-indent:-9999px;width:20px;}';
			$css .= '.add_callme{background:url(source/plugin/amucallme_dzx/images/a1.png) no-repeat 0 50%;}</style>';
		}
		return $str.$css;
	}

	function post_amucallme(){
		global $_G;
		if($_G['uid'] && in_array($_G['fid'],$this->fids)){
			$amupma= '/\[@\]([^ ,\[\n]{3,20})\[\/@\]/';
			preg_match_all($amupma,$_G['gp_message'],$amu_pp,PREG_SET_ORDER);
			loaducenter();
			foreach($amu_pp as $key => $val){
				$ucresult = uc_user_checkname($val[1]);
				 if($_G['group']['allowposturl'] && $ucresult == -3){
					 $_G['gp_message'] = str_replace($val[0], "[url=home.php?mod=space&username=".$val[1]."]@".$val[1]."[/url]",$_G['gp_message']);
				 }elseif(!$_G['group']['allowposturl'] && $ucresult == -3){
					 $_G['gp_message'] = str_replace($val[0], "".$val[1]."",$_G['gp_message']);
				 }
				 $val = $val[1];
				 $pp[] = $val;
			}
			$pp["username"] = array_diff($pp, array(null));
			$authorsd = serialize(array_unique($pp["username"]));
			dsetcookie('amucallme_dzx_ed', base64_encode($authorsd), 600);
			if($pp["username"]){
				$this->usernames = $this->searchmembers($pp);
			}
		}
		if($_G['uid'] && in_array($_G['fid'],$this->fids) && in_array($_G['groupid'],$this->gids)){
			$amupma= '/\[@=group\]([0-9]+)\[\/@\]/';
			preg_match_all($amupma,$_G['gp_message'],$amu_pp,PREG_SET_ORDER);
			$callgids = (array)unserialize($_G['cache']['plugin']['amucallme_dzx']['callgids']);
			loadcache('usergroups');
			for($i=0;$i<=count($callgids);$i++){
				$value['groupid'] = $callgids[$i];
				$value['grouptitle'] = $_G['cache']['usergroups'][$callgids[$i]]['grouptitle'];
				$groups[$callgids[$i]] = $value;
			}
			foreach($amu_pp as $key => $val){
				 if($_G['group']['allowposturl'] && in_array($val[1],$callgids)){
					 $_G['gp_message'] = str_replace($val[0], "[url=home.php?mod=spacecp&ac=usergroup&gid=".$val[1]."]@".strip_tags($groups[$val[1]]['grouptitle'])."[/url]",$_G['gp_message']);
				 }elseif(!$_G['group']['allowposturl'] && $ucresult == -3){
					 $_G['gp_message'] = str_replace($val[0], "".strip_tags($groups[$val[1]]['grouptitle'])."",$_G['gp_message']);
				 }
				 $ppg[] = $val[1];
			}
			if(!function_exists('discuzcode')) {
				include libfile('function/discuzcode');
			}
			$this->message = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.cutstr(strip_tags(discuzcode($_G['gp_message'], 1, 0)),40,'...');
			$ppg["groupid"] = array_diff($ppg, array(null));
			if($ppg["groupid"]){
				$this->gusernames = $this->searchmembers($ppg);
			}
		}
	}

	function post_amucallme_output($a){
		global $_G;
		$this->amucallme_dzx_output($a);
	}
}



?>
