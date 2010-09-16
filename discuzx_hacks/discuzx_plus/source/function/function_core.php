<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_core.php 650 2010-09-13 07:25:03Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('DISCUZ_CORE_FUNCTION', true);

function error($message, $vars = array(), $return = false) {
	$message = str_replace(array_keys($vars), $vars, lang('error', $message));
	discuz_core::error_log($message);
	if(!$return) {
		global $_G;
		@header('Content-Type: text/html; charset='.$_G['config']['output']['charset']);
		exit($message);
	} else {
		return $message;
	}
}

function checkperm($perm) {
	global $_G;
	return (empty($_G['group'][$perm])?'':$_G['group'][$perm]);
}

function updatesession($force = false) {
	global $_G;
	static $updated = false;
	if(!$updated) {
		$discuz = & discuz_core::instance();
		foreach($discuz->session->var as $k => $v) {
			if(isset($_G['member'][$k]) && $k != 'lastactivity') {
				$discuz->session->set($k, $_G['member'][$k]);
			}
		}

		foreach($_G['action'] as $k => $v) {
			$discuz->session->set($k, $v);
		}

		$discuz->session->update();

		$updated = true;
	}
	return $updated;
}

function dmicrotime() {
	return array_sum(explode(' ', microtime()));
}

function setglobal($key , $value, $group = null) {
	global $_G;
	$k = explode('/', $group === null ? $key : $group.'/'.$key);
	switch (count($k)) {
		case 1: $_G[$k[0]] = $value; break;
		case 2: $_G[$k[0]][$k[1]] = $value; break;
		case 3: $_G[$k[0]][$k[1]][$k[2]] = $value; break;
		case 4: $_G[$k[0]][$k[1]][$k[2]][$k[3]] = $value; break;
		case 5: $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] =$value; break;
	}
	return true;
}

function getglobal($key, $group = null) {
	global $_G;
	$k = explode('/', $group === null ? $key : $group.'/'.$key);
	switch (count($k)) {
		case 1: return isset($_G[$k[0]]) ? $_G[$k[0]] : null; break;
		case 2: return isset($_G[$k[0]][$k[1]]) ? $_G[$k[0]][$k[1]] : null; break;
		case 3: return isset($_G[$k[0]][$k[1]][$k[2]]) ? $_G[$k[0]][$k[1]][$k[2]] : null; break;
		case 4: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]] : null; break;
		case 5: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] : null; break;
	}
	return null;
}

function getgpc($k, $type='GP') {
	$type = strtoupper($type);
	switch($type) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		default:
			if(isset($_GET[$k])) {
				$var = &$_GET;
			} else {
				$var = &$_POST;
			}
			break;
	}

	return isset($var[$k]) ? $var[$k] : NULL;

}

function getuserbyuid($uid) {
	static $users = array();
	if(empty($users[$uid])) {
		$users[$uid] = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='$uid'");
	}
	return $users[$uid];
}

function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : getglobal('authkey'));
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';//note $errstr : $errno \r\n
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

function dexit($message = '') {
	echo $message;
	output();
	exit();
}

function dheader($string, $replace = true, $http_response_code = 0) {
	$string = str_replace(array("\r", "\n"), array('', ''), $string);
	if(empty($http_response_code) || PHP_VERSION < '4.3' ) {
		@header($string, $replace);
	} else {
		@header($string, $replace, $http_response_code);
	}
	if(preg_match('/^\s*location:/is', $string)) {
		exit();
	}
}

function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {

	global $_G;

	$config = $_G['config']['cookie'];

	$_G['cookie'][$var] = $value;
	$var = ($prefix ? $config['cookiepre'] : '').$var;
	$_COOKIE[$var] = $var;

	if($value == '' || $life < 0) {
		$value = '';
		$life = -1;
	}

	$life = $life > 0 ? getglobal('timestamp') + $life : ($life < 0 ? getglobal('timestamp') - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'].'; HttpOnly' : $config['cookiepath'];

	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	if(PHP_VERSION < '5.2.0') {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure);
	} else {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
	}
}

function getcookie($key) {
	global $_G;
	return isset($_G['cookie'][$key]) ? $_G['cookie'][$key] : '';
}

function fileext($filename) {
	return addslashes(trim(substr(strrchr($filename, '.'), 1, 10)));
}

function formhash($specialadd = '') {
	global $_G;
	$hashadd = defined('IN_ADMINCP') ? 'Only For Discuz! Admin Control Panel' : '';
	return substr(md5(substr($_G['timestamp'], 0, -7).$_G['username'].$_G['uid'].$_G['authkey'].$hashadd.$specialadd), 8, 8);
}

function checkrobot($useragent = '') {
	static $kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
	static $kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';

	$useragent = empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent;

	if(!strexists($useragent, 'http://') && preg_match("/($kw_browsers)/i", $useragent)) {
		return false;
	} elseif(preg_match("/($kw_spiders)/i", $useragent)) {
		return true;
	} else {
		return false;
	}
}

function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

function quescrypt($questionid, $answer) {
	return $questionid > 0 && $answer != '' ? substr(md5($answer.md5($questionid)), 16, 8) : '';
}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

function avatar($uid, $size = 'middle', $returnsrc = FALSE, $real = FALSE, $static = FALSE, $ucenterurl = '') {
	global $_G;
	static $staticavatar;
	if($staticavatar === null) {
		$staticavatar = $_G['setting']['avatarmethod'];
	}

	$ucenterurl = empty($ucenterurl) ? $_G['setting']['ucenterurl'] : $ucenterurl;
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	if(!$staticavatar && !$static) {
		return $returnsrc ? $ucenterurl.'/avatar.php?uid='.$uid.'&size='.$size : '<img src="'.$ucenterurl.'/avatar.php?uid='.$uid.'&size='.$size.($real ? '&type=real' : '').'" />';
	} else {
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$file = $ucenterurl.'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).($real ? '_real' : '').'_avatar_'.$size.'.jpg';
		return $returnsrc ? $file : '<img src="'.$file.'" onerror="this.onerror=null;this.src=\''.$ucenterurl.'/images/noavatar_'.$size.'.gif\'" />';
	}
}

function lang($file, $langvar = null, $vars = array()) {
	global $_G;
	@list($path, $file) = explode('/', $file);
	if(!$file) {
		$file = $path;
		$path = '';
	}

	$key = $path == '' ? $file : $path.'_'.$file;
	if(!isset($_G['lang'][$key])) {
		@include DISCUZ_ROOT.'./source/language/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';
		isset($lang) && $_G['lang'][$key] = $lang;
	}
	$returnvalue = &$_G['lang'];
	$return = $langvar !== null ? (!empty($returnvalue[$key][$langvar]) ? $returnvalue[$key][$langvar] : '') : (!empty($returnvalue[$key]) ? $returnvalue[$key] : null);
	$return = $return ? $return : $langvar;
	if($vars && is_array($vars)) {
		$searchs = $replaces = array();
		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
		$return = str_replace($searchs, $replaces, $return);
	}
	return $return;
}

function customlang($langvar = '', $vars = array(), $customlang = array(), $defaultlang = 'poll/message') {
	$ret = '';
	$langvar = (string) $langvar;
	if(!empty($langvar)) {
		$ret = !empty($customlang[$langvar]) ? $customlang[$langvar] : lang($defaultlang, $langvar);
	}
	if($vars && is_array($vars)) {
		$searchs = $replaces = array();
		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
		$ret = str_replace($searchs, $replaces, $ret);
	}
	return $ret;
}

function checktplrefresh($maintpl, $subtpl, $timecompare, $cachefile, $tpldir, $file) {
	static $tplrefresh, $timestamp;
	if($tplrefresh === null) {
		$tplrefresh = getglobal('config/output/tplrefresh');
		$timestamp = getglobal('timestamp');
	}

	if(empty($timecompare) || $tplrefresh == 1 || ($tplrefresh > 1 && !($timestamp % $tplrefresh))) {
		if(empty($timecompare) || @filemtime(DISCUZ_ROOT.$subtpl) > $timecompare) {
			require_once DISCUZ_ROOT.'/source/class/class_template.php';
			$template = new template();
			$template->parse_template($maintpl, $tpldir, $file, $cachefile);
			return TRUE;
		}
	}
	return FALSE;
}

function template($file, $tpldir = '', $gettplfile = 0) {
	global $_G;
	static $_init_style = false;
	if($_init_style === false) {
		$discuz = & discuz_core::instance();
		$discuz->_init_style();
		$_init_style = true;
	}

	if(strpos($file, 'header') === 0 || strpos($file, 'footer') === 0) {
		if(empty($_G['inajax']) && !empty($_G['showmessage']['module']) && !empty($_G['cache']['modulelist'][$_G['showmessage']['module']])) {
			$file = $_G['showmessage']['module'].'/common/'.$file;
		} else {
			$file = 'common/'.$file;
		}
	}

	$file .= !empty($_G['inajax']) && ($file == 'common/header' || $file == 'common/footer') ? '_ajax' : '';
	$tpldir = $tpldir ? $tpldir : (defined('TPLDIR') ? TPLDIR : '');
	$tplfile = ($tpldir ? $tpldir.'/' : './template/').$file.'.htm';
	$filebak = $file;
	$file == 'common/header' && defined('CURMODULE') && CURMODULE && $file = 'common/header_'.CURMODULE;
	$cachefile = './data/template/'.str_replace('/', '_', $file).'.tpl.php';
	if(!file_exists(DISCUZ_ROOT.$tplfile)) {
		$tplfile = './template/default/'.$filebak.'.htm';
	}
	if($gettplfile) {
		return $tplfile;
	}
	checktplrefresh($tplfile, $tplfile, @filemtime($cachefile), $cachefile, $tpldir, $file);
	return $cachefile;
}

function loaducenter() {
	require_once DISCUZ_ROOT.'./config/config_ucenter.php';
	require_once DISCUZ_ROOT.'./uc_client/client.php';
}

function loadcache($cachenames, $force = false) {
	global $_G;
	static $loadedcache = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	$caches = array();
	foreach($cachenames as $k) {
		if(!isset($loadedcache[$k]) || $force) {
			$caches[] = $k;
			$loadedcache[$k] = true;
		}
	}

	if(!empty($caches)) {
		$cachedata = cachedata($caches);
		foreach($cachedata as $cname => $data) {
			if($cname == 'setting') {
				$_G['setting'] = $data;
			} elseif (strexists($cname, 'usergroup_'.$_G['groupid'])) {
				$_G['cache'][$cname] = $_G['group'] = $data;
			} elseif($cname == 'grouplevels') {
				$_G['grouplevels'] = $data;
			} else {
				$_G['cache'][$cname] = $data;
			}
		}
	}
	return true;
}

function cachedata($cachenames) {
	static $isfilecache, $allowmem;

	if($isfilecache === null) {
		$isfilecache = getglobal('config/cache/type') == 'file';
		$allowmem = memory('check');
	}

	$data = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	if($allowmem) {
		$newarray = array();
		foreach($cachenames as $name) {
			$data[$name] = memory('get', $name);
			if($data[$name] === null) {
				$data[$name] = null;
				$newarray[] = $name;
			}
		}
		if(empty($newarray)) {
			return $data;
		} else {
			$cachenames = $newarray;
		}
	}

	if($isfilecache) {
		$lostcaches = array();
		foreach($cachenames as $cachename) {
			if(!@include_once(DISCUZ_ROOT.'./data/cache/cache_'.$cachename.'.php')) {
				$lostcaches[] = $cachename;
			}
		}
		if(!$lostcaches) {
			return $data;
		}
		$cachenames = $lostcaches;
		unset($lostcaches);
	}
	$query = DB::query("SELECT /*!40001 SQL_CACHE */ * FROM ".DB::table('common_syscache')." WHERE cname IN ('".implode("','", $cachenames)."')");
	while($syscache = DB::fetch($query)) {
		$data[$syscache['cname']] = $syscache['ctype'] ? unserialize($syscache['data']) : $syscache['data'];
		$allowmem && (memory('set', $syscache['cname'], $data[$syscache['cname']]));
		if($isfilecache) {
			$cachedata = '$data[\''.$syscache['cname'].'\'] = '.var_export($data[$syscache['cname']], true).";\n\n";
			if($fp = @fopen(DISCUZ_ROOT.'./data/cache/cache_'.$syscache['cname'].'.php', 'wb')) {
				fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!\n//Identify: ".md5($syscache['cname'].$cachedata)."\n\n$cachedata?>");
				fclose($fp);
			}
		}
	}

	foreach ($cachenames as $name) {
		if($data[$name] === null) {
			$data[$name] = null;
			$allowmem && (memory('set', $name, array()));
		}
	}

	return $data;
}

function dgmdate($timestamp, $format = 'dt', $timeoffset = '9999', $uformat = '') {
	global $_G;
	static $dformat, $tformat, $dtformat, $offset, $lang;
	if($dformat === null) {
		$dformat = getglobal('setting/dateformat');
		$tformat = getglobal('setting/timeformat');
		$dtformat = $dformat.' '.$tformat;
		$offset = getglobal('member/timeoffset');
		$lang = lang('core', 'date');
	}
	if($dformat === null) {
		$dformat = 'Y-n-j';
		$tformat = 'H:i';
		$dtformat = $dformat.' '.$tformat;
		$offset = 8;
	}
	$timeoffset = $timeoffset == 9999 ? $offset : $timeoffset;
	$timestamp += $timeoffset * 3600;
	$format = empty($format) || $format == 'dt' ? $dtformat : ($format == 'd' ? $dformat : ($format == 't' ? $tformat : $format));
	if($format == 'u') {
		$todaytimestamp = TIMESTAMP - (TIMESTAMP + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
		$s = gmdate(!$uformat ? $dtformat : $uformat, $timestamp);
		$time = TIMESTAMP + $timeoffset * 3600 - $timestamp;
		if($timestamp >= $todaytimestamp) {
			if($time > 3600) {
				return '<span title="'.$s.'">'.intval($time / 3600).''.$lang['hour'].$lang['before'].'</span>';
			} elseif($time > 1800) {
				return '<span title="'.$s.'">'.$lang['half'].$lang['hour'].$lang['before'].'</span>';
			} elseif($time > 60) {
				return '<span title="'.$s.'">'.intval($time / 60).' '.$lang['min'].$lang['before'].'</span>';
			} elseif($time > 0) {
				return '<span title="'.$s.'">'.$time.' '.$lang['sec'].$lang['before'].'</span>';
			} elseif($time == 0) {
				return '<span title="'.$s.'">'.$lang['now'].'</span>';
			} elseif($time < -3600) {
				return '<span title="'.$s.'">'.intval(abs($time) / 3600).' '.$lang['hour'].$lang['after'].'</span>';
			} elseif($time < -1800) {
				return '<span title="'.$s.'">'.$lang['half'].$lang['hour'].$lang['after'].'</span>';
			} elseif($time < -60) {
				return '<span title="'.$s.'">'.intval(abs($time) / 60).' '.$lang['min'].$lang['after'].'</span>';
			} elseif($time < 0) {
				return '<span title="'.$s.'">'.abs($time).' '.$lang['sec'].$lang['after'].'</span>';
			} else {
				return $s;
			}
		} elseif(($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
			if($days == 0) {
				return '<span title="'.$s.'">'.$lang['yday'].'&nbsp;'.gmdate($tformat, $timestamp).'</span>';
			} elseif($days == 1) {
				return '<span title="'.$s.'">'.$lang['byday'].'&nbsp;'.gmdate($tformat, $timestamp).'</span>';
			} else {
				return '<span title="'.$s.'">'.($days + 1).'&nbsp;'.$lang['day'].$lang['before'].'</span>';
			}
		} else {
			return $s;
		}
	} else {
		return gmdate($format, $timestamp);
	}
}

function dmktime($date) {
	if(strpos($date, '-')) {
		$time = explode('-', $date);
		return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
	}
	return 0;
}

function save_syscache($cachename, $data) {
	static $isfilecache, $allowmem;
	if($isfilecache === null) {
		$isfilecache = getglobal('config/cache/type');
		$allowmem = memory('check');
	}

	if(is_array($data)) {
		$ctype = 1;
		$data = addslashes(serialize($data));
	} else {
		$ctype = 0;
	}

	DB::query("REPLACE INTO ".DB::table('common_syscache')." (cname, ctype, dateline, data) VALUES ('$cachename', '$ctype', '".TIMESTAMP."', '$data')");

	$allowmem && memory('rm', $cachename);
	$isfilecache && @unlink(DISCUZ_ROOT.'./data/cache/cache_'.$cachename.'.php');
}

function dimplode($array) {
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return '';
	}
}

function libfile($libname, $folder = '') {
	$libpath = DISCUZ_ROOT.'/source/'.$folder;
	if(strstr($libname, '/')) {
		list($pre, $name) = explode('/', $libname);
		return realpath("{$libpath}/{$pre}/{$pre}_{$name}.php");
	} else {
		return realpath("{$libpath}/{$libname}.php");
	}
}

function cutstr($string, $length, $dot = ' ...') {
	if(strlen($string) <= $length) {
		return $string;
	}

	$pre = '{%';
	$end = '%}';
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

	$strcut = '';
	if(strtolower(CHARSET) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}

	$strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	return $strcut.$dot;
}
function dstripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

function aidencode($aid) {
	global $_G;
	return rawurlencode(base64_encode($aid.'|'.substr(md5($aid.md5($_G['authkey']).TIMESTAMP.$_G['uid']), 0, 8).'|'.TIMESTAMP.'|'.$_G['uid']));
}

function rewritedata() {
	global $_G;
	$data = array();
	if(!defined('IN_ADMINCP')) {
		if(in_array('all_script', $_G['setting']['rewritestatus'])) {
			$data['search']['all_script'] = "/<a href\=\"()([a-z]+)\.php\?mod=([^\"]+?)\"([^\>]*)?\>/e";
			$data['replace']['all_script'] = "rewriteoutput('all_script', 0, '\\1', '\\2', '\\3', '\\4', '\\5')";
		}
	} else {
		$data['rulesearch']['all_script'] = '{script}-{param}.html';
		$data['rulereplace']['all_script'] = '{script}.php?rewrite={param}';
		$data['rulevars']['all_script']['{script}'] = '([a-z]+)';
		$data['rulevars']['all_script']['{param}'] = '(.+)';
	}
	return $data;
}

function rewriteoutput($type, $returntype, $host) {
	global $_G;
	$host = $host ? 'http://'.$host : '';
	$fextra = '';
	if($type == 'all_script') {
		list(,,, $script, $param, $extra) = func_get_args();
		if(preg_match('/^space&(amp;)?u[^&]+$/', $param)) {
			$extra .= ' c=1';
		}
		if(strexists($extra, 'showWindow') || strexists($extra, 'ajax') || strexists($param, '/') || strexists($param, '%2F') || strexists($param, '-')) {
			return '<a href="'.$script.'.php?mod='.$param.'"'.dstripslashes($extra).'>';
		}
		if(($apos = strrpos($param, '#')) !== FALSE) {
			$fextra = substr($param, $apos);
			$param = substr($param, 0, $apos);
		}
		$param = str_replace('&amp;', '&', $param);
		parse_str($param, $params);
		$param = $comma = '';
		$i = 0;
		foreach($params as $k => $v) {
			if($i) {
				$param .= $comma.$k.'-'.rawurlencode($v);
				$comma = '-';
			} else {
				$param .= $k.'-';$i++;
			}
		}
		$r = array(
			'{script}' => $script,
			'{param}' => substr($param, -1) != '-' ? $param : substr($param, 0, strlen($param) -1),
		);
	} elseif($type == 'site_default') {
		list(,,, $url) = func_get_args();
		if(!preg_match('/^\w+\.php/i', $url) || preg_match('/^member\.php/i', $url)) {
			$host = '';
		}
		if(!$returntype) {
			return '<a href="'.$host.$url.'"';
		} else {
			return $host.$url;
		}
	}
	$href = str_replace(array_keys($r), $r, $_G['setting']['rewriterule'][$type]).$fextra;
	if(!$returntype) {
		return '<a href="'.$host.$href.'"'.dstripslashes($extra).'>';
	} else {
		return $host.$href;
	}
}

function output() {

	global $_G;
	if(defined('DISCUZ_OUTPUTED')) {
		return;
	} else {
		define('DISCUZ_OUTPUTED', 1);
	}

	if(!empty($_G['blockupdate'])) {
		block_updatecache($_G['blockupdate']['bid']);
	}

	$_G['domain'] = array();
	$port = empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':'.$_SERVER['SERVER_PORT'];
	foreach($_G['config']['app']['domain'] as $app => $domain) {
		if($domain || $_G['config']['app']['domain']['default']) {
			$domain = empty($domain) ? $_G['config']['app']['domain']['default'] : $domain;
			$_G['domain']['search'][$app] = "<a href=\"{$app}.php";
			$_G['domain']['replace'][$app] = '<a href="http://'.$domain.$port.$_G['siteroot'];
			$_G['domain']['pregxprw'][$app] = '<a href\="http\:\/\/('.preg_quote($domain.$port.$_G['siteroot'], '/').')';
		} else {
			$_G['domain']['pregxprw'][$app] = "<a href\=\"(){$app}.php";
		}
	}

	if(defined('DISCUZ_DEBUG') && DISCUZ_DEBUG && @include(libfile('function/debug'))) {
		function_exists('debugmessage') && debugmessage();
	}
}

function debug($var = null) {
	echo '<pre>';
	if($var === null) {
		print_r($GLOBALS);
	} else {
		print_r($var);
	}
	exit();
}

function debuginfo() {
	global $_G;
	if(getglobal('setting/debug')) {
		$db = & DB::object();
		$_G['debuginfo'] = array('time' => number_format((dmicrotime() - $_G['starttime']), 6), 'queries' => $db->querynum, 'memory' => ucwords($_G['memory']));
		return TRUE;
	} else {
		return FALSE;
	}
}

function check_seccode($value, $idhash) {
	global $_G;
	if(!$_G['setting']['seccodestatus']) {
		return true;
	}
	if(!isset($_G['cookie']['seccode'.$idhash])) {
		return false;
	}
	list($checkvalue, $checktime, $checkidhash, $checksid, $checkformhash) = explode("\t", authcode($_G['cookie']['seccode'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
	return $checkvalue == strtoupper($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && $checksid == $_G['sid'] && FORMHASH == $checkformhash;
}

function check_secqaa($value, $idhash) {
	global $_G;
	if(!$_G['setting']['secqaa']) {
		return true;
	}
	if(!isset($_G['cookie']['secqaa'.$idhash])) {
		return false;
	}
	loadcache('secqaa');
	list($checkvalue, $checktime, $checkidhash, $checksid, $checkformhash) = explode("\t", authcode($_G['cookie']['secqaa'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
	return $checkvalue == md5($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && $checksid == $_G['sid'] && FORMHASH == $checkformhash;
}

function showmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
	global $_G;

	$param = array(
		'header'	=> false,
		'timeout'	=> null,
		'refreshtime'	=> null,
		'closetime'	=> null,
		'locationtime'	=> null,
		'alert'		=> null,
		'return'	=> false,
		'redirectmsg'	=> 0,
		'msgtype'	=> 1,
		'showmsg'	=> true,
		'showdialog'	=> false,
		'login'		=> false,
		'handle'	=> false,
		'extrajs'	=> '',
	);

	if($custom) {
		$alerttype = 'alert_info';
		$show_message = $message;
		include template('common/showmessage');
		dexit();
	}

	define('CACHE_FORBIDDEN', TRUE);
	$_G['setting']['msgforward'] = @unserialize($_G['setting']['msgforward']);
	$handlekey = '';

	if(empty($_G['inajax']) && (!empty($_G['gp_quickforward']) || $_G['setting']['msgforward']['quick'] && $_G['setting']['msgforward']['messages'] && @in_array($message, $_G['setting']['msgforward']['messages']))) {
		$param['header'] = true;
	}
	if(!empty($_G['inajax'])) {
		$handlekey = $_G['gp_handlekey'] = !empty($_G['gp_handlekey']) ? htmlspecialchars($_G['gp_handlekey']) : '';
		$param['handle'] = true;
	}
	if(!empty($_G['inajax'])) {
		$param['msgtype'] = empty($_G['gp_ajaxmenu']) && (empty($_POST) || !empty($_G['gp_nopost'])) ? 2 : 3;
	}
	if($url_forward) {
		$param['timeout'] = true;
		if($param['handle'] && !empty($_G['inajax'])) {
			$param['showmsg'] = false;
		}
	}

	foreach($extraparam as $k => $v) {
		$param[$k] = $v;
	}
	if(array_key_exists('set', $extraparam)) {
		$setdata = array('1' => array('msgtype' => 3));
		if($setdata[$extraparam['set']]) {
			foreach($setdata[$extraparam['set']] as $k => $v) {
				$param[$k] = $v;
			}
		}
	}

	if($param['timeout'] !== null) {
		$refreshtime = intval($param['refreshtime'] === null ? $_G['setting']['msgforward']['refreshtime'] : $param['refreshtime']);
		$refreshsecond = !empty($refreshtime) ? $refreshtime : 3;
		$refreshtime = $refreshsecond * 1000;
	} else {
		$refreshtime = $refreshsecond = 0;
	}

	if($param['login'] && $_G['uid'] || $url_forward) {
		$param['login'] = false;
	}

	$param['header'] = $url_forward && $param['header'] ? true : false;

	if($param['header']) {
		header("HTTP/1.1 301 Moved Permanently");
		dheader("location: ".str_replace('&amp;', '&', $url_forward));
	}

	$module = $_G['showmessage']['module'] != 'common' ? $_G['showmessage']['module'].'/message' : 'message';
	$show_message = lang($module, $message, $values);
	if($module!='message' && $show_message == $message) {
		$show_message = lang('message', $message, $values);
	}

	$show_jsmessage = str_replace("'", "\\\'", strip_tags($show_message));
	if(!$param['showmsg']) {
		$show_message = '';
	}

	if($param['msgtype'] == 3) {
		$show_message = str_replace(lang('message', 'return_search'), lang('message', 'return_replace'), $show_message);
	}

	$allowreturn = !$param['timeout'] && stristr($show_message, lang('message', 'return')) || $param['return'] ? true : false;
	if($param['alert'] === null) {
		$alerttype = $url_forward ? (preg_match('/\_(succeed|success)$/', $message) ? 'alert_right' : 'alert_info') : ($allowreturn ? 'alert_error' : 'alert_info');
	} else {
		$alerttype = 'alert_'.$param['alert'];
	}

	$extra = '';
	if($param['handle']) {
		$valuesjs = $comma = $subjs = '';
		foreach($values as $k => $v) {
			if(is_array($v)) {
				$subcomma = '';
				foreach($v as $subk => $subv) {
					$subjs .= $subcomma.'\''.$subk.'\':\''.$subv.'\'';
					$subcomma = ',';
				}
				$valuesjs .= $comma.'\''.$k.'\':{'.$subjs.'}';
			} else {
				$valuesjs .= $comma.'\''.$k.'\':\''.$v.'\'';
			}
			$comma = ',';
		}
		$valuesjs = '{'.$valuesjs.'}';
		if($url_forward) {
			$extra .= 'if($(\'return_'.$handlekey.'\')) $(\'return_'.$handlekey.'\').className=\'onright\';if(typeof succeedhandle_'.$handlekey.'==\'function\') {succeedhandle_'.$handlekey.'(\''.$url_forward.'\', \''.$show_jsmessage.'\', '.$valuesjs.');}';
		} else {
			$extra .= 'if(typeof errorhandle_'.$handlekey.'==\'function\') {errorhandle_'.$handlekey.'(\''.$show_jsmessage.'\', '.$valuesjs.');}';
		}
	}
	if($handlekey) {
		if($param['showdialog']) {
			$st = $param['closetime'] !== null ? 'setTimeout("hideMenu(\'fwin_dialog\', \'dialog\')", '.($param['closetime'] * 1000).');' : '';
			$st .= $param['locationtime'] !== null ?'setTimeout("window.location.href =\''.$url_forward.'\';", '.($param['locationtime'] * 1000).');' : '';
			$extra .= 'hideWindow(\''.$handlekey.'\');showDialog(\''.$show_jsmessage.'\', \'notice\', null, '.($param['locationtime'] !== null ? 'function () { window.location.href =\''.$url_forward.'\'; }' : 'null').', 0);'.$st.'';
			$param['closetime'] = null;
		}
		if($param['closetime'] !== null) {
			$extra .= 'setTimeout("hideWindow(\''.$handlekey.'\')", '.($param['closetime'] * 1000).');';
		}
	}
	if(!$extra && $param['timeout']) {
		$extra .= 'setTimeout("window.location.href =\''.$url_forward.'\';", '.$refreshtime.');';
	}
	$show_message .= $extra ? '<script type="text/javascript" reload="1">'.$extra.'</script>' : '';
	$show_message .= $param['extrajs'] ? $param['extrajs'] : '';

	include template('common/showmessage');

	dexit();
}

function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
	if(!getgpc($var)) {
		return FALSE;
	} else {
		global $_G;
		if($allowget || ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_G['gp_formhash']) && $_G['gp_formhash'] == formhash() && empty($_SERVER['HTTP_X_FLASH_VERSION']) && (empty($_SERVER['HTTP_REFERER']) ||
		preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
			if(checkperm('seccode')) {
				if($secqaacheck && !check_secqaa($_G['gp_secanswer'], $_G['gp_sechash'])) {
					showmessage('submit_secqaa_invalid');
				}
				if($seccodecheck && !check_seccode($_G['gp_seccodeverify'], $_G['gp_sechash'])) {
					showmessage('submit_seccode_invalid');
				}
			}
			return TRUE;
		} else {
			showmessage('submit_invalid');
		}
	}
}

function multi($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $autogoto = FALSE, $simple = FALSE) {
	global $_G;
	$ajaxtarget = !empty($_G['gp_ajaxtarget']) ? " ajaxtarget=\"".htmlspecialchars($_G['gp_ajaxtarget'])."\" " : '';

	$a_name = '';
	if(strpos($mpurl, '#')) {
		$a_strs = explode('#', $mpurl);
		$mpurl = $a_strs[0];
		$a_name = '#'.$a_strs[1];
	}

	if(defined('IN_ADMINCP')) {
		$shownum = $showkbd = TRUE;
		$lang['prev'] = '&lsaquo;&lsaquo;';
		$lang['next'] = '&rsaquo;&rsaquo;';
	} else {
		$shownum = $showkbd = FALSE;
		$lang['prev'] = lang('core', 'prevpage');
		$lang['next'] = lang('core', 'nextpage');
	}

	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';

	$realpages = 1;
	$_G['page_next'] = 0;
	if($num > $perpage) {

		$offset = floor($page * 0.5);

		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}
		$_G['page_next'] = $to;

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1'.$a_name.'" class="first"'.$ajaxtarget.'>1 ...</a>' : '').
		($curpage > 1 && !$simple ? '<a href="'.$mpurl.'page='.($curpage - 1).$a_name.'" class="prev"'.$ajaxtarget.'>'.$lang['prev'].'</a>' : '');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<strong>'.$i.'</strong>' :
			'<a href="'.$mpurl.'page='.$i.($ajaxtarget && $i == $pages && $autogoto ? '#' : $a_name).'"'.$ajaxtarget.'>'.$i.'</a>';
		}

		$multipage .= ($to < $pages ? '<a href="'.$mpurl.'page='.$pages.$a_name.'" class="last"'.$ajaxtarget.'>... '.$realpages.'</a>' : '').
		($curpage < $pages && !$simple ? '<a href="'.$mpurl.'page='.($curpage + 1).$a_name.'" class="nxt"'.$ajaxtarget.'>'.$lang['next'].'</a>' : '').
		($showkbd && !$simple && $pages > $page && !$ajaxtarget ? '<kbd><input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\''.$mpurl.'page=\'+this.value; doane(event);}" /></kbd>' : '');

		$multipage = $multipage ? '<div class="pg">'.($shownum && !$simple ? '<em>&nbsp;'.$num.'&nbsp;</em>' : '').$multipage.'</div>' : '';
	}
	$maxpage = $realpages;
	return $multipage;
}

function simplepage($num, $perpage, $curpage, $mpurl) {
	$return = '';
	$lang['next'] = lang('core', 'nextpage');
	$lang['prev'] = lang('core', 'prevpage');
	$next = $num == $perpage ? '<a class="nxt" href="'.$mpurl.'&amp;page='.($curpage + 1).'">'.$lang['next'].'</a>' : '';
	$prev = $curpage > 1 ? '<span class="pgb"><a href="'.$mpurl.'&amp;page='.($curpage - 1).'">'.$lang['prev'].'</a></span>' : '';
	if($next || $prev) {
		$return = '<div class="pg">'.$prev.$next.'</div>';
	}
	return $return;
}

function censor($message, $modword = NULL) {
	require_once libfile('class/censor');
	$censor = discuz_censor::instance();
	$censor->check($message, $modword);
	if($censor->modbanned()) {
		$wordbanned = implode(', ', $censor->words_found);
		showmessage('word_banned', '', array('wordbanned' => $wordbanned));
	}
	return $message;
}

function runlog($file, $message, $halt=0) {
	global $_G;

	$nowurl = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$log = dgmdate($_G['timestamp'], 'Y-m-d H:i:s')."\t".$_G['clientip']."\t$_G[uid]\t{$nowurl}\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($message))."\n";
	$yearmonth = dgmdate($_G['timestamp'], 'Ym');
	$logdir = DISCUZ_ROOT.'./data/log/';
	if(!is_dir($logdir)) mkdir($logdir, 0777);
	$logfile = $logdir.$yearmonth.'_'.$file.'.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);
		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>', "\r", "\n"), '', $log)."\n");
		fclose($fp);
	}
	if($halt) exit();
}

function dmkdir($dir, $mode = 0777){
	if(!is_dir($dir)) {
		dmkdir(dirname($dir));
		@mkdir($dir, $mode);
		@touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);
	}
	return true;
}

function dreferer($default = '') {
	global $_G;

	$default = empty($default) ? $GLOBALS['_t_curapp'] : '';
	if(empty($_G['referer'])) {
		$referer = !empty($_G['gp_referer']) ? $_G['gp_referer'] : $_SERVER['HTTP_REFERER'];
		$_G['referer'] = preg_replace("/([\?&])((sid\=[a-z0-9]{6})(&|$))/i", '\\1', $referer);
		$_G['referer'] = substr($_G['referer'], -1) == '?' ? substr($_G['referer'], 0, -1) : $_G['referer'];
	} else {
		$_G['referer'] = htmlspecialchars($_G['referer']);
	}

	if(strpos($_G['referer'], 'member.php?mod=logging')) {
		$_G['referer'] = $default;
	}
	return strip_tags($_G['referer']);
}

function upload_images($file, $type, $thumbwidth = 176, $thumbheight = 176) {
	global $_G;

	require_once libfile('class/upload');
	$upload = new discuz_upload();

	if(!$upload->init($file, $type)) {
		return false;
	}

	if(!$upload->save()) {
		if(!defined('IN_ADMINCP')) {
			showmessage($upload->errormessage());
		} else {
			cpmsg($upload->errormessage(), '', 'error');
		}
	}

	if(!empty($_G['cache']['modulelist'][$type]['mid'])) {
		$mid = $_G['cache']['modulelist'][$type]['mid'];
	} else {
		$_G['cache']['modulelist'][$type]['mid'] = $mid = DB::result_first("SELECT mid FROM ".DB::table('common_module')." WHERE identifier='$type' AND available='1'");
	}

	$isimage = $upload->attach['isimage'] ? 1 : 0;
	$data = array(
		'mid' => $mid,
		'authorid' => $_G['uid'],
		'filesize' => $upload->attach['size'],
		'type' => $isimage,
		'filename' => $upload->attach['name'],
		'url' => $upload->attach['attachment'],
	);
	$return = $upload->attach;
	DB::insert('common_attachment', $data, true);
	$return['aid'] = DB::insert_id();

	if(!$return['aid']) {
		if(!defined('IN_ADMINCP')) {
			showmessage('common_attachment_error');
		} else {
			cpmsg('common_attachment_error', '', 'error');
		}
	}

	require_once libfile('class/image');
	$image = new image;
	$source = $_G['setting']['attachdir'].$type.'/'.$upload->attach['attachment'];

	$setting_thumbwidth = $thumbwidth > 10 && $thumbwidth < 1000 ? intval($thumbwidth) : 176;
	$setting_thumbheight = $thumbheight > 10 && $thumbheight < 1000 ? intval($thumbheight) : 176;

	$isthumb = $image->Thumb($source, '', $setting_thumbwidth, $setting_thumbheight);
	if(!$isthumb) {
		$thumbtarget = $_G['setting']['attachdir'].$type.'/'.$upload->attach['attachment'].'.thumb.jpg';
		$isthumb = @copy($source, $thumbtarget);
	}

	if(!$isthumb) {
		if(!defined('IN_ADMINCP')) {
			showmessage('thumb_error');
		} else {
			cpmsg('thumb_error', '', 'error');
		}
	}

	return $return;
}

function delete_images($aid) {
	global $_G;
	$condition = '';
	if(is_array($aid)) {
		$where = array();
		foreach($aid as $id) {
			$id = intval($id);
			if($id > 0) {
				$where[] = $id;
			}
		}
		$condition = 'aid IN ('.implode(',', $where).')';
	} else {
		$aid = intval($aid);
		if($aid > 0) {
			$condition = "aid='$aid'";
		}
	}
	if($condition) {
		$query = DB::query('SELECT mid, url FROM '.DB::table('common_attachment').' WHERE '.$condition);
		while($attach = DB::fetch($query)) {
			if(!empty($attach['url'])) {
				$type = '';
				if(!empty($attach['mid'])) {
					foreach($_G['cache']['modulelist'] as $key=>$value) {
						if($value['mid'] == $attach['mid']) {
							$type = $key;
							break;
						}
					}
					if(empty($type)) {
						$type = DB::result_first("SELECT identifier FROM ".DB::table('common_module')." WHERE mid='{$attach['mid']}' AND available='1'");
						$_G['cache']['modulelist'][$type]['mid'] = $attach['mid'];
					}
				}
				$source = $_G['setting']['attachdir'].($type ? $type.'/' : '').$attach['url'];
				$thumb = $source.'.thumb.jpg';
				@unlink($source);
				@unlink($thumb);
			}
		}
		DB::delete('common_attachment', $condition);
	}
}

function diconv($str, $in_charset, $out_charset = CHARSET, $ForceTable = FALSE) {
	global $_G;

	$in_charset = strtoupper($in_charset);
	$out_charset = strtoupper($out_charset);
	if($in_charset != $out_charset) {
		require_once libfile('class/chinese');
		$chinese = new Chinese($in_charset, $out_charset, $ForceTable);
		return $chinese->Convert($str);
	} else {
		return $str;
	}
}

function renum($array) {
	$newnums = $nums = array();
	foreach($array as $id => $num) {
		$newnums[$num][] = $id;
		$nums[$num] = $num;
	}
	return array($nums, $newnums);
}

function sizecount($size) {
	if($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . ' GB';
	} elseif($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . ' MB';
	} elseif($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . ' KB';
	} else {
		$size = $size . ' Bytes';
	}
	return $size;
}

function swapclass($class1, $class2 = '') {
	static $swapc = null;
	$swapc = isset($swapc) && $swapc != $class1 ? $class1 : $class2;
	return $swapc;
}

function writelog($file, $log) {
	global $_G;
	$_G['setting']['timeoffset'] = !empty($_G['setting']['timeoffset']) ? intval($_G['setting']['timeoffset']) : 8;
	$yearmonth = dgmdate(TIMESTAMP, 'Ym', $_G['setting']['timeoffset']);
	$logdir = DISCUZ_ROOT.'./data/log/';
	$logfile = $logdir.$yearmonth.'_'.$file.'.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);

		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		$log = is_array($log) ? $log : array($log);
		foreach($log as $tmp) {
			fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
		}
		fclose($fp);
	}
}

function getcolorpalette($colorid, $id, $background) {
	return "<input id=\"c$colorid\" onclick=\"c{$colorid}_frame.location='static/image/admincp/getcolor.htm?c{$colorid}|{$id}';showMenu({'ctrlid':'c$colorid'})\" type=\"button\" class=\"colorwd\" value=\"\" style=\"background: $background\"><span id=\"c{$colorid}_menu\" style=\"display: none\"><iframe name=\"c{$colorid}_frame\" src=\"\" frameborder=\"0\" width=\"166\" height=\"186\" scrolling=\"no\"></iframe></span>";
}

function loadcacheobject($type) {
	static $object = null;

	global $_G;
	return $_G['ea'];
	$cachetype = in_array($type, array('file', 'memcache', 'sql')) ? $type : $_G['config']['cache']['type'];
	if($type && $type != $cachetype) {
		return;
	}
	if($cachetype == 'memcache') {
		$cachetype = $_G['config']['cache']['main']['type'];
	}
	if(!$cachetype) {
		return;
	}
	if(!isset($object[$cachetype])) {
		require_once libfile('cache/'.$cachetype, 'class');
		$object[$cachetype] = new ultrax_cache($_G['config']['cache']['main'][$cachetype]);
	}
	return $object[$cachetype];
}

function memory($cmd, $key='', $value='', $ttl = 0) {
	$discuz = & discuz_core::instance();
	if($cmd == 'check') {
		return  $discuz->mem->enable ? $discuz->mem->type : '';
	} elseif($discuz->mem->enable && in_array($cmd, array('set', 'get', 'rm'))) {
		switch ($cmd) {
			case 'set': return $discuz->mem->set($key, $value, $ttl); break;
			case 'get': return $discuz->mem->get($key); break;
			case 'rm': return $discuz->mem->rm($key); break;
		}
	}
	return null;
}

function ipaccess($ip, $accesslist) {
	return preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($accesslist, '/')).")/", $ip);
}

function getcount($tablename, $condition) {
	if(empty($condition)) {
		$where = '1';
	} elseif(is_array($condition)) {
		$where = DB::implode_field_value($condition, ' AND ');
	} else {
		$where = $condition;
	}
	$row = DB::fetch_first("SELECT COUNT(*) AS num FROM ".DB::table($tablename)." WHERE $where");
	return $row['num'];
}

function sysmessage($message) {
	require libfile('function/sysmessage');
	show_system_message($message);
}

if(!function_exists('file_put_contents')) {
	if(!defined('FILE_APPEND')) define('FILE_APPEND', 8);
	function file_put_contents($filename, $data, $flag = 0) {
		$return = false;
		if($fp = @fopen($filename, $flag != FILE_APPEND ? 'w' : 'a')) {
			if($flag == LOCK_EX) @flock($fp, LOCK_EX);
			$return = fwrite($fp, is_array($data) ? implode('', $data) : $data);
			fclose($fp);
		}
		return $return;
	}
}

function periodscheck($periods, $showmessage = 1) {
	global $_G;

	if(empty($_G['group']['disableperiodctrl']) && !empty($_G['setting'][$periods])) {
		$now = dgmdate(TIMESTAMP, 'G.i');
		foreach(explode("\r\n", str_replace(':', '.', $_G['setting'][$periods])) as $period) {
			list($periodbegin, $periodend) = explode('-', $period);
			if(($periodbegin > $periodend && ($now >= $periodbegin || $now < $periodend)) || ($periodbegin < $periodend && $now >= $periodbegin && $now < $periodend)) {
				$banperiods = str_replace("\r\n", ', ', $_G['setting'][$periods]);
				if($showmessage) {
					showmessage('period_nopermission', NULL, array('banperiods' => $banperiods), array('login' => 1));
				} else {
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}

function updatecredit($uid, $module, $num, $action = '') {

}

function getcredit($uid, $module) {

}

?>