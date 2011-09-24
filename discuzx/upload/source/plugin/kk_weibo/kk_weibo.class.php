<?php
    if(!defined('IN_DISCUZ')) exit('Access Denied');        
    class plugin_kk_weibo {
    	protected $context=Array('tree'=>Array());				
		
		function global_userabout_bottom($param) {
			//plugin::kk_weibo			
			$result='<ul><li><a href="/plugin.php?id=kk_weibo:weibo"><img width="16" height="16" src="static/image/feed/file.gif">关注</a></li>';			
			return Array('home::space'=>$result,'plugin::kk_weibo'=>$result);
		}
        //------------------------------------------------------------------------------------------
		function _getUIDList($str,$max=50) {
			$result=Array(); $index=0;
			foreach(explode(',',$str) as $cur) {
				$cur=(int)$cur; if($cur>0) $result[]=$cur;
				if($index>$max) break;
				$index+=1;
			}
			return implode(',',$result);
		}
		function _buildUIDTree() {
			global $_G,$postlist; $result=Array();
			foreach($postlist as $post) {
				$result[$post['authorid']]=Array('count_attention'=>0,'count_fans'=>0,'already_attention'=>false);
			}
			$uid_string=implode(',',array_keys($result));$table=DB::table('kk_weibo_stat');			
			$query=DB::query("select uid,count_attention,count_fans from {$table} where uid in ({$uid_string})");
			while($cur_fetch=DB::fetch($query)) {
				$cur_uid=$cur_fetch['uid'];
				$result[$cur_uid]=array_merge($result[$cur_uid],$cur_fetch);
			}
			//--------------------------------------------------------------------------------------
			$table=DB::table('kk_weibo');
			$query=DB::query("select uid_rel from {$table} where uid={$_G['uid']} and uid_rel in ({$uid_string})");
			while($cur_fetch=DB::fetch($query)) {
				$cur_uid=$cur_fetch['uid_rel'];
				$result[$cur_uid]['already_attention']=true;
			}
			//--------------------------------------------------------------------------------------
			$this->context['tree']=$result;
		}
    }    
    class plugin_kk_weibo_forum extends plugin_kk_weibo {		
    	function viewthread_sidetop_output() {
    		global $postlist; $result=Array();
			$this->_buildUIDTree(); $tree=$this->context['tree'];
			foreach($postlist as $post) {
				$cur_output=Array(); $cur_uid=$post['authorid']; $cur_stat=$tree[$cur_uid]; //var_dump($cur_stat);
				$cur_output[]="<div class=\"kk_weibo_top\"><ul>";
				$cur_output[]="<li><div class=\"num\"><a href=\"/plugin.php?id=kk_weibo:weibo&rel={$cur_uid}\" target=\"_blank\">{$cur_stat['count_attention']}</a></div>关注</li>";
				$cur_output[]="<li class=\"li_fans\"><div class=\"num\"><a href=\"plugin.php?id=kk_weibo:weibo&rel={$cur_uid}&view=fans\" target=\"_blank\">{$cur_stat['count_fans']}</a></div>粉丝</li>";
				$cur_output[]="<li class=\"li_posts\"><div class=\"num\"><a href=\"/home.php?mod=space&uid={$cur_uid}&do=thread&type=reply\" target=\"_blank\">{$post['posts']}</a></div>帖子</li>";
				$cur_output[]="</ul></div><br clear=\"both\"/>";
				$result[]=implode('',$cur_output);
    		}
    		return $result;
    	}
    	function viewthread_sidebottom_output() {
    		global $_G,$postlist; $result=Array(); $tree=$this->context['tree'];
    		foreach($postlist as $post) {
				$cur_output=Array(); $cur_uid=$post['authorid']; $cur_stat=$tree[$cur_uid];
				if($_G['uid']==$post['authorid']) $cur_output[]='';
				else {
					$cur_output[]="<div class=\"kk_weibo_bottom\">";
					if($cur_stat['already_attention']) {
						$cur_output[]="<span class=\"icon2\"><a href=\"javascript:void(0)\">已关注</a></span>";
					} else {
						$cur_output[]="<span class=\"icon1\"><a href=\"/plugin.php?id=kk_weibo:service&action=add&rel={$cur_uid}\" onclick=\"showWindow('kk_weibo',this.href);return false;\">加关注</a></span>";
					}
					$cur_output[]="</div>";	
				}				
				$result[]=implode('',$cur_output);
    		}
    		return $result;
    	}
		function viewthread_top_output() {
			global $postlist; $result=Array();
			$result[]='<style>';
			$result[]='.kk_weibo_top {margin-left:20px;}';
			$result[]='.kk_weibo_top li {float:left;padding:2px 0px;width:30px;}';
			$result[]='.kk_weibo_top li a{color:#336699;}';
			$result[]='.kk_weibo_top li.li_fans {padding:2px 4px 2px 8px;margin-right:6px;border:1px solid #CCC;border-width:0px 1px;}';
			$result[]='.kk_weibo_top li div.num {font-size:14px;font-family:Arial;font-weight:bold;}';
			$result[]='.kk_weibo_top li.li_posts {width:50px;}';
			$result[]='.kk_weibo_bottom {margin-left:20px;margin-top:-4px;}';
			$result[]='.kk_weibo_bottom span {padding-top:2px;padding-left:16px;text-indent:16px;}';
			$result[]='.kk_weibo_bottom span a{color:#336699;}';
			$result[]='.kk_weibo_bottom span.icon1 {background:url(/static/image/common/addbuddy.gif) no-repeat -2px 0px;}';
			$result[]='.kk_weibo_bottom span.icon2 {background:url(/static/image/common/data_valid.gif) no-repeat 0px 2px;}';
			$result[]='</style>';			
			//$result[]='<link rel="stylesheet" type="text/css" href="/source/plugin/kk_weibo/res/viewthread.css" />';			
			return implode("\n",$result);
		}    	
    }
	class plugin_kk_weibo_home extends plugin_kk_weibo {
		function space_home_navlink() {
			global $_G; $cache=$_G['cache']['plugin']['kk_weibo'];
            if(!$cache['open_tag']) return '';            
			$selected=($_GET['kk_weibo']=='1')?' class="a"':'';
			return "<li{$selected}><a href=\"home.php?mod=space&do=home&view=all&kk_weibo=1\">关注的动态</a></li>";			
		}
	}	
?>