<?php

require_once './source/class/class_core.php';
require_once './fblib/facebook.php';
//if(!function_exists('sendmail')) {
//	include libfile('function/mail');
//}
$discuz = & discuz_core::instance();

$cachelist = array('plugin');
$discuz->cachelist = $cachelist;
$discuz->init();
runhooks();

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$actarray = array('logging','login','userset','adm');
$act = !in_array($_G['gp_act'], $actarray) ? null : $_G['gp_act'];
if(!$act) {
	showmessage(do_iconv('未定義操作，請返回。'), 'index.php');
}

$fb_cf = $_G['cache']['plugin']['facebook_connect'];
if(!$fb_cf['appid'] || !$fb_cf['secret']) {
	showmessage(do_iconv('請先到後台設置Facebook Connect插件，輸入正確的FB應用程式ID與密鑰。'), null, array(), array('alert'=>'error'));
}
$facebook = new Facebook(array('appId'=>$fb_cf['appid'], 'secret'=>$fb_cf['secret']));

$rf = !$_G['gp_rf'] ? 'index.php' : urlencode(str_ireplace($_G[siteurl], '', $_G['gp_rf']));

if($act == 'logging') {
	if($_G['uid']) {
		showmessage(do_iconv('您已經登入！'), 'index.php', array(), array('alert'=>'info'));
	} else {
		header('Location:'.$facebook->getLoginUrl(array('scope'=>'email,user_likes,publish_stream', 'redirect_uri'=>$_G[siteurl].'fblogin.php?act=login&rf='.$rf)));
	}
} elseif($act == 'login' && $_G['gp_code']) {
	if($_G['uid']) {
		showmessage(do_iconv('您已經登入！'), 'index.php', array(), array('alert'=>'info'));
	} else {
		try {
			$fb_me = $facebook->api('/me');
			$fql = "SELECT pic_small,pic,pic_big FROM user WHERE uid='".$fb_me[id]."'";
			$fparam = array('method'=>'fql.query', 'query'=>$fql, 'callback'=>'');
			$fb_pic = $facebook->api($fparam);
		} catch (FacebookApiException $e) {
			header('Location:'.$facebook->getLoginUrl(array('scope'=>'email,user_likes,publish_stream', 'redirect_uri'=>$_G[siteurl].'fblogin.php?act=login&rf='.$rf)));
		}
	}
	if($fb_me['id'] && $fb_me['name'] && $fb_me['email']) {
		require_once libfile('function/misc');
		require_once libfile('function/member');
		include_once libfile('function/stat');
		loaducenter();
		$fcuid = DB::fetch_first("SELECT uid FROM ".DB::table('facebook_connect')." WHERE fbid='".$fb_me['id']."'");
		$cmuid = DB::fetch_first("SELECT uid FROM ".DB::table('common_member')." WHERE email='".$fb_me['email']."'");
		$NOWURL = 'http://'.$_SERVER['HTTP_HOST'].request_uri();
		$NOWURL = str_ireplace("&z=nuser", "", $NOWURL);
		if($fcuid['uid'] || $cmuid['uid']) {
			$gfbu = $fcuid['uid'] ? $fcuid['uid'] : $cmuid['uid'];
			$fbu = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='".$gfbu."'");
			setloginstatus($fbu, $_G['gp_cookietime'] ? 2592000 : 0);
			DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."' WHERE uid='".$_G['uid']."'");
			updatestat('login', 1);
			updatecreditbyaction('daylogin', $_G['uid']);
			checkusergroup($_G['uid']);
			if($cmuid['uid'] && !$fcuid['uid']) {
				$fclt = DB::fetch_first("SELECT liketid FROM ".DB::table('facebook_connect')." WHERE uid='".$_G['uid']."'");
				if($fclt['liketid']) {
					DB::query("UPDATE ".DB::table('facebook_connect')." SET fbid='".$fb_me['id']."' WHERE uid='".$_G['uid']."'");
				} else {
					DB::query("REPLACE ".DB::table('facebook_connect')." SET uid='".$_G['uid']."', fbid='".$fb_me['id']."'");
				}
			}
			showmessage(do_iconv('您透過Facebook帳戶登入成功<br><br>歡迎回訪：').$_G['member']['username'], urldecode($rf), array(), array('alert'=>'right'));
		} else {
			if(!$_G['setting']['regstatus'] || $_G['setting']['bbclosed']) {
				showmessage(!$_G['setting']['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $_G['setting']['regclosemessage']));
				exit;
			}
			include './source/class/class_fblnuser.php';
		}
	}
} elseif($act == 'userset') {
	include './source/class/class_fbluserset.php';
} elseif($act == 'adm') {
	include './source/class/class_fbladm.php';
} else {
	showmessage(do_iconv('未定義操作，請返回。'), 'index.php');
}

function do_iconv($str) {
	return iconv("big5","utf-8",$str);
}

function request_uri() {
	if (isset($_SERVER['REQUEST_URI'])) {
		$uri = $_SERVER['REQUEST_URI'];
	} else {
		if (isset($_SERVER['argv'])) {
			$uri = htmlentities($_SERVER['PHP_SELF']) .'?'. $_SERVER['argv'][0];
		} else {
			$uri = htmlentities($_SERVER['PHP_SELF']) .'?'. $_SERVER['QUERY_STRING'];
		}
	}
	return $uri;
}

function setatr($fb_pic, $uid, $ucdir='') {	
	$ib = file_get_contents($fb_pic['pic_big']);
	$im = file_get_contents($fb_pic['pic']);
	$is = file_get_contents($fb_pic['pic_small']);

	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);

	if($ucdir == '') {
		$ucdir = './uc_server';
	}

	if(!file_exists($ucdir.'/data/avatar/'.$dir1.'/')) {
		mkdir($ucdir.'/data/avatar/'.$dir1.'/');
	}
	if(!file_exists($ucdir.'/data/avatar/'.$dir1.'/'.$dir2.'/')) {
		mkdir($ucdir.'/data/avatar/'.$dir1.'/'.$dir2.'/');
	}
	if(!file_exists($ucdir.'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/')) {
		if(mkdir($ucdir.'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/')) {
			$ipath = $ucdir.'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2);
			fclose(fwrite(fopen($ipath."_avatar_big.jpg", "wr"), $ib));
			fclose(fwrite(fopen($ipath."_avatar_middle.jpg", "wr"), $im));
			fclose(fwrite(fopen($ipath."_avatar_small.jpg", "wr"), $is));
		}	
	} else {
		$ipath = $ucdir.'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2);
		fclose(fwrite(fopen($ipath."_avatar_big.jpg", "wr"), $ib));
		fclose(fwrite(fopen($ipath."_avatar_middle.jpg", "wr"), $im));
		fclose(fwrite(fopen($ipath."_avatar_small.jpg", "wr"), $is));
	}

	if(file_exists($ipath."_avatar_small.jpg")) {
		return true;
	} else {
		return false;
	}
}
?>