<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_study_linkkiller{

	function global_footer(){
		
		global $_G;		
		$plugin_study_linkkiller = $_G['cache']['plugin']['study_linkkiller'];
		$study_urlexp = $plugin_study_linkkiller['study_urlexp'];
		$study_urllen = $plugin_study_linkkiller['study_urllen'];
		$study_urllen = !empty($study_urllen)? $study_urllen:'30';
		$study_urlloc = $plugin_study_linkkiller['study_urlloc'];
		$study_urlloc = !empty($study_urlloc)? 'outlink.php?url=':'';
		include template('study_linkkiller:presentation');
		
		//print_r($_G);
		return $return;
	}
}

class plugin_study_linkkiller_forum extends plugin_study_linkkiller{

	function viewthread_postbottom_output() {
		
		global $_G, $postlist;

		$plugin_study_linkkiller = $_G['cache']['plugin']['study_linkkiller'];
		$study_groups = unserialize($plugin_study_linkkiller['study_groups']);
		$study_fids = unserialize($plugin_study_linkkiller['study_forums']);
		$study_wesites = explode("\r\n",$plugin_study_linkkiller['study_wesites']);
		$study_urlway = $plugin_study_linkkiller['study_urlway'];
		$study_rel = $plugin_study_linkkiller['study_rel'];
		$study_urlpre = $plugin_study_linkkiller['study_urlpre'];
		
		if($study_urlpre == '1'){
			$study_rel = $study_rel.' onClick="study_disposeurl(this); return false;"';
		}
		
		$killlink = false;
		if($study_urlway == '1' && IS_ROBOT){
			$killlink = true;
		}else if($study_urlway == '2'){
			$killlink = true;
		}
		
		if($killlink){
			if(in_array($_G['fid'], $study_fids)){
		
				foreach($postlist as $id => $post) {
					
					$preg = '#<a(.*)href="http://(.*)"(.*)>#iUs';
					preg_match_all($preg,$post['message'],$arr);

					
					if(!empty($arr)){

						foreach($arr[1] as $k => $ar) {
								
								$welink = false;
								foreach($study_wesites as $key => $site) {
									if(!empty($site)){
										if(stristr($arr[2][$k],$site)){
											
												$welink = true;
												
										}
									}
									
								}
								if(!stristr($arr[1][$k],$study_rel) && !$welink){
											
											$find = '<a'.$arr[1][$k].'href="http://'.$arr[2][$k].'"'.$arr[3][$k].'>';
											$replace = '<a'.$arr[1][$k].'href="http://'.$arr[2][$k].'" '.$study_rel.$arr[3][$k].'>';
						
											$post['message'] = str_replace($find,$replace,$post['message']);
											$postlist[$id] = $post;
											
								}
						}
					}
					$arr = array();
				}
			}
		}
		return array();
	}
}
?>