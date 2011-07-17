<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/delete');
class threadplugin_imgpoll {

	var $name = '图片投票';
	var $buttontext = '发布图片投票';
	var $iconfile = 'source/plugin/imgpoll/new.gif';
	var $identifier = 'imgpoll';
	
	
	function threadplugin_imgpoll() {
	}
    
    function _str_comment($str){
        $in_sql_comment = cutstr( dhtmlspecialchars(trim($str)), 80, '');
        $search = array("\r\n", "\n", "\r");
        $in_sql_comment = str_replace($search, '', $in_sql_comment);
        return $in_sql_comment; 
    }

	function newthread($fid) {
		global $_G;
		$action = 'newthread';
		include template('imgpoll:post_imgpoll');
		return $return;
	}

	function newthread_submit($fid) {
		global $_G;
		global $imgpollarray;
		global $imgpolloption,$imgpolloptionaid,$imgpolloptiondesc;
		global $attachnew;

		
		$imgpolloption = $_G['gp_imgpolloption'];
		$imgpolloptionaid = $_G['gp_imgpolloptionaid'];
		$imgpolloptiondesc= $_G['gp_imgpolloptiondesc'];
		
		$imgpollarray=array();
		foreach($imgpolloption as $key => $value) {
			$imgpolloption[$key] = censor($imgpolloption[$key]);
			$imgpolloptiondesc[$key] = censor($imgpolloptiondesc[$key]);
			if(trim($value) === '' || empty($imgpolloptionaid[$key])) {
				unset($imgpolloption[$key],$imgpolloptionaid[$key],$imgpolloptiondesc[$key]);
			}
		}
		
		if(count($imgpolloption) > $_G['setting']['maxpolloptions']) {
			showmessage('post_poll_option_toomany', '', array('maxpolloptions' => $_G['setting']['maxpolloptions']));
		} elseif(count($imgpolloption) < 2) {
			showmessage('post_poll_inputmore');
		}
		$curpolloption = count($imgpolloption);
		$imgpollarray['maxchoices'] = empty($_G['gp_maxchoices']) ? 0 : ($_G['gp_maxchoices'] > $curpolloption ? $curpolloption : $_G['gp_maxchoices']);
		$imgpollarray['multiple'] = empty($_G['gp_maxchoices']) || $_G['gp_maxchoices'] == 1 ? 0 : 1;
		$imgpollarray['visible'] = empty($_G['gp_visibilitypoll']);
		$imgpollarray['overt'] = !empty($_G['gp_overt']);

		if(!preg_match("/^\d*$/", trim($_G['gp_maxchoices']))) {
			showmessage('poll_maxchoices_expiration_invalid');
		}

		if(preg_match("/^\d*$/", trim($_G['gp_expiration']))) {
			if(empty($_G['gp_expiration'])) {
				$imgpollarray['expiration'] = 0;
			} else {
				$imgpollarray['expiration'] = TIMESTAMP + 86400 * $_G['gp_expiration'];
			}
		} else {
			showmessage('poll_maxchoices_expiration_invalid');
		}
		

	}
	

	function newthread_submit_end($fid) {
		global $_G;
		global $tid,$pid,$uid;
		global $imgpollarray;
		global $imgpolloption,$imgpolloptionaid,$imgpolloptiondesc;

		$uid = $uid ? $uid : $_G['uid'];

		foreach($imgpolloption as $key=>$polloptvalue) {
			$polloptvalue = dhtmlspecialchars(trim($polloptvalue));
			$imgpolloptionaid[$key]=intval($imgpolloptionaid[$key]);
			$imgpolloptiondesc[$key]=dhtmlspecialchars(trim($imgpolloptiondesc[$key]));
			DB::query("INSERT INTO ".DB::table('forum_imgpolloption')." (tid, aid, polloption,optiondescribe) VALUES ('$tid','$imgpolloptionaid[$key]', '$polloptvalue','$imgpolloptiondesc[$key]')");
			convertunusedattach($imgpolloptionaid[$key], $tid, 999999);
		}
		DB::query("INSERT INTO ".DB::table('forum_imgpoll')." (tid, multiple, visible, maxchoices, expiration, overt)
			VALUES ('$tid', '$imgpollarray[multiple]', '$imgpollarray[visible]', '$imgpollarray[maxchoices]', '$imgpollarray[expiration]', '$imgpollarray[overt]')");
	}


	function editpost($fid, $tid) {
		global $_G;
		global $pid,$postlist;
		global $polloptions;
		global $opts;
		$action = 'edit';
		$options = DB::fetch_first("SELECT * FROM ".DB::table('forum_imgpoll')." WHERE tid='$tid'");
		$multiple = $options['multiple'];
		$visible = $options['visible'];
		$maxchoices = $options['maxchoices'];
		$expiration = $options['expiration'];
		$expirationdays=round(($expiration - TIMESTAMP) / 86400);
		$overt = $options['overt'];
			
		
		$query = DB::query("SELECT * FROM ".DB::table('forum_imgpolloption')." WHERE tid='$tid' ORDER BY polloptionid");
		$opts = 1;
		while($options = DB::fetch($query)) {
			$option = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['polloption']);
			$optiondesc = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['optiondescribe']);
			
			$attach = DB::fetch_first("SELECT * FROM ".DB::table(getattachtablebytid($tid))." where aid=".$options['aid']);
			
			if($attach['isimage']) {
				$attachurl = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
				$thumb = $attachurl.($attach['thumb'] ? '.thumb.jpg' : '');
				$width = $thumb && $_G['setting']['thumbwidth'] < $attach['width'] ? $_G['setting']['thumbwidth'] : $attach['width'];
			}
			
			$polloptions[$opts++] = array
			(
				'polloptionid'	=> $options['polloptionid'],
				'aid'           => $options['aid'],
				'polloption'	=> $option,
				'polloptiondesc'=> $optiondesc,
				'attachment'    => $thumb,
				'attachurl'     => $attachurl,
				'width'         => $width
			);
			
		}
		$opts--;
		
		include template('imgpoll:post_imgpoll');
		return $return;
	}

	function editpost_submit($fid, $tid) {
	    global $_G;
	    global $pid,$uid;
	    global $isorigauthor, $close;
	    global $imgpolloptionid,$imgpolloption,$imgpolloptionaid,$imgpolloptiondesc;
	    global $isorigauthor;
	
	    if($_G['group']['alloweditpoll'] || $isorigauthor && !empty($_G['gp_imgpolls'])) {
		$imgpolloptionid = $_G['gp_imgpolloptionid'];
		$imgpolloption = $_G['gp_imgpolloption'];
	   	$imgpolloptionaid = $_G['gp_imgpolloptionaid'];
	   	$imgpolloptiondesc= $_G['gp_imgpolloptiondesc'];
		
		$imgpollarray=array();
		
		foreach($imgpolloption as $key => $value) {						
			if(empty($imgpolloptionid[$key]) && (trim($value) === '' || empty($imgpolloptionaid[$key]))) {
				unset($imgpolloption[$key],$imgpolloptionaid[$key],$imgpolloptiondesc[$key]);
			}
		}
		
		if(count($imgpolloption) > $_G['setting']['maxpolloptions']) {
			showmessage('post_poll_option_toomany', '', array('maxpolloptions' => $_G['setting']['maxpolloptions']));
		} elseif(count($imgpolloption) < 2) {
			showmessage('post_poll_inputmore');
		}
		$curpolloption = count($imgpolloption);
		$imgpollarray['maxchoices'] = empty($_G['gp_maxchoices']) ? 0 : ($_G['gp_maxchoices'] > $curpolloption ? $curpolloption : $_G['gp_maxchoices']);
		$imgpollarray['multiple'] = empty($_G['gp_maxchoices']) || $_G['gp_maxchoices'] == 1 ? 0 : 1;
		$imgpollarray['visible'] = empty($_G['gp_visibilitypoll']);
		$imgpollarray['overt'] = !empty($_G['gp_overt']);
		$imgpollarray['expiration'] = $_G['gp_expiration'];

		if(!preg_match("/^\d*$/", trim($_G['gp_maxchoices'])) || !preg_match("/^\d*$/", trim($_G['gp_expiration']))) {
			showmessage('poll_maxchoices_expiration_invalid');
		}

		$expiration = intval($_G['gp_expiration']);
		if($close) {
			$imgpollarray['expiration'] = TIMESTAMP;
		} elseif($expiration) {
			if(empty($imgpollarray['expiration'])) {
				$imgpollarray['expiration'] = 0;
			} else {
				$imgpollarray['expiration'] = TIMESTAMP + 86400 * $expiration;
			}
		}
		
		$oldaid = array();
		$query = DB::query("SELECT aid FROM ".DB::table('forum_imgpolloption')." WHERE tid='$_G[tid]'");
		while($tempaid = DB::fetch($query)) {
			$oldaid[] = $tempaid['aid'];
		}
		foreach($oldaid as $oldaidvalue){
			if(!in_array($oldaidvalue,$imgpolloptionaid)){
				$query = DB::query("SELECT attachment, thumb, remote, aid, picid FROM ".DB::table(getattachtablebytid($tid))." WHERE aid='$oldaidvalue'");
				while($attach = DB::fetch($query)) {
					@unlink("./{$_G['setting']['attachurl']}forum/imgpoll/".md5($tid)."/".md5($oldaidvalue).".jpg");
					dunlink($attach);
				}
				DB::query("DELETE FROM ".DB::table(getattachtablebytid($tid))." WHERE aid='$oldaidvalue'");
				DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE aid='$oldaidvalue' AND tid='$tid'");
			}
		}
		$tableid= getattachtableid($tid);
		foreach($imgpolloption as $key=>$polloptvalue) {
			$polloptvalue = dhtmlspecialchars(trim($polloptvalue));
			$imgpolloptionaid[$key]=intval($imgpolloptionaid[$key]);
			$imgpolloptiondesc[$key]=dhtmlspecialchars(trim($imgpolloptiondesc[$key]));
			if(!empty($imgpolloptionid[$key])) {
				if(trim($polloptvalue) === ''){
					DB::query("UPDATE ".DB::table('forum_imgpolloption')." SET aid='$imgpolloptionaid[$key]', optiondescribe='$imgpolloptiondesc[$key]' WHERE polloptionid='".$_G['gp_imgpolloptionid'][$key]."' AND tid='$_G[tid]'");
				}else{
					DB::query("UPDATE ".DB::table('forum_imgpolloption')." SET aid='$imgpolloptionaid[$key]', polloption='$polloptvalue', optiondescribe='$imgpolloptiondesc[$key]' WHERE polloptionid='".$_G['gp_imgpolloptionid'][$key]."' AND tid='$_G[tid]'");
				}
			}else{
				DB::query("INSERT INTO ".DB::table('forum_imgpolloption')." (tid, aid, polloption,optiondescribe) VALUES ('$tid','$imgpolloptionaid[$key]', '$polloptvalue','$imgpolloptiondesc[$key]')");
			}
			
			DB::query("UPDATE ".DB::table('forum_attachment')." SET tid='$tid', tableid='$tableid' WHERE aid='$imgpolloptionaid[$key]'");
			
		}

		$newaid = array();
		$query = DB::query("SELECT aid FROM ".DB::table('forum_imgpolloption')." WHERE tid='$_G[tid]'");
		while($tempaid = DB::fetch($query)){
			$newaid[] = $tempaid['aid'];
		}

		
		foreach($newaid as $newaidvalue){
			if(!in_array($newaidvalue,$oldaid)){
				convertunusedattach($newaidvalue, $tid, 999999);
			}
		}
		DB::query("UPDATE ".DB::table('forum_imgpoll')." SET multiple='$imgpollarray[multiple]', visible='$imgpollarray[visible]', maxchoices='$imgpollarray[maxchoices]', expiration='$imgpollarray[expiration]', overt='$imgpollarray[overt]' WHERE tid='$_G[tid]'", 'UNBUFFERED');
	    } 
	}

	function editpost_submit_end() {
	
	}

	function newreply_submit_end() {
	}
	
	function viewthread($tid,$postappend) {	
	global $_G;
	global $thread;
	global $skipaids;
	global $attachurl,$attachthumb,$attachwidth,$attachfile,$thumbfile;
	global $post,$postlist;
	
	$polloptions = array();
	$votersuid = '';
	

	if($count = DB::fetch_first("SELECT MAX(votes) AS max, SUM(votes) AS total FROM ".DB::table('forum_imgpolloption')." WHERE tid='$_G[tid]'")) {
		$options = DB::fetch_first("SELECT * FROM ".DB::table('forum_imgpoll')." WHERE tid='$_G[tid]'");
		$multiple = $options['multiple'];
		$visible = $options['visible'];
		$maxchoices = $options['maxchoices'];
		$expiration = $options['expiration'];
		$overt = $options['overt'];
		$voterscount = $options['voters'];
		$colors = array('E92725', 'F27B21', 'F2A61F', '5AAF4A', '42C4F5', '0099CC', '3365AE', '2A3591', '592D8E', 'DB3191');
		
		$query = DB::query("SELECT * FROM ".DB::table('forum_imgpolloption')."  WHERE tid='$_G[tid]' ORDER BY votes desc,polloptionid");
		$ci = 0;
		$opts = 1;
		$dirmd5=md5($_G[tid]);
		if(!is_dir("./{$_G['setting']['attachurl']}forum/imgpoll/")) @mkdir("./{$_G['setting']['attachurl']}forum/imgpoll/");
		while($options = DB::fetch($query)) {
			$polloptionid = $options['polloptionid'];
			$viewvoteruid[] = $options['voterids'];
			$voterids .= "\t".$options['voterids'];
			$option = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['polloption']);
			$optiondesc = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['optiondescribe']);	
			
			if($options['aid']) {
				$attach = DB::fetch_first("SELECT * FROM ".DB::table(getattachtablebytid($_G['tid']))." where aid=".$options['aid']);
				
				if($attach['isimage']) {
					$attachurl=$attach['remote']?$_G['setting']['ftp']['attachurl']:$_G['setting']['attachurl'];
					$attachthumb= $attachurl.($attach['thumb'] ? '.thumb.jpg' : '');
					$attachwidth = $attachthumb && $_G['setting']['thumbwidth'] < $attach['width'] ? $_G['setting']['thumbwidth'] : $attach['width'];
					$attachfile=$attachurl."/forum/".$attach['attachment'];
					$filemd5=md5($attach['aid']);
					$thumbdir=$attachurl."forum/imgpoll/{$dirmd5}/";
					if(!is_dir($thumbdir)) @mkdir($thumbdir);
					$thumbfile=$thumbdir."{$filemd5}.jpg";
					if(!file_exists($thumbfile)) $this->dzthumb($attachfile,$thumbfile,220,220,0);
				}else{
					$thumbfile = 0;
				}
				$skipaids[] = $options['aid'];
			}
									
			$voted = in_array(($_G['uid'] ? $_G['uid'] : $_G['clientip']), explode("\t", $options['voterids']));
		
			$polloptions[$opts++] = array
			(
				'polloptionid'	=> $polloptionid,
				'polloption'	=> $option,
				'polloptiondesc'=> $optiondesc,
				'voted'         => $voted,
				'votes'		=> $options['votes'],
				'width'		=> $options['votes'] > 0 ? (@round($options['votes'] * 100 / $count['total'])).'%' : '8px',
				'percent'	=> @sprintf("%01.2f", $options['votes'] * 100 / $count['total']),
				'color'		=> $colors[$ci],
				'aid'           => $options['aid'],
				'attachment'    => $thumbfile,
				'attachurl'     => $attachfile
			);
			$ci++;
			if($ci == count($colors)) {
				$ci = 0;
			}
		}
		
				
		$voterids = explode("\t", $voterids);
		$voters = array_unique($voterids);
		array_shift($voters);

		if(!$expiration) {
			$expirations = TIMESTAMP + 86400;
		} else {
			$expirations = $expiration;
			if($expirations > TIMESTAMP) {
				$_G['forum_thread']['remaintime'] = remaintime($expirations - TIMESTAMP);
			}
		}
	
		$allwvoteusergroup = $_G['group']['allowvote'];
		$allowvotepolled = !in_array(($_G['uid'] ? $_G['uid'] : $_G['clientip']), $voters);
		$allowvotethread = ($_G['forum_thread']['isgroup'] || !$_G['forum_thread']['closed'] && !checkautoclose($_G['forum_thread']) || $_G['group']['alloweditpoll']) && TIMESTAMP < $expirations && $expirations > 0;
	
		$_G['group']['allowvote'] = $allwvoteusergroup && $allowvotepolled && $allowvotethread;

		$optiontype = $multiple ? 'checkbox' : 'radio';
		$visiblepoll = $visible || $_G['forum']['ismoderator'] || ($_G['uid'] && $_G['uid'] == $_G['forum_thread']['authorid']) || ($expirations >= TIMESTAMP && in_array(($_G['uid'] ? $_G['uid'] : $_G['clientip']), $voters)) || $expirations < TIMESTAMP ? 0 : 1;
		
	}		
		include template('imgpoll:viewthread_imgpoll');
		return $return;
	}
	function dzthumb($srcfile,$dstfile,$dstw,$dsth=0,$mode=0,$data=''){
		//mode=0为固定宽高，画质裁切不变形
		//mode=1为固定宽高，画质会拉伸变形
		//mode=2为可变宽高，宽高不超过指定大小
		//mode=3为固定宽度，高度随比例变化
		$data=$data==''?@GetImageSize($srcfile):$data;
		if(!$data) return false;
		if($data[2]==2) $im=@ImageCreateFromJPEG($srcfile);
		elseif ($data[2]==1) $im=@ImageCreateFromGIF($srcfile);
		elseif($data[2]==3) $im=@ImageCreateFromPNG($srcfile);
		list($img_w, $img_h) = $data;
		if($dsth==0) $mode=3;
		if($mode==0){
			$imgratio = $img_w / $img_h;
			$thumbratio = $dstw / $dsth;
			if($imgratio >= 1 && $imgratio >= $thumbratio || $imgratio < 1 && $imgratio > $thumbratio) {
				$cuty = $img_h;
				$cutx = $cuty * $thumbratio;
			} elseif($imgratio >= 1 && $imgratio <= $thumbratio || $imgratio < 1 && $imgratio < $thumbratio) {
				$cutx = $img_w;
				$cuty = $cutx / $thumbratio;
			}
			$cx = $cutx;
			$cy = $cuty;
		}elseif($mode==1){
			$cx = $img_w;
			$cy = $img_h;
		}elseif ($mode==2){
			$cx = $img_w;
			$cy = $img_h;
			$bit=$img_w/$img_h;
			if($dstw/$dsth>$bit){
				$dstw=($img_w/$img_h)*$dsth;
			}else{
				$dsth=($img_h/$img_w)*$dstw;
			}
		}elseif($mode==3){
			$cx = $img_w;
			$cy = $img_h;
			$dsth=$dstw * $img_h / $img_w;
		}
		$ni=imagecreatetruecolor($dstw,$dsth);
		ImageCopyResampled($ni,$im,0,0,0,0,$dstw,$dsth, $cx, $cy);
		clearstatcache();
		if($data[2]==2) ImageJPEG($ni,$dstfile,100);
		elseif($data[2]==1) ImageGif($ni,$dstfile);
		elseif($data[2]==3) ImagePNG($ni,$dstfile);
		return true;
	}	
}


?>

