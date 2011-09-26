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
		function _buildStyle() {
			global $_G; $cache=$_G['cache']['plugin']['kk_weibo'];
			$result=include DISCUZ_ROOT.'/source/plugin/kk_weibo/kk_weibo.style.php';
			//if(!empty($cache['data_tpl'])) $result['data_tpl']=$cache['data_tpl'];
			//if(!empty($cache['btn_add_tpl'])) $result['btn_add_tpl']=$cache['btn_add_tpl'];
			//if(!empty($cache['btn_del_tpl'])) $result['btn_del_tpl']=$cache['btn_del_tpl'];
			if(!empty($cache['css_append'])) $result['css_output'].="\n".$cache['css_append'];
			$this->context['style']=$result;
		}		
		function _replaceTplMacro($tpl,$param) {
			foreach($param as $key=>$value) $tpl=str_replace('{'.$key.'}',$value,$tpl);
			return $tpl;
		}
    }    
    class plugin_kk_weibo_forum extends plugin_kk_weibo {
		function __construct() {$this->_buildStyle();}
		function _button_output($post) {
			global $_G; $tree=$this->context['tree']; $style=$this->context['style'];
			$cur_uid=$post['authorid']; $cur_stat=$tree[$cur_uid];
			$cur_param=Array(
				'url_add' 		=> "/plugin.php?id=kk_weibo:service&action=add&rel={$cur_uid}",
				'click_add' 	=> "showWindow('kk_weibo',this.href);return false;",
				'url_del' 		=> "/plugin.php?id=kk_weibo:service&action=del&rel={$cur_uid}",
				'click_del' 	=> "showWindow('kk_weibo',this.href);return false;",
				'uid' 			=> $cur_uid,
				);
			
			if($_G['uid']==$cur_uid) return '';
			else if($cur_stat['already_attention']) return $this->_replaceTplMacro($style['btn_del_tpl'],$cur_param);
			else return $this->_replaceTplMacro($style['btn_add_tpl'],$cur_param);
		}
		function _data_output($post) {
			global $_G; $tree=$this->context['tree']; $style=$this->context['style'];
			$cur_output=Array(); $cur_uid=$post['authorid']; $cur_stat=$tree[$cur_uid];
			$cur_param=Array(
				'count_attention' 	=> $cur_stat['count_attention'],
				'count_fans' 		=> $cur_stat['count_fans'],
				'posts' 			=> $post['posts'],
				'threads' 			=> $post['threads'],
				'doings' 			=> $post['doings'],
				'url_attention' 	=> "/plugin.php?id=kk_weibo:weibo&rel={$cur_uid}",
				'url_fans' 			=> "/plugin.php?id=kk_weibo:weibo&rel={$cur_uid}&view=fans",
				'uid' 				=> $cur_uid,
				);
			
			$cur_output[]=$this->_replaceTplMacro($style['data_tpl'],$cur_param);
			if($style['btn_position']==2) $cur_output[]=$this->_button_output($post);
			return implode("\n",$cur_output);
		}
		function viewthread_avatar_output() {
			global $_G,$postlist; $result=Array(); $style=$this->context['style'];
			if($style['data_position']==1) return Array();
			$this->_buildUIDTree();			
			foreach($postlist as $post) $result[]=$this->_data_output($post);
    		return $result;
		}
    	function viewthread_sidetop_output() {
    		global $_G,$postlist; $result=Array();$style=$this->context['style'];
			if($style['data_position']==2) return Array();
			$this->_buildUIDTree();			
			foreach($postlist as $post) $result[]=$this->_data_output($post);
    		return $result;
    	}
    	function viewthread_sidebottom_output() {
			global $_G,$postlist; $result=Array(); $style=$this->context['style'];
    		foreach($postlist as $post) {
				if($style['btn_position']==1) $result[]=$this->_button_output($post);				
    		}
    		return $result;
    	}
		function viewthread_top_output() {
			$result=Array('<style>'); $style=$this->context['style'];			
			$result[]=$style['css_output'];
			$result[]='</style>';
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