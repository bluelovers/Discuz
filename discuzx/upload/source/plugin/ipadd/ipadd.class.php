<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_ipadd {
	function global_header() {		
		return;
	}
}

class plugin_ipadd_forum extends plugin_ipadd {
	function index_top() {	
		global $_G;	
		if(!$_G['uid']) {
			return;
		}	
		$return = '';
		$ip = '';
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		preg_match("/((25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)\.){3}(25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|[1-9])/", $ip, $ipp);
		$ip = $ipp[0];
		
		$query = DB::query("SELECT lastip FROM ".DB::table('common_member_lastip')." WHERE uid = '{$_G['uid']}'");
		DB::query("REPLACE INTO ".DB::table('common_member_lastip')." (`uid`, `lastip`) VALUES ('{$_G['uid']}', '{$ip}')");
		while($data = DB::fetch($query)) {
			$lastip[] = $data['lastip'];
		}			
		$ipdatafile = DISCUZ_ROOT.'./source/plugin/ipadd/ipdata/qqwry.dat';
		if(!file_exists($ipdatafile)) {
			$ipdatafile = DISCUZ_ROOT.'./data/ipdata/tinyipdata.dat';
		}
		include DISCUZ_ROOT.'./source/function/function_misc.php';
		$ipnow = substr(convertip_full($ip, $ipdatafile), 0, 11);	
		$iplast = substr(convertip_full($lastip[0], $ipdatafile), 0, 11);
		$iplast_full = convertip_full($lastip[0], $ipdatafile);
		$address = $_G['cache']['plugin']['ipadd']['address'];	
		if($address) {
				$return .= '当前IP:'.$ip.'&nbsp;'.convertip_full($ip, $ipdatafile);
		} else {
				$return .= '当前IP:'.$ip.'&nbsp;';
		}
		if($iplast != $ipnow && $lastip[0] != NULL ) {	
			$message = "<font color=\"red\"> 您上次访问的IP：{$lastip[0]}{$iplast_full}，异地登录成功</font>";
			showmessage("$message", dreferer());
		} 
		include template('ipadd:index');
		return $return;
	}
}
?>


