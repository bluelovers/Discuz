<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_core.php 17218 2010-09-27 00:00:29Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('DISCUZ_CORE_FUNCTION', true);

function system_error($message, $show = true, $save = true, $halt = true) {
	require_once libfile('class/error');
	discuz_error::system_error($message, $show, $save, $halt);
}

function updatesession($force = false) {

	global $_G;
	static $updated = false;
	if(!$updated) {
		$discuz = & discuz_core::instance();
		$oltimespan = $_G['setting']['oltimespan'];
		$lastolupdate = $discuz->session->var['lastolupdate'];
		if($_G['uid'] && $oltimespan && TIMESTAMP - ($lastolupdate ? $lastolupdate : $_G['member']['lastactivity']) > $oltimespan * 60) {
			DB::query("UPDATE ".DB::table('common_onlinetime')."
				SET total=total+'$oltimespan', thismonth=thismonth+'$oltimespan', lastupdate='" . TIMESTAMP . "'
				WHERE uid='{$_G['uid']}'");
			if(!DB::affected_rows()) {
				DB::insert('common_onlinetime', array(
					'uid' => $_G['uid'],
					'thismonth' => $oltimespan,
					'total' => $oltimespan,
					'lastupdate' => TIMESTAMP,
				));
			}
			$discuz->session->set('lastolupdate', TIMESTAMP);
		}
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

		if($_G['uid'] && TIMESTAMP - $_G['member']['lastactivity'] > 21600) {
			if($oltimespan && TIMESTAMP - $_G['member']['lastactivity'] > 43200) {
				$total = DB::result_first("SELECT total FROM ".DB::table('common_onlinetime')." WHERE uid='$_G[uid]'");
				DB::update('common_member_count', array('oltime' => round(intval($total) / 60)), "uid='$_G[uid]'", 1);
			}
			DB::update('common_member_status', array('lastip' => $_G['clientip'], 'lastactivity' => TIMESTAMP, 'lastvisit' => TIMESTAMP), "uid='$_G[uid]'", 1);
		}
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
		$users[$uid] = DB::fetch_first("SELECT m.*, mc.*, ms.* FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_count')." mc USING(uid)
			LEFT JOIN ".DB::table('common_member_status')." ms USING(uid)
			WHERE m.uid='$uid'");
	}
	return $users[$uid];
}

function getuserprofile($field) {
	global $_G;
	if(isset($_G['member'][$field])) {
		return $_G['member'][$field];
	}
	static $tablefields = array(
		'count'		=> array('extcredits1','extcredits2','extcredits3','extcredits4','extcredits5','extcredits6','extcredits7','extcredits8','friends','posts','threads','digestposts','doings','blogs','albums','sharings','attachsize','views','oltime'),
		'status'	=> array('regip','lastip','lastvisit','lastactivity','lastpost','lastsendmail','notifications','myinvitations','pokes','pendingfriends','invisible','buyercredit','sellercredit','favtimes','sharetimes'),
		'field_forum'	=> array('publishfeed','customshow','customstatus','medals','sightml','groupterms','authstr','groups','attentiongroup'),
		'field_home'	=> array('videophoto','spacename','spacedescription','domain','addsize','addfriend','menunum','theme','spacecss','blockposition','recentnote','spacenote','privacy','feedfriend','acceptemail','magicgift'),
		'profile'	=> array('realname','gender','birthyear','birthmonth','birthday','constellation','zodiac','telephone','mobile','idcardtype','idcard','address','zipcode','nationality','birthprovince','birthcity','resideprovince','residecity','residedist','residecommunity','residesuite','graduateschool','company','education','occupation','position','revenue','affectivestatus','lookingfor','bloodtype','height','weight','alipay','icq','qq','yahoo','msn','taobao','site','bio','interest','field1','field2','field3','field4','field5','field6','field7','field8'),
		'verify'	=> array('verify1', 'verify2', 'verify3', 'verify4', 'verify5'),
	);
	$profiletable = '';
	foreach($tablefields as $table => $fields) {
		if(in_array($field, $fields)) {
			$profiletable = $table;
			break;
		}
	}
	if($profiletable) {
		$data = DB::fetch_first("SELECT ".implode(',', $tablefields[$table])." FROM ".DB::table('common_member_'.$table)." WHERE uid='$_G[uid]'");
		if(!$data) {
			foreach($tablefields[$table] as $k) {
				$data[$k] = '';
			}
		}
		$_G['member'] = array_merge(is_array($_G['member']) ? $_G['member'] : array(), $data);
		return $_G['member'][$field];
	}
}

function daddslashes($string, $force = 1, $strip = FALSE) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val, $force, $strip);
		}
	} else {
//		$string = addslashes($string);
		$string = addslashes($strip ? stripslashes($string) : $string);
	}
	return $string;
}

/**
 * 字符串解密加密
 **/
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	/**
	 * 隨機密鑰長度 取值 0-32;
	 * 加入隨機密鑰，可以令密文無任何規律，即便是原文和密鑰完全相同，加密結果也會每次不同，增大破解難度。
	 * 取值越大，密文變動規律越大，密文變化 = 16 的 $ckey_length 次方
	 * 當此值為 0 時，則不產生隨機密鑰
	 *
	 * @var int
	 **/
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
	require_once libfile('function/filesock');
	return _dfsockopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
}

/**
 * @param string $string
 * @param ENT_QUOTES|null $quote_style
 * @param bool $htmlspecialchars_decode
 * @param ENT_QUOTES|ENT_NOQUOTES|ENT_COMPAT|null $quote_style
 * @return string
 **/
function dhtmlspecialchars($string, $quote_style = null, $htmlspecialchars_decode = false, $htmlspecialchars_decode_quote_style = null) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
//			$string[$key] = dhtmlspecialchars($val, $quote_style);
			$string[$key] = dhtmlspecialchars($val, $quote_style, $htmlspecialchars_decode, $htmlspecialchars_decode_quote_style);
		}
	} else {
//		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
//		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));

		$searcharray1 = array('&', '"', '<', '>');
		$replacearray1 = array('&amp;', '&quot;', '&lt;', '&gt;');

		if ($quote_style & ENT_QUOTES) {
			$searcharray1[] = "'";
			$replacearray1[] = '&#039;';
		}

		$searcharray = array
			(
				"/&amp;#(\d{3,6}|x[a-fA-F0-9]{4});/",
				"/&amp;#([a-zA-Z][a-z0-9]{2,6});/",
			);
		$replacearray = array
			(
				"&#\\1;",
				"&#\\1;",
			);

		// bluelovers
		$htmlspecialchars_decode && $string = htmlspecialchars_decode($string, $htmlspecialchars_decode_quote_style);
		// bluelovers

		$string = preg_replace($searcharray, $replacearray, str_replace($searcharray1, $replacearray1, $string));
	}
	return $string;
}

function dexit($message = '') {
//	echo $message;
	if (is_array($message)) {
		print_r($message);
	} else {
		echo($message);
	}
	output();
	exit();
}

function dheader($string, $replace = true, $http_response_code = 0) {
	$string = str_replace(array("\r", "\n"), array('', ''), $string);

	if (sclass_exists('Scorpio_Hook')) {
		Scorpio_Hook::execute('Func_'.__FUNCTION__.':Before', array(&$string, &$replace, &$http_response_code));
	}

	if(empty($http_response_code) || PHP_VERSION < '4.3' ) {
		@header($string, $replace);
	} else {
		@header($string, $replace, $http_response_code);
	}

	if (sclass_exists('Scorpio_Hook')) {
		Scorpio_Hook::execute('Func_'.__FUNCTION__.':After', array(&$string, &$replace, &$http_response_code));
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

	// bluelovers
	$ext = '';
	$random = '';

	if (is_array($size)) {
		$class = isset($size['class']) ? $size['class'] : $size[0];
		$style = $style.(isset($size['style']) ? $size['style'] : $size[1]);

		if (isset($size['class'])) {
			unset($size['class']);
		} else {
			unset($size[0]);
		}
		if (isset($size['style'])) {
			unset($size['style']);
		} else {
			unset($size[1]);
		}
		if (isset($size['random'])) {
			$random = getglobal('timestamp');
			unset($size['random']);
		} else {
			$random = getglobal('timestamp');
			unset($size[2]);
		}

		if (is_array($size) && count($size)) {
			foreach ($size as $k => $v) {
				$ext .= ' '.$k.'="'.$v.'"';
			}
		}

		$size = $class;
	} else {
		$class = $size;
	}
	$size = explode('_', $size);
	$size = $size[0];
	// bluelovers

	$ucenterurl = empty($ucenterurl) ? $_G['setting']['ucenterurl'] : $ucenterurl;
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));

	// bluelovers
	$class = empty($class) ? $size : $class;

	$url_ext = $ext2 = '';

	if ($random) $url_ext .= '&random='.$random;

	if($uid > 0) {
	// bluelovers

		$ext2 .= ' onerror="this.onerror=null;this.src=\''.$ucenterurl.'/images/noavatar_'.$size.'.gif\'"';
		$ext2 .= ' class="avatar avatar_'.$class.'" style="'.$style.'"';
		$ext2 .= '" lowsrc="'.$ucenterurl.'/images/noavatar_'.$size.'.gif"';
		$ext = $ext2 . ' ' . $ext;

		if(!$staticavatar && !$static) {

			$file = $ucenterurl.'/avatar.php?uid='.$uid.'&size='.$size.($real ? '&type=real' : '').$url_ext;

			return $returnsrc ? $file : '<img src="'.$file.'"'.$ext.' />';
		} else {
			$uid = sprintf("%09d", $uid);
			$dir1 = substr($uid, 0, 3);
			$dir2 = substr($uid, 3, 2);
			$dir3 = substr($uid, 5, 2);
			$file = $ucenterurl.'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).($real ? '_real' : '').'_avatar_'.$size.'.jpg';

			if ($url_ext) $file .= '?'.$url_ext;

			return $returnsrc ? $file : '<img src="'.$file.'" '.$ext.' />';
		}

	// bluelovers
	} else {
//		$ext2 .= ' onerror="this.onerror=null;this.src=\''.$ucenterurl.'/images/noavatar_'.$size.'.gif\'"';
		$ext2 .= ' class="avatar avatar_'.$class.'" style="'.$style.'"';
//		$ext2 .= '" lowsrc="'.$ucenterurl.'/images/noavatar_'.$size.'.gif"';
		$ext = $ext2 . ' ' . $ext;

		$file = (!preg_match('/^http:\/\//i', IMGDIR) ? $GLOBALS['boardurl'] : '').IMGDIR.'/syspm.gif';
		return $returnsrc ? $file : '<img src="'.$file.'" '.$ext.' />';
	}
	// bluelovers
}

/**
 * 加載語言
 *
 * @param $file - 語言文件，可包含路徑如 forum/xxx home/xxx
 * @param $langvar - 語言文字索引
 * @param $vars - 變量替換數組
 * @return 語言文字
 */
//function lang($file, $langvar = null, $vars = array(), $default = null) {
//}
function lang($file, $langvar = null, $vars = array(), $default = null, $checkmode = 0) {
	global $_G;
	list($path, $file) = explode('/', $file);
	if(!$file) {
		$file = $path;
		$path = '';
	}

	if($path != 'plugin') {
		$key = $path == '' ? $file : $path.'_'.$file;
		if(!isset($_G['lang'][$key])) {

			// bluelovers
			$lang = null;
			// bluelovers

			include DISCUZ_ROOT.'./source/language/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';

			// bluelovers
			if (sclass_exists('Scorpio_Hook')) {
				Scorpio_Hook::execute('Func_'.__FUNCTION__.'', array('load_lang:after', &$lang, array(
					'key' => $key,
					'file' => $file,
					'langvar' => $langvar,
					'vars' => $vars,
					'default' => $default,
				)));
			}
			// bluelovers

			$_G['lang'][$key] = $lang;
		}
		$returnvalue = &$_G['lang'];
	} else {
		if(!isset($_G['lang']['plugin'])) {

			// bluelovers
			$lang = null;
			// bluelovers

			include DISCUZ_ROOT.'./data/plugindata/lang_plugin.php';

			// bluelovers
			if (sclass_exists('Scorpio_Hook')) {
				Scorpio_Hook::execute('Func_'.__FUNCTION__.'', array('load_lang_plugindata:after', &$lang, array(
					'langvar' => $langvar,
					'vars' => $vars,
					'default' => $default,
				)));
			}
			// bluelovers

			$_G['lang']['plugin'] = $lang;
		}
		$returnvalue = &$_G['lang']['plugin'];
		$key = &$file;
	}
	$return = $langvar !== null ? (isset($returnvalue[$key][$langvar]) ? $returnvalue[$key][$langvar] : null) : $returnvalue[$key];

	// bluelovers
	if ($checkmode) {
		return $return ? $return : null;
	}
	// bluelovers

	$return = $return === null ? ($default !== null ? $default : $langvar) : $return;
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

function checktplrefresh($maintpl, $subtpl, $timecompare, $templateid, $cachefile, $tpldir, $file) {
	static $tplrefresh, $timestamp;
	if($tplrefresh === null) {
		$tplrefresh = getglobal('config/output/tplrefresh');
		$timestamp = getglobal('timestamp');
	}

	if(empty($timecompare) || $tplrefresh == 1 || ($tplrefresh > 1 && !($timestamp % $tplrefresh))) {
		if(empty($timecompare) || @filemtime(DISCUZ_ROOT.$subtpl) > $timecompare) {
			require_once DISCUZ_ROOT.'/source/class/class_template.php';
			$template = new template();
			$template->parse_template($maintpl, $templateid, $tpldir, $file, $cachefile);
			return TRUE;
		}
	}
	return FALSE;
}

function template($file, $templateid = 0, $tpldir = '', $gettplfile = 0, $primaltpl='') {
	global $_G;
	static $_init_style = false;
	if($_init_style === false) {
		$discuz = & discuz_core::instance();
		$discuz->_init_style();
		$_init_style = true;
	}
	if(strexists($file, ':')) {
		$clonefile = '';
		list($templateid, $file, $clonefile) = explode(':', $file);
		$oldfile = $file;
		$file = empty($clonefile) || STYLEID != $_G['cache']['style_default']['styleid'] ? $file : $file.'_'.$clonefile;
		if($templateid == 'diy' && STYLEID == $_G['cache']['style_default']['styleid']) {
			$_G['style']['prefile'] = '';
			$diypath = DISCUZ_ROOT.'./data/diy/';
			$preend = '_diy_preview';
			$previewname = $diypath.$file.$preend.'.htm';
			$_G['gp_preview'] = !empty($_G['gp_preview']) ? $_G['gp_preview'] : '';
			$curtplname = $oldfile;
			if(file_exists($diypath.$file.'.htm')) {
				$tpldir = 'data/diy';
				!$gettplfile && $_G['style']['tplsavemod'] = 1;

				$curtplname = $file;
				$flag = file_exists($previewname);
				if($_G['gp_preview'] == 'yes') {
					$file .= $flag ? $preend : '';
				} else {
					$_G['style']['prefile'] = $flag ? 1 : '';
				}

			} elseif(file_exists($diypath.($primaltpl ? $primaltpl : $oldfile).'.htm')) {
				$file = $primaltpl ? $primaltpl : $oldfile;
				$tpldir = 'data/diy';
				!$gettplfile && $_G['style']['tplsavemod'] = 0;

				$curtplname = $file;
				$flag = file_exists($previewname);
				if($_G['gp_preview'] == 'yes') {
					$file .= $flag ? $preend : '';
				} else {
					$_G['style']['prefile'] = $flag ? 1 : '';
				}

			} else {
				$file = $primaltpl ? $primaltpl : $oldfile;
			}
			$tplrefresh = $_G['config']['output']['tplrefresh'];
			$tplmtime = @filemtime($diypath.$file.'.htm');
			if($tpldir == 'data/diy' && ($tplrefresh ==1 || ($tplrefresh > 1 && !($_G['timestamp'] % $tplrefresh))) && $tplmtime && $tplmtime < @filemtime(DISCUZ_ROOT.TPLDIR.'/'.($primaltpl ? $primaltpl : $oldfile).'.htm')) {
				if (!updatediytemplate($file)) {
					@unlink($diypath.$file.'.htm');
					$tpldir = '';
				}
			}

			if (!$gettplfile && empty($_G['style']['tplfile'])) {
				$_G['style']['tplfile'] = empty($clonefile) ? $curtplname : $oldfile.':'.$clonefile;
			}

			$_G['style']['prefile'] = !empty($_G['gp_preview']) && $_G['gp_preview'] == 'yes' ? '' : $_G['style']['prefile'];

		} else {
			$tpldir = './source/plugin/'.$templateid.'/template';
		}
	}

	$file .= !empty($_G['inajax']) && ($file == 'common/header' || $file == 'common/footer') ? '_ajax' : '';
	$tpldir = $tpldir ? $tpldir : (defined('TPLDIR') ? TPLDIR : '');
	$templateid = $templateid ? $templateid : (defined('TEMPLATEID') ? TEMPLATEID : '');
	$tplfile = ($tpldir ? $tpldir.'/' : './template/').$file.'.htm';
	$filebak = $file;
	$file == 'common/header' && defined('CURMODULE') && CURMODULE && $file = 'common/header_'.$_G['basescript'].'_'.CURMODULE;
	$cachefile = './data/template/'.(defined('STYLEID') ? STYLEID.'_' : '_').$templateid.'_'.str_replace('/', '_', $file).'.tpl.php';
	if($templateid != 1 && !file_exists(DISCUZ_ROOT.$tplfile)) {
		$tplfile = './template/default/'.$filebak.'.htm';
	}
	if($gettplfile) {
		return $tplfile;
	}
	checktplrefresh($tplfile, $tplfile, @filemtime(DISCUZ_ROOT.$cachefile), $templateid, $cachefile, $tpldir, $file);
	return DISCUZ_ROOT.$cachefile;
}


function modauthkey($id) {
	global $_G;
	return md5($_G['username'].$_G['uid'].$_G['authkey'].substr(TIMESTAMP, 0, -7).$id);
}

function getcurrentnav() {
	global $_G;
	if(!empty($_G['mnid'])) {
		return $_G['mnid'];
	}
	$mnid = '';
	$_G['basefilename'] = $_G['basefilename'] == $_G['basescript'] ? $_G['basefilename'] : $_G['basescript'].'.php';
	if(array_key_exists($_G['basefilename'], $_G['setting']['navmns'])) {
		foreach($_G['setting']['navmns'][$_G['basefilename']] as $navmn) {
			if($navmn[0] == array_intersect_assoc($navmn[0], $_GET)) {
				$mnid = $navmn[1];
			}
		}
	}
	if(!$mnid && isset($_G['setting']['navdms'])) {
		foreach($_G['setting']['navdms'] as $navdm => $navid) {
			if(strexists(strtolower($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), $navdm)) {
				$mnid = $navid;
				break;
			}
		}
	}
	if(!$mnid && isset($_G['setting']['navmn'][$_G['basefilename']])) {
		$mnid = $_G['setting']['navmn'][$_G['basefilename']];
	}
	return $mnid;
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
	foreach ($cachenames as $k) {
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
			} elseif(strexists($cname, 'usergroup_'.$_G['groupid'])) {
				$_G['cache'][$cname] = $_G['perm'] = $_G['group'] = $data;
			} elseif(!$_G['uid'] && strexists($cname, $_G['setting']['newusergroupid'])) {
				$_G['perm'] = $data;
			} elseif($cname == 'style_default') {
				$_G['cache'][$cname] = $_G['style'] = $data;
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
	global $_G;
	static $isfilecache, $allowmem;

	if($isfilecache === null) {
		$isfilecache = getglobal('config/cache/type') == 'file';
		$allowmem = memory('check');
	}

	$data = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	if($allowmem) {
		$newarray = array();
		foreach ($cachenames as $name) {
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
				fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!\n//Identify: ".md5($syscache['cname'].$cachedata.$_G['config']['security']['authkey'])."\n\n$cachedata?>");
				fclose($fp);
			}
		}
	}

	foreach($cachenames as $name) {
		if($data[$name] === null) {
			$data[$name] = null;
			$allowmem && (memory('set', $name, array()));
		}
	}

	return $data;
}

function dgmdate($timestamp, $format = 'dt', $timeoffset = '9999', $uformat = '') {
	global $_G;
	$format == 'u' && !$_G['setting']['dateconvert'] && $format = 'dt';
	static $dformat, $tformat, $dtformat, $offset, $lang;
	if($dformat === null) {
		$dformat = getglobal('setting/dateformat');
		$tformat = getglobal('setting/timeformat');
		$dtformat = $dformat.' '.$tformat;
		$offset = getglobal('member/timeoffset');
		$lang = lang('core', 'date');
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
				return '<span title="'.$s.'">'.intval($time / 3600).'&nbsp;'.$lang['hour'].$lang['before'].'</span>';
			} elseif($time > 1800) {
				return '<span title="'.$s.'">'.$lang['half'].$lang['hour'].$lang['before'].'</span>';
			} elseif($time > 60) {
				return '<span title="'.$s.'">'.intval($time / 60).'&nbsp;'.$lang['min'].$lang['before'].'</span>';
			} elseif($time > 0) {
				return '<span title="'.$s.'">'.$time.'&nbsp;'.$lang['sec'].$lang['before'].'</span>';
			} elseif($time == 0) {
				return '<span title="'.$s.'">'.$lang['now'].'</span>';
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

	// bluelovers
	Scorpio_Hook::execute('Func_' . __FUNCTION__ . ':Before', array(&$cachename, &$data, array(&$isfilecache, &$allowmem)));
	// bluelovers

	if($isfilecache === null) {
		$isfilecache = getglobal('config/cache/type') == 'file';
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

function block_get($parameter) {
	global $_G;
	static $allowmem;
	if($allowmem === null) {
		include_once libfile('function/block');
		$allowmem = getglobal('setting/memory/diyblock/enable') && memory('check');
	}
	if(!$allowmem) {
		block_get_batch($parameter);
		return true;
	}
	$blockids = explode(',', $parameter);
	$lostbids = array();
	foreach ($blockids as $bid) {
		$bid = intval($bid);
		if($bid) {
			$_G['block'][$bid] = memory('get', 'blockcache_'.$bid);
			if($_G['block'][$bid] === null) {
				$lostbids[] = $bid;
			}
		}
	}

	if($lostbids) {
		block_get_batch(implode(',', $lostbids));
		foreach ($lostbids as $bid) {
			if(isset($_G['block'][$bid])) {
				memory('set', 'blockcache_'.$bid, $_G['block'][$bid], getglobal('setting/memory/diyblock/ttl'));
			}
		}
	}
}

function block_display($bid) {
	include_once libfile('function/block');
	block_display_batch($bid);
}

function dimplode($array) {
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}

// bluelovers
function sclass_exists($class) {
	return (class_exists($class, false) || interface_exists($class, false)) ? true : false;
}
// bluelovers

function libfile($libname, $folder = '', $source = 'source/') {
//	$libpath = DISCUZ_ROOT.'/source/'.$folder;
	$libpath = DISCUZ_ROOT.'/'.(is_array($source) ? implode('/', $source) : $source).'/'.$folder;
	if(strstr($libname, '/')) {
		list($pre, $name) = explode('/', $libname);
//		return realpath("{$libpath}/{$pre}/{$pre}_{$name}.php");
		$ret = "{$libpath}/{$pre}/{$pre}_{$name}.php";
	} else {
//		return realpath("{$libpath}/{$libname}.php");
		$ret = realpath("{$libpath}/{$libname}.php");
	}

	// bluelovers
	if (sclass_exists('Scorpio_File')) {
		$ret = Scorpio_File::file($ret);
	} else {
		$ret = str_replace(array('\\', '//'), '/', $ret);
	}
	if (sclass_exists('Scorpio_Hook')) {
		Scorpio_Hook::execute('Func_'.__FUNCTION__.'', array(&$ret, DISCUZ_ROOT));
	}
	// bluelovers

	return $ret;
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

function aidencode($aid, $type = 0) {
	global $_G;
	$s = !$type ? $aid.'|'.substr(md5($aid.md5($_G['authkey']).TIMESTAMP.$_G['uid']), 0, 8).'|'.TIMESTAMP.'|'.$_G['uid'] : $aid.'|'.md5($aid.md5($_G['config']['security']['authkey']).TIMESTAMP).'|'.TIMESTAMP;
	return rawurlencode(base64_encode($s));
}

function getforumimg($aid, $nocache = 0, $w = 140, $h = 140, $type = '') {
	global $_G;
	$key = authcode("$aid\t$w\t$h", 'ENCODE', $_G['config']['security']['authkey']);
	return 'forum.php?mod=image&aid='.$aid.'&size='.$w.'x'.$h.'&key='.rawurlencode($key).($nocache ? '&nocache=yes' : '').($type ? '&type='.$type : '');
}

function rewritedata() {
	global $_G;
	$data = array();
	if(!defined('IN_ADMINCP')) {
		if(in_array('portal_topic', $_G['setting']['rewritestatus'])) {
			$data['search']['portal_topic'] = "/".$_G['domain']['pregxprw']['portal']."\?mod\=topic&(amp;)?topic\=(.+?)?\"([^\>]*)\>/e";
			$data['replace']['portal_topic'] = "rewriteoutput('portal_topic', 0, '\\1', '\\3', '\\4')";
		}

		if(in_array('portal_article', $_G['setting']['rewritestatus'])) {
			$data['search']['portal_article'] = "/".$_G['domain']['pregxprw']['portal']."\?mod\=view&(amp;)?aid\=(\d+)(&amp;page\=(\d+))?\"([^\>]*)\>/e";
			$data['replace']['portal_article'] = "rewriteoutput('portal_article', 0, '\\1', '\\3', '\\5', '\\6')";
		}

		if(in_array('forum_forumdisplay', $_G['setting']['rewritestatus'])) {
			$data['search']['forum_forumdisplay'] = "/".$_G['domain']['pregxprw']['forum']."\?mod\=forumdisplay&(amp;)?fid\=(\w+)(&amp;page\=(\d+))?\"([^\>]*)\>/e";
			$data['replace']['forum_forumdisplay'] = "rewriteoutput('forum_forumdisplay', 0, '\\1', '\\3', '\\5', '\\6')";
		}

		if(in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
			$data['search']['forum_viewthread'] = "/".$_G['domain']['pregxprw']['forum']."\?mod\=viewthread&(amp;)?tid\=(\d+)(&amp;extra\=(page\%3D(\d+))?)?(&amp;page\=(\d+))?\"([^\>]*)\>/e";
			$data['replace']['forum_viewthread'] = "rewriteoutput('forum_viewthread', 0, '\\1', '\\3', '\\8', '\\6', '\\9')";
		}

		if(in_array('group_group', $_G['setting']['rewritestatus'])) {
			$data['search']['group_group'] = "/".$_G['domain']['pregxprw']['forum']."\?mod\=group&(amp;)?fid\=(\d+)(&amp;page\=(\d+))?\"([^\>]*)\>/e";
			$data['replace']['group_group'] = "rewriteoutput('group_group', 0, '\\1', '\\3', '\\5', '\\6')";
		}

		if(in_array('home_space', $_G['setting']['rewritestatus'])) {
			$data['search']['home_space'] = "/".$_G['domain']['pregxprw']['home']."\?mod=space&(amp;)?(uid\=(\d+)|username\=([^&]+?))\"([^\>]*)\>/e";
			$data['replace']['home_space'] = "rewriteoutput('home_space', 0, '\\1', '\\4', '\\5', '\\6')";
		}

		if(in_array('all_script', $_G['setting']['rewritestatus'])) {
			$data['search']['all_script'] = "/".$_G['domain']['pregxprw']['all_script']."(([a-z]+)\.php)?\?mod=([^\"]+?)\"([^\>]*)?\>/e";
			$data['replace']['all_script'] = "rewriteoutput('all_script', 0, '\\1', '\\4', '\\5', '\\6', '\\7')";
		}
	} else {
		$data['rulesearch']['portal_topic'] = 'topic-{name}.html';
		$data['rulereplace']['portal_topic'] = 'portal.php?mod=topic&topic={name}';
		$data['rulevars']['portal_topic']['{name}'] = '(.+)';

		$data['rulesearch']['portal_article'] = 'article-{id}-{page}.html';
		$data['rulereplace']['portal_article'] = 'portal.php?mod=view&aid={id}&page={page}';
		$data['rulevars']['portal_article']['{id}'] = '([0-9]+)';
		$data['rulevars']['portal_article']['{page}'] = '([0-9]+)';

		$data['rulesearch']['forum_forumdisplay'] = 'forum-{fid}-{page}.html';
		$data['rulereplace']['forum_forumdisplay'] = 'forum.php?mod=forumdisplay&fid={fid}&page={page}';
		$data['rulevars']['forum_forumdisplay']['{fid}'] = '(\w+)';
		$data['rulevars']['forum_forumdisplay']['{page}'] = '([0-9]+)';

		$data['rulesearch']['forum_viewthread'] = 'thread-{tid}-{page}-{prevpage}.html';
		$data['rulereplace']['forum_viewthread'] = 'forum.php?mod=viewthread&tid={tid}&extra=page\%3D{prevpage}&page={page}';
		$data['rulevars']['forum_viewthread']['{tid}'] = '([0-9]+)';
		$data['rulevars']['forum_viewthread']['{page}'] = '([0-9]+)';
		$data['rulevars']['forum_viewthread']['{prevpage}'] = '([0-9]+)';

		$data['rulesearch']['group_group'] = 'group-{fid}-{page}.html';
		$data['rulereplace']['group_group'] = 'forum.php?mod=group&fid={fid}&page={page}';
		$data['rulevars']['group_group']['{fid}'] = '([0-9]+)';
		$data['rulevars']['group_group']['{page}'] = '([0-9]+)';

		$data['rulesearch']['home_space'] = 'space-{user}-{value}.html';
		$data['rulereplace']['home_space'] = 'home.php?mod=space&{user}={value}';
		$data['rulevars']['home_space']['{user}'] = '(username|uid)';
		$data['rulevars']['home_space']['{value}'] = '(.+)';

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
	if($type == 'forum_forumdisplay') {
		list(,,, $fid, $page, $extra) = func_get_args();
		$r = array(
			'{fid}' => empty($_G['setting']['forumkeys'][$fid]) ? $fid : $_G['setting']['forumkeys'][$fid],
			'{page}' => $page ? $page : 1,
		);
	} elseif($type == 'forum_viewthread') {
		list(,,, $tid, $page, $prevpage, $extra) = func_get_args();
		$r = array(
			'{tid}' => $tid,
			'{page}' => $page ? $page : 1,
			'{prevpage}' => $prevpage && !IS_ROBOT ? $prevpage : 1,
		);
	} elseif($type == 'home_space') {
		list(,,, $uid, $username, $extra) = func_get_args();
		$_G['setting']['rewritecompatible'] && $username = rawurlencode($username);
		$r = array(
			'{user}' => $uid ? 'uid' : 'username',
			'{value}' => $uid ? $uid : $username,
		);
	} elseif($type == 'group_group') {
		list(,,, $fid, $page, $extra) = func_get_args();
		$r = array(
			'{fid}' => $fid,
			'{page}' => $page ? $page : 1,
		);
	} elseif($type == 'portal_topic') {
		list(,,, $name, $extra) = func_get_args();
		$r = array(
			'{name}' => $name,
		);
	} elseif($type == 'portal_article') {
		list(,,, $id, $page, $extra) = func_get_args();
		$r = array(
			'{id}' => $id,
			'{page}' => $page ? $page : 1,
		);
	} elseif($type == 'all_script') {
		list(,,, $script, $param, $extra) = func_get_args();
		if(!$script) $script = 'index';
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
		if(!preg_match('/^\w+\.php/i', $url)) {
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

//function output() {
function output($in_ajax = false) {

	global $_G;


	if(defined('DISCUZ_OUTPUTED')) {
		return;
	} else {
		define('DISCUZ_OUTPUTED', 1);
	}

	if(!empty($_G['blockupdate'])) {
		block_updatecache($_G['blockupdate']['bid']);
	}
	if(empty($_G['setting']['domain']['app']['default'])) {
		$temp = parse_url($_G['siteurl']);
		$_G['setting']['domain']['app']['default'] = $temp['host'];
	}
	$_G['domain'] = array();
	$port = empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':'.$_SERVER['SERVER_PORT'];
	if(is_array($_G['setting']['domain']['app'])) {

		// bluelovers
		if (!isset($_G['setting']['domain']['app']['userapp'])) {
			$_G['setting']['domain']['app']['userapp'] = null;
		}
		// bluelovers

		foreach($_G['setting']['domain']['app'] as $app => $domain) {
			if($domain || $_G['setting']['domain']['app']['default']) {
				$appphp = "{$app}.php";
				if(!$domain) {
					$domain = $_G['setting']['domain']['app']['default'];
				}

				// bluelovers
				$_G['domain']['search'][$app.'2'] = "<a href=\"{$app}.php\"";
				$_G['domain']['replace'][$app.'2'] = '<a href="http://'.$domain.$port.$_G['siteroot'].$app."\"";
				// bluelovers

				$_G['domain']['search'][$app] = "<a href=\"{$app}.php";
				$_G['domain']['replace'][$app] = '<a href="http://'.$domain.$port.$_G['siteroot'].$appphp;
				$_G['domain']['pregxprw'][$app] = '<a href\="http\:\/\/('.preg_quote($domain.$port.$_G['siteroot'], '/').')'.$appphp;
			} else {
				$_G['domain']['pregxprw'][$app] = "<a href\=\"(){$app}.php";
			}
		}
		$_G['domain']['pregxprw']['all_script'] .= '<a href\="http\:\/\/(('.implode('|', $_G['setting']['domain']['app']).')'.preg_quote($port.$_G['siteroot'], '/').')';
	}
	if($_G['setting']['rewritestatus'] || $_G['domain']['search']) {

		$content = ob_get_contents();

		$_G['domain']['search'] && $content = str_replace($_G['domain']['search'], $_G['domain']['replace'], $content);

		// bluelovers
		if ($_G['setting']['domain']['app']['default'] || $_G['setting']['rewritestatus']) {
			$content = preg_replace("/<a(\s+(?:[a-z]+)=\"[a-z0-9]+\")+\s+href=\"([^\"]+)\"/i", "<a href=\"\\2\"\\1", $content);
		}
		// bluelovers

		$_G['setting']['domain']['app']['default'] && $content = preg_replace("/<a href=\"([^\"]+)\"/e", "rewriteoutput('site_default', 0, '".$_G['setting']['domain']['app']['default'].$port.$_G['siteroot']."', '\\1')", $content);

		if($_G['setting']['rewritestatus'] && !defined('IN_MODCP') && !defined('IN_ADMINCP')) {
			$searcharray = $replacearray = array();
			$array = rewritedata();
			$content = preg_replace($array['search'], $array['replace'], $content);
		}

		// bluelovers
		if (!$in_ajax) {
		// bluelovers
			ob_end_clean();
			$_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();

			echo $content;

//			phpinfo();
//			exit();

		// bluelovers
		} else {
			return $content;
		}
		// bluelovers
	}

	// bluelovers
	if ($in_ajax) return;
	// bluelovers

	if($_G['setting']['ftp']['connid']) {
		@ftp_close($_G['setting']['ftp']['connid']);
	}
	$_G['setting']['ftp'] = array();

	if(defined('CACHE_FILE') && CACHE_FILE && !defined('CACHE_FORBIDDEN')) {
		global $_G;
		if(diskfreespace(DISCUZ_ROOT.'./'.$_G['setting']['cachethreaddir']) > 1000000) {
			if($fp = @fopen(CACHE_FILE, 'w')) {
				flock($fp, LOCK_EX);
				fwrite($fp, empty($content) ? ob_get_contents() : $content);
			}
			@fclose($fp);
			chmod(CACHE_FILE, 0777);
		}
	}

	if(defined('DISCUZ_DEBUG') && DISCUZ_DEBUG && @include(libfile('function/debug'))) {
		function_exists('debugmessage') && debugmessage();
	}
}

function output_ajax() {

	// bluelvoers
	if (!$s = output(true)) {
	// bluelvoers

		$s = ob_get_contents();

	// bluelvoers
	}
	// bluelvoers

	ob_end_clean();
	$s = preg_replace("/([\\x01-\\x08\\x0b-\\x0c\\x0e-\\x1f])+/", ' ', $s);
	$s = str_replace(array(chr(0), ']]>'), array(' ', ']]&gt;'), $s);
	if(defined('DISCUZ_DEBUG') && DISCUZ_DEBUG && @include(libfile('function/debug'))) {
		function_exists('debugmessage') && $s .= debugmessage(1);
	}
	return $s;
}

function runhooks() {
	global $_G;
	if(defined('CURMODULE')) {
		hookscript(CURMODULE, $_G['basescript']);
		if(($do = !empty($_G['gp_do']) ? $_G['gp_do'] : (!empty($_GET['do']) ? $_GET['do'] : ''))) {
			hookscript(CURMODULE, $_G['basescript'].'_'.$do);
		}
	}
}

function hookscript($script, $hscript, $type = 'funcs', $param = array(), $func = '') {
	global $_G;
	static $pluginclasses;
	if(!isset($_G['setting']['hookscript'][$hscript][$script][$type])) {
		return;
	}
	if(!isset($_G['cache']['plugin'])) {
		loadcache('plugin');
	}
	foreach((array)$_G['setting']['hookscript'][$hscript][$script]['module'] as $identifier => $include) {
		$hooksadminid[$identifier] = !$_G['setting']['hookscript'][$hscript][$script]['adminid'][$identifier] || ($_G['setting']['hookscript'][$hscript][$script]['adminid'][$identifier] && $_G['adminid'] > 0 && $_G['setting']['hookscript'][$hscript][$script]['adminid'][$identifier] >= $_G['adminid']);
		if($hooksadminid[$identifier]) {
			@include_once DISCUZ_ROOT.'./source/plugin/'.$include.'.class.php';
		}
	}
	if(@is_array($_G['setting']['hookscript'][$hscript][$script][$type])) {
		$funcs = !$func ? $_G['setting']['hookscript'][$hscript][$script][$type] : array($func => $_G['setting']['hookscript'][$hscript][$script][$type][$func]);
		foreach($funcs as $hookkey => $hookfuncs) {
			foreach($hookfuncs as $hookfunc) {
				if($hooksadminid[$hookfunc[0]]) {
					$classkey = 'plugin_'.($hookfunc[0].($hscript != 'global' ? '_'.$hscript : ''));
					if(!class_exists($classkey)) {
						continue;
					}
					if(!isset($pluginclasses[$classkey])) {
						$pluginclasses[$classkey] = new $classkey;
					}
					if(!method_exists($pluginclasses[$classkey], $hookfunc[1])) {
						continue;
					}
					$return = $pluginclasses[$classkey]->$hookfunc[1]($param);
					if(is_array($return)) {
						foreach($return as $k => $v) {
							$_G['setting']['pluginhooks'][$hookkey][$k] .= $v;
						}
					} else {
						$_G['setting']['pluginhooks'][$hookkey] .= $return;
					}
				}
			}
		}
	}
}

function hookscriptoutput($tplfile) {
	global $_G;
	hookscript('global', 'global');
	if(defined('CURMODULE')) {
		$param = array('template' => $tplfile, 'message' => $_G['hookscriptmessage'], 'values' => $_G['hookscriptvalues']);
		hookscript(CURMODULE, $_G['basescript'], 'outputfuncs', $param);
		if(($do = !empty($_G['gp_do']) ? $_G['gp_do'] : (!empty($_GET['do']) ? $_GET['do'] : ''))) {
			hookscript(CURMODULE, $_G['basescript'].'_'.$do, 'outputfuncs', $param);
		}
	}
}

function pluginmodule($pluginid, $type) {
	global $_G;
	if(!isset($_G['cache']['plugin'])) {
		loadcache('plugin');
	}
	list($identifier, $module) = explode(':', $pluginid);
	if(!is_array($_G['setting']['plugins'][$type]) || !array_key_exists($pluginid, $_G['setting']['plugins'][$type])) {
		showmessage('undefined_action');
	}
	if(!empty($_G['setting']['plugins'][$type][$pluginid]['url'])) {
		dheader('location: '.$_G['setting']['plugins'][$type][$pluginid]['url']);
	}
	$directory = $_G['setting']['plugins'][$type][$pluginid]['directory'];
	if(empty($identifier) || !preg_match("/^[a-z]+[a-z0-9_]*\/$/i", $directory) || !preg_match("/^[a-z0-9_\-]+$/i", $module)) {
		showmessage('undefined_action');
	}
	if(@!file_exists(DISCUZ_ROOT.($modfile = './source/plugin/'.$directory.$module.'.inc.php'))) {
		showmessage('plugin_module_nonexistence', '', array('mod' => $modfile));
	}
	return DISCUZ_ROOT.$modfile;
}
function updatecreditbyaction($action, $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0) {

	include_once libfile('class/credit');
	$credit = & credit::instance();
	if($extrasql) {
		$credit->extrasql = $extrasql;
	}
	return $credit->execrule($action, $uid, $needle, $coef, $update, $fid);
}

function checklowerlimit($action, $uid = 0, $coef = 1, $fid = 0, $returnonly = 0) {
	global $_G;

	include_once libfile('class/credit');
	$credit = & credit::instance();
	$limit = $credit->lowerlimit($action, $uid, $coef, $fid);
	if($returnonly) return $limit;
	if($limit !== true) {
		$GLOBALS['id'] = $limit;
		$lowerlimit = is_array($action) && $action['extcredits'.$limit] ? abs($action['extcredits'.$limit]) + $_G['setting']['creditspolicy']['lowerlimit'][$limit] : $_G['setting']['creditspolicy']['lowerlimit'][$limit];
		$rulecredit = array();
		if(!is_array($action)) {
			$rule = $credit->getrule($action, $fid);
			foreach($_G['setting']['extcredits'] as $extcreditid => $extcredit) {
				if($rule['extcredits'.$extcreditid]) {
					$rulecredit[] = $extcredit['title'].($rule['extcredits'.$extcreditid] > 0 ? '+'.$rule['extcredits'.$extcreditid] : $rule['extcredits'.$extcreditid]);
				}
			}
		} else {
			$rule = array();
		}
		$values = array(
			'title' => $_G['setting']['extcredits'][$limit]['title'],
			'lowerlimit' => $lowerlimit,
			'unit' => $_G['setting']['extcredits'][$limit]['unit'],
			'ruletext' => $rule['rulename'],
			'rulecredit' => implode(', ', $rulecredit)
		);
		if(!is_array($action)) {
			if(!$fid) {
				showmessage('credits_policy_lowerlimit', '', $values);
			} else {
				showmessage('credits_policy_lowerlimit_fid', '', $values);
			}
		} else {
			showmessage('credits_policy_lowerlimit_norule', '', $values);
		}
	}
}

function batchupdatecredit($action, $uids = 0, $extrasql = array(), $coef = 1, $fid = 0) {

	include_once libfile('class/credit');
	$credit = & credit::instance();
	if($extrasql) {
		$credit->extrasql = $extrasql;
	}
	return $credit->updatecreditbyrule($action, $uids, $coef, $fid);
}

/**
 * 添加積分
 * @param Integer $uids: 用戶uid或者uid數組
 * @param String $dataarr: member count相關操作數組，例: array('extcredits1' => 1, 'doings' => -1)
 * @param Boolean $checkgroup: 是否檢查用戶組 true or false
 * @param String $operation: 積分記錄操作類型(不記錄積分日誌可忽略)
 * @param Integer $relatedid: 積分記錄相關 ID(不記錄積分日誌可忽略)
 * @param String $ruletxt: 動畫效果中的積分規則文本(UTF-8格式)
 *
 * @link http://www.bbsapp.com/thread-76-1-1.html
 *
 * 積分記錄操作類型對照表

	關聯 ID
	含義
TRC	common_task.taskid	任務獎勵積分
RTC	forum_thread.tid	發表懸賞主題扣除積分
RAC	forum_thread.tid	最佳答案獲取懸賞積分
MRC	common_magic.mid	道具隨即獲取積分
TFR	common_member.uid	積分轉賬轉出
RCV	common_member.uid	積分轉賬接收
CEC	common_member.uid	積分兌換
ECU	common_member.uid	通過ucenter 兌換積分
SAC	forum_attachment.aid	出售附件獲得積分
BAC	forum_attachment.aid	購買附件支出積分
PRC	forum_post.pid	帖子被評分所得積分
STC	forum_thread.tid	出售主題獲得積分
BTC	forum_thread.tid	購買主題支出積分
AFD	common_member.uid	購買積分即積分充值
UGP	common_usergroup.groupid	購買擴展用戶組支出積分
RPC	common_report.id        	舉報功能中的獎懲
ACC	forum_activity.tid	參與活動扣除積分
 */
function updatemembercount($uids, $dataarr = array(), $checkgroup = true, $operation = '', $relatedid = 0, $ruletxt = '') {
	if(empty($uids)) return;
	if(!is_array($dataarr) || empty($dataarr)) return;
	if($operation && $relatedid) {
		$writelog = true;
		$log = array(
			'uid' => $uids,
			'operation' => $operation,
			'relatedid' => $relatedid,
			'dateline' => time(),
		);
	} else {
		$writelog = false;
	}
	$data = array();
	foreach($dataarr as $key => $val) {
		if(empty($val)) continue;
		$val = intval($val);
		$id = intval($key);
		if(0< $id && $id < 9) {
			$data['extcredits'.$id] = $val;
			$writelog && $log['extcredits'.$id] = $val;
		} else {
			$data[$key] = $val;
		}
	}
	if($writelog) {
		DB::insert('common_credit_log', $log);
	}
	if($data) {
		include_once libfile('class/credit');
		$credit = & credit::instance();
		$credit->updatemembercount($data, $uids, $checkgroup, $ruletxt);
	}
}

function checkusergroup($uid = 0) {
	include_once libfile('class/credit');
	$credit = & credit::instance();
	$credit->checkusergroup($uid);
}

function checkformulasyntax($formula, $operators, $tokens) {
	$var = implode('|', $tokens);
	$operator = implode('', $operators);

	$operator = str_replace(
		array('+', '-', '*', '/', '(', ')', '{', '}', '\''),
		array('\+', '\-', '\*', '\/', '\(', '\)', '\{', '\}', '\\\''),
		$operator
	);

	if(!empty($formula)) {
		if(!preg_match("/^([$operator\.\d\(\)]|(($var)([$operator\(\)]|$)+))+$/", $formula) || !is_null(eval(preg_replace("/($var)/", "\$\\1", $formula).';'))){
			return false;
		}
	}
	return true;
}

function checkformulacredits($formula) {
	return checkformulasyntax(
		$formula,
		array('+', '-', '*', '/', ' '),
		array('extcredits[1-8]', 'digestposts', 'posts', 'threads', 'oltime', 'friends', 'doings', 'polls', 'blogs', 'albums', 'sharings')
	);
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
//		$_G['debuginfo'] = array('time' => number_format((dmicrotime() - $_G['starttime']), 6), 'queries' => $db->querynum, 'memory' => ucwords($_G['memory']));

		$ios = function_exists('get_included_files') ? count(get_included_files()) : 0;
		$umem = function_exists('memory_get_usage') ? strtolower(sizecount(memory_get_usage(), 1)) : 0;

		$_G['debuginfo'] = array('time' => number_format((dmicrotime() - $_G['starttime']), 6), 'queries' => $db->querynum, 'memory' => ucwords($_G['memory']), 'ios' => $ios, 'umem' => $umem);
		return TRUE;
	} else {
		return FALSE;
	}
}

function getfocus_rand($module) {
	global $_G;

	if(empty($_G['setting']['focus']) || !array_key_exists($module, $_G['setting']['focus'])) {
		return null;
	}
	do {
		$focusid = $_G['setting']['focus'][$module][array_rand($_G['setting']['focus'][$module])];
		if(!empty($_G['cookie']['nofocus_'.$focusid])) {
			unset($_G['setting']['focus'][$module][$focusid]);
			$continue = 1;
		} else {
			$continue = 0;
		}
	} while(!empty($_G['setting']['focus'][$module]) && $continue);
	if(!$_G['setting']['focus'][$module]) {
		return null;
	}
	loadcache('focus');
	if(empty($_G['cache']['focus']['data']) || !is_array($_G['cache']['focus']['data'])) {
		return null;
	}
	return $focusid;
}

function check_seccode($value, $idhash) {
	global $_G;
	if(!$_G['setting']['seccodestatus']) {
		return true;
	}
	if(!isset($_G['cookie']['seccode'.$idhash])) {
		return false;
	}
	list($checkvalue, $checktime, $checkidhash, $checkformhash) = explode("\t", authcode($_G['cookie']['seccode'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
	return $checkvalue == strtoupper($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && FORMHASH == $checkformhash;
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
	list($checkvalue, $checktime, $checkidhash, $checkformhash) = explode("\t", authcode($_G['cookie']['secqaa'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
	return $checkvalue == md5($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && FORMHASH == $checkformhash;
}

function adshow($parameter) {
	global $_G;
	if($_G['inajax']) {
		return;
	}
	$params = explode('/', $parameter);
	$customid = 0;
	$customc = explode('_', $params[0]);
	if($customc[0] == 'custom') {
		$params[0] = $customc[0];
		$customid = $customc[1];
	}
	$adcontent = null;
	if(empty($_G['setting']['advtype']) || !in_array($params[0], $_G['setting']['advtype'])) {
		$adcontent = '';
	}
	if($adcontent === null) {
		loadcache('advs');
		$adids = array();
		$evalcode = &$_G['cache']['advs']['evalcode'][$params[0]];
		$parameters = &$_G['cache']['advs']['parameters'][$params[0]];
		$codes = &$_G['cache']['advs']['code'][$_G['basescript']][$params[0]];
		if(!empty($codes)) {
			foreach($codes as $adid => $code) {
				$parameter = &$parameters[$adid];
				$checked = true;
				@eval($evalcode['check']);
				if($checked) {
					$adids[] = $adid;
				}
			}
			if(!empty($adids)) {
				$adcode = $extra = '';
				@eval($evalcode['create']);
				if(empty($notag)) {
					$adcontent = '<div'.($params[1] != '' ? ' class="'.$params[1].'"' : '').$extra.'>'.$adcode.'</div>';
				} else {
					$adcontent = $adcode;
				}
			}
		}
	}
	$adfunc = 'ad_'.$params[0];
	$_G['setting']['pluginhooks'][$adfunc] = null;
	$hscript = $_G['basescript'].(($do = !empty($_G['gp_do']) ? $_G['gp_do'] : (!empty($_GET['do']) ? $_GET['do'] : '')) ? '_'.$do : '');
	hookscript('ad', 'global', 'funcs', array('params' => $params, 'content' => $adcontent), $adfunc);
	hookscript('ad', $hscript, 'funcs', array('params' => $params, 'content' => $adcontent), $adfunc);
	return $_G['setting']['pluginhooks'][$adfunc] === null ? $adcontent : $_G['setting']['pluginhooks'][$adfunc];
}

/**
 * 顯示提示信息
 *
 * @param $message - 提示信息，可中文也可以是 lang_message.php 中的數組 key 值
 * @param $url_forward - 提示後跳轉的 url
 * @param $values - 提示信息中可替換的變量值 array(key => value ...) 形式
 * @param $extraparam - 擴展參數 array(key => value ...) 形式
 **/
function showmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
	global $_G;

	$param = array(

		/** 跳轉控制 **/
		'header'	=> false,
		// header跳轉
		'timeout'	=> null,
		// 定時跳轉
		'refreshtime'	=> null,
		// 自定義跳轉時間
		'closetime'	=> null,
		// 自定義關閉時間，限於 msgtype = 2
		'locationtime'	=> null,
		// 自定義跳轉時間，限於 msgtype = 2
		/** 內容控制 **/
		'alert'		=> null,
		// alert 圖標樣式 right/info/error
		'return'	=> false,
		// 顯示請返回
		'redirectmsg'	=> 0,
		// 下載時用的提示信息，當跳轉時顯示的信息樣式
		// 0:如果您的瀏覽器沒有自動跳轉，請點擊此鏈接
		// 1:如果 n 秒後下載仍未開始，請點擊此鏈接
		'msgtype'	=> 1,
		// 信息樣式
		// 1:非 Ajax
		// 2:Ajax 彈出框
		// 3:Ajax 只顯示信息文本
		'showmsg'	=> true,
		// 顯示信息文本
		'showdialog'	=> false,
		// 關閉原彈出框顯示 showDialog 信息，限於 msgtype = 2
		'login'		=> false,
		// 未登錄時顯示登錄鏈接

		/** Ajax 控制 **/
		'handle'	=> false,
		// 執行 js 回調函數
		'extrajs'	=> '',
	);

	$navtitle = lang('core', 'title_board_message');

	// bluelovers
	if (sclass_exists('Scorpio_Hook')) {
		Scorpio_Hook::execute('Func_'.__FUNCTION__.':Before_custom', array(array(
			'message' => &$message,
			'url_forward' => &$url_forward,
			'values' => &$values,
			'extraparam' => &$extraparam,
			'custom' => &$custom,
			'param' => &$param,
			'navtitle' => &$navtitle,
		)));
	}
	// bluelovers

	if($custom) {
		$alerttype = 'alert_info';
		$show_message = $message;
		include template('common/showmessage');
		dexit();
	}

	define('CACHE_FORBIDDEN', TRUE);
	$_G['setting']['msgforward'] = @unserialize($_G['setting']['msgforward']);
	$handlekey = $leftmsg = '';

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

	$timedefault = intval($param['refreshtime'] === null ? $_G['setting']['msgforward']['refreshtime'] : $param['refreshtime']);
	if($param['timeout'] !== null) {
		$refreshsecond = !empty($timedefault) ? $timedefault : 3;
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

	$_G['hookscriptmessage'] = $message;
	$_G['hookscriptvalues'] = $values;
	$vars = explode(':', $message);
	if(count($vars) == 2) {
		$show_message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$show_message = lang('message', $message, $values);
	}
	if($param['msgtype'] == 2 && $param['login']) {
		dheader('location: member.php?mod=logging&action=login&handlekey='.$handlekey.'&infloat=yes&inajax=yes&guestmessage=yes');
	}
	$show_jsmessage = str_replace("'", "\\'", strip_tags($show_message));
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
				foreach ($v as $subk => $subv) {
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
			$extra .= 'if($(\'return_'.$handlekey.'\')) $(\'return_'.$handlekey.'\').className=\'onerror\';if(typeof succeedhandle_'.$handlekey.'==\'function\') {succeedhandle_'.$handlekey.'(\''.$url_forward.'\', \''.$show_jsmessage.'\', '.$valuesjs.');}';
		} else {
			$extra .= 'if(typeof errorhandle_'.$handlekey.'==\'function\') {errorhandle_'.$handlekey.'(\''.$show_jsmessage.'\', '.$valuesjs.');}';
		}
	}
	if($param['closetime'] !== null) {
		$param['closetime'] = $param['closetime'] === true ? $timedefault : $param['closetime'];
		$leftmsg = $param['closetime'].lang('message', 'showmessage_closetime');
	}
	if($param['locationtime'] !== null) {
		$param['locationtime'] = $param['locationtime'] === true ? $timedefault : $param['locationtime'];
		$leftmsg = $param['locationtime'].lang('message', 'showmessage_locationtime');
	}
	if($handlekey) {
		if($param['showdialog']) {
			$st = $param['closetime'] !== null ? 'setTimeout("hideMenu(\'fwin_dialog\', \'dialog\')", '.($param['closetime'] * 1000).');' : '';
			$st .= $param['locationtime'] !== null ?'setTimeout("window.location.href =\''.$url_forward.'\';", '.($param['locationtime'] * 1000).');' : '';
			$extra .= 'hideWindow(\''.$handlekey.'\');showDialog(\''.$show_jsmessage.'\', \'notice\', null, '.($param['locationtime'] !== null ? 'function () { window.location.href =\''.$url_forward.'\'; }' : 'null').', 0, null, \''.$leftmsg.'\');'.$st;
			$param['closetime'] = null;
			$st = '';
		}
		if($param['closetime'] !== null) {
			$extra .= 'setTimeout("hideWindow(\''.$handlekey.'\')", '.($param['closetime'] * 1000).');';
		}
	} else {
		$st = $param['locationtime'] !== null ?'setTimeout("window.location.href =\''.$url_forward.'\';", '.($param['locationtime'] * 1000).');' : '';
	}
	if(!$extra && $param['timeout']) {
		$extra .= 'setTimeout("window.location.href =\''.$url_forward.'\';", '.$refreshtime.');';
	}
	$show_message .= $extra ? '<script type="text/javascript" reload="1">'.$extra.$st.'</script>' : '';
	$show_message .= $param['extrajs'] ? $param['extrajs'] : '';

	include template('common/showmessage');
	dexit();
}

/**
 * 檢查是否正確提交了表單
 *
 * @param $var 需要檢查的變量
 * @param $allowget 是否允許GET方式
 * @param $seccodecheck 驗證碼檢測是否開啟
 * @return 返回是否正確提交了表單
 */
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
	if(strpos($mpurl, '#') !== FALSE) {
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
		$lang['prev'] = '&nbsp;&nbsp;';
		$lang['next'] = lang('core', 'nextpage');
	}

	$multipage = '';
	$mpurl .= strpos($mpurl, '?') !== FALSE ? '&amp;' : '?';

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
	$next = $num == $perpage ? '<a href="'.$mpurl.'&amp;page='.($curpage + 1).'" class="nxt">'.$lang['next'].'</a>' : '';
	$prev = $curpage > 1 ? '<span class="pgb"><a href="'.$mpurl.'&amp;page='.($curpage - 1).'">'.$lang['prev'].'</a></span>' : '';
	if($next || $prev) {
		$return = '<div class="pg">'.$prev.$next.'</div>';
	}
	return $return;
}

function censor($message, $modword = NULL, $return = FALSE) {
	require_once libfile('class/censor');
	$censor = discuz_censor::instance();
	$censor->check($message, $modword);
	if($censor->modbanned()) {
		$wordbanned = implode(', ', $censor->words_found);
		if($return) {
			return array('message' => lang('message', 'word_banned', array('wordbanned' => $wordbanned)));
		}
		showmessage('word_banned', '', array('wordbanned' => $wordbanned));
	}
	return $message;
}

function censormod($message) {
	global $_G;
	if($_G['group']['ignorecensor']) {
		return false;
	}

	require_once libfile('class/censor');
	$censor = discuz_censor::instance();
	$censor->check($message);
	return $censor->modmoderated();
}

function space_merge(&$values, $tablename) {
	global $_G;

	$uid = empty($values['uid'])?$_G['uid']:$values['uid'];
	$var = "member_{$uid}_{$tablename}";
	if($uid) {
		if(!isset($_G[$var])) {
			$query = DB::query("SELECT * FROM ".DB::table('common_member_'.$tablename)." WHERE uid='$uid'");
			if($_G[$var] = DB::fetch($query)) {
				if($tablename == 'field_home') {
					$_G['setting']['privacy'] = empty($_G['setting']['privacy']) ? array() : (is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : unserialize($_G['setting']['privacy']));
					$_G[$var]['privacy'] = empty($_G[$var]['privacy'])? array() : is_array($_G[$var]['privacy']) ? $_G[$var]['privacy'] : unserialize($_G[$var]['privacy']);
					foreach (array('feed','view','profile') as $pkey) {
						if(empty($_G[$var]['privacy'][$pkey]) && !isset($_G[$var]['privacy'][$pkey])) {
							$_G[$var]['privacy'][$pkey] = isset($_G['setting']['privacy'][$pkey]) ? $_G['setting']['privacy'][$pkey] : array();
						}
					}
					$_G[$var]['acceptemail'] = empty($_G[$var]['acceptemail'])? array() : unserialize($_G[$var]['acceptemail']);
					if(empty($_G[$var]['acceptemail'])) {
						$_G[$var]['acceptemail'] = empty($_G['setting']['acceptemail'])?array():unserialize($_G['setting']['acceptemail']);
					}
				}
			} else {
				DB::insert('common_member_'.$tablename, array('uid'=>$uid));
				$_G[$var] = array();
			}
		}
		$values = array_merge($values, $_G[$var]);
	}
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

function stripsearchkey($string) {
	$string = trim($string);
	$string = str_replace('*', '%', addcslashes($string, '%_'));
	$string = str_replace('_', '\_', $string);
	return $string;
}

function dmkdir($dir, $mode = 0777, $makeindex = TRUE){
	if(!is_dir($dir)) {
		dmkdir(dirname($dir));
		@mkdir($dir, $mode);
		if(!empty($makeindex)) {
			@touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);
		}
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

function ftpcmd($cmd, $arg1 = '') {
	static $ftp;
	$ftpon = getglobal('setting/ftp/on');
	if(!$ftpon) {
		return $cmd == 'error' ? -101 : 0;
	} elseif($ftp == null) {
		require_once libfile('class/ftp');
		$ftp = & discuz_ftp::instance();
	}
	if(!$ftp->enabled) {
		return 0;
	} elseif($ftp->enabled && !$ftp->connectid) {
		$ftp->connect();
	}
	switch ($cmd) {
		case 'upload' : return $ftp->upload(getglobal('setting/attachdir').'/'.$arg1, $arg1); break;
		case 'delete' : return $ftp->ftp_delete($arg1); break;
		case 'close'  : return $ftp->ftp_close(); break;
		case 'error'  : return $ftp->error(); break;
		case 'object' : return $ftp; break;
		default       : return false;
	}

}

function diconv($str, $in_charset, $out_charset = CHARSET, $ForceTable = FALSE) {
	global $_G;

	$in_charset = strtoupper($in_charset);
	$out_charset = strtoupper($out_charset);
	if($in_charset != $out_charset) {
		require_once libfile('class/chinese');
		$chinese = new Chinese($in_charset, $out_charset, $ForceTable);
		$strnew = $chinese->Convert($str);
		if(!$ForceTable && !$strnew && $str) {
			$chinese = new Chinese($in_charset, $out_charset, 1);
			$strnew = $chinese->Convert($str);
		}
		return $strnew;
	} else {
		return $str;
	}
}

function renum($array) {
	$newnums = $nums = array();
	foreach ($array as $id => $num) {
		$newnums[$num][] = $id;
		$nums[$num] = $num;
	}
	return array($nums, $newnums);
}

function getonlinenum($fid = 0, $tid = 0) {

	if($fid) {
		$sql = " AND fid='$fid'";
	}
	if($tid) {
		$sql = " AND tid='$tid'";
	}
	return DB::result_first('SELECT count(*) FROM '.DB::table("common_session")." WHERE 1 $sql");
}

/**
 * 顯示為易於讀取的文件單位
 **/
function sizecount($size = 0) {
	/*
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
	*/

	$i = 0;
	$sizename = array(' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
	return ($size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) : 0) . $sizename[$i];
}

function swapclass($class1, $class2 = '') {
	static $swapc = null;
	$swapc = isset($swapc) && $swapc != $class1 ? $class1 : $class2;
	return $swapc;
}

function writelog($file, $log) {
	global $_G;
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
function getcolorpalette($colorid, $id, $background, $fun = '') {
	return "<input id=\"c$colorid\" onclick=\"c{$colorid}_frame.location='static/image/admincp/getcolor.htm?c{$colorid}|{$id}|{$fun}';showMenu({'ctrlid':'c$colorid'})\" type=\"button\" class=\"colorwd\" value=\"\" style=\"background: $background\"><span id=\"c{$colorid}_menu\" style=\"display: none\"><iframe name=\"c{$colorid}_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe></span>";
}

function notification_add($touid, $type, $note, $notevars = array(), $system = 0) {
	global $_G;

	$tospace = array('uid'=>$touid);
	space_merge($tospace, 'field_home');
	$filter = empty($tospace['privacy']['filter_note'])?array():array_keys($tospace['privacy']['filter_note']);

	if($filter && (in_array($type.'|0', $filter) || in_array($type.'|'.$_G['uid'], $filter))) {
		return false;
	}

	$notevars['actor'] = "<a href=\"home.php?mod=space&uid=$_G[uid]\">".$_G['member']['username']."</a>";
	if(!is_numeric($type)) {
		$vars = explode(':', $note);
		if(count($vars) == 2) {
			$notestring = lang('plugin/'.$vars[0], $vars[1], $notevars);
		} else {
			$notestring = lang('notification', $note, $notevars);
		}
	} else {
		$notestring = $note;
	}

	$oldnote = array();
	if($notevars['from_id'] && $notevars['from_idtype']) {
		$oldnote = DB::fetch_first("SELECT * FROM ".DB::table('home_notification')."
			WHERE uid='$touid' AND from_id='$notevars[from_id]' AND from_idtype='$notevars[from_idtype]'");
	}
	if(empty($oldnote['from_num'])) $oldnote['from_num'] = 0;

	$setarr = array(
		'uid' => $touid,
		'type' => $type,
		'new' => 1,
		'authorid' => $_G['uid'],
		'author' => $_G['username'],
		'note' => addslashes($notestring),
		'dateline' => $_G['timestamp'],
		'from_id' => $notevars['from_id'],
		'from_idtype' => $notevars['from_idtype'],
		'from_num' => ($oldnote['from_num']+1)
	);
	if($system) {
		$setarr['authorid'] = 0;
		$setarr['author'] = '';
	}

	if($oldnote['id']) {
		DB::update('home_notification', $setarr, array('id'=>$oldnote['id']));
	} else {
		$oldnote['new'] = 0;
		DB::insert('home_notification', $setarr);
	}

	if(empty($oldnote['new'])) {
		DB::query("UPDATE ".DB::table('common_member_status')." SET notifications=notifications+1 WHERE uid='$touid'");
		DB::query("UPDATE ".DB::table('common_member')." SET newprompt=newprompt+1 WHERE uid='$touid'");

		require_once libfile('function/mail');
		$mail_subject = lang('notification', 'mail_to_user');
		sendmail_touser($touid, $mail_subject, $notestring, $type);
	}

	if(!$system && $_G['uid'] && $touid != $_G['uid']) {
		DB::query("UPDATE ".DB::table('home_friend')." SET num=num+1 WHERE uid='$_G[uid]' AND fuid='$touid'");
	}
}

function sendpm($toid, $subject, $message, $fromid = '') {
	global $_G;
	if($fromid === '') {
		$fromid = $_G['uid'];
	}
	loaducenter();
	uc_pm_send($fromid, $toid, $subject, $message);
}

function g_icon($groupid, $return = 0) {
	global $_G;
	if(empty($_G['cache']['usergroups'][$groupid]['icon'])) {
		$s =  '';
	} else {
		if(substr($_G['cache']['usergroups'][$groupid]['icon'], 0, 5) == 'http:') {
			$s = '<img src="'.$_G['cache']['usergroups'][$groupid]['icon'].'" align="absmiddle">';
		} else {
			$s = '<img src="'.$_G['setting']['attachurl'].'common/'.$_G['cache']['usergroups'][$groupid]['icon'].'" align="absmiddle">';
		}
	}
	if($return) {
		return $s;
	} else {
		echo $s;
	}
}
function updatediytemplate($targettplname = '') {
	global $_G;
	$r = false;
	$where = empty($targettplname) ? '' : " WHERE targettplname='$targettplname'";
	$query = DB::query("SELECT * FROM ".DB::table('common_diy_data')."$where");
	require_once libfile('function/portalcp');
	while($value = DB::fetch($query)) {
		$r = save_diy_data($value['primaltplname'], $value['targettplname'], unserialize($value['diycontent']));
	}
	return $r;
}

function space_key($uid, $appid=0) {
	global $_G;

	$siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
	return substr(md5($siteuniqueid.'|'.$uid.(empty($appid)?'':'|'.$appid)), 8, 16);
}


function getposttablebytid($tid) {
	global $_G;
	loadcache('threadtableids');
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
	if(!in_array(0, $threadtableids)) {
		$threadtableids = array_merge(array(0), $threadtableids);
	}
	foreach($threadtableids as $tableid) {
		$threadtable = $tableid ? "forum_thread_$tableid" : 'forum_thread';
		$posttableid = DB::result_first("SELECT posttableid FROM ".DB::table($threadtable)." WHERE tid='$tid'");
		if($posttableid !== false) {
			break;
		}
	}
	if(!$posttableid) {
		return 'forum_post';
	}
	return 'forum_post_'.$posttableid;
}

function getposttableid($type) {
	global $_G;
	loadcache('posttable_info');
	if($type == 'a') {
		$tabletype = 'addition';
	} else {
		$tabletype = 'primary';
	}
	if(!empty($_G['cache']['posttable_info'])) {
		foreach($_G['cache']['posttable_info'] as $key => $value) {
			if($value['type'] == $tabletype) {
				return $key;
			}
		}
	}
	return NULL;
}

function getposttable($type, $noprefix = true) {
	$tableid = getposttableid($type);
	if($type == 'a' && $tableid === NULL) {
		return NULL;
	}
	if($tableid) {
		$tablename = "forum_post_$tableid";
	} else {
		$tablename = 'forum_post';
	}

	if(!$noprefix) {
		$tablename = DB::table($tablename);
	}
	return $tablename;
}

function getcountofposts($from, $condition) {
	$ptable = getposttable('p');
	$atable = getposttable('a');

	$from_clause = str_replace(DB::table('forum_post'), DB::table($ptable), $from);
	$sum = DB::result_first("SELECT COUNT(*) FROM $from_clause WHERE $condition");
	if($atable) {
		$from_clause = str_replace(DB::table('forum_post'), DB::table($atable), $from);
		$sum += DB::result_first("SELECT COUNT(*) FROM $from_clause WHERE $condition");
	}
	return $sum;
}

function getfieldsofposts($field, $condition) {
	$ptable = getposttable('p');
	$atable = getposttable('a');

	$query = DB::query("SELECT $field FROM ".DB::table($ptable)." WHERE $condition");
	$result = array();
	while($post = DB::fetch($query)) {
		$result[] = $post;
	}
	if($atable) {
		$query = DB::query("SELECT $field FROM ".DB::table($atable)." WHERE $condition");
		while($post = DB::fetch($query)) {
			$result[] = $post;
		}
	}
	return $result;
}

function getallwithposts($sqlstruct, $onlyprimarytable = false) {
	$ptable = getposttable('p');
	$atable = getposttable('a');
	$result = array();

	$from_clause = str_replace(DB::table('forum_post'), DB::table($ptable), $sqlstruct['from']);
	$sql = "SELECT {$sqlstruct['select']} FROM $from_clause WHERE {$sqlstruct['where']}";
	$sqladd = '';
	if (!empty($sqlstruct['order'])) {
		$sqladd .= " ORDER BY {$sqlstruct['order']}";
	}
	if(!empty($sqlstruct['limit'])) {
		$sqladd .= " LIMIT {$sqlstruct['limit']}";
	}
	$sql = $sql . $sqladd;
	$query = DB::query($sql);
	while($row = DB::fetch($query)) {
		$result[] = $row;
	}

	if(!$onlyprimarytable && $atable !== NULL) {
		$from_clause = str_replace(DB::table('forum_post'), DB::table($atable), $sqlstruct['from']);
		$sql = "SELECT {$sqlstruct['select']} FROM $from_clause WHERE {$sqlstruct['where']}";
		$sql = $sql . $sqladd;

		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$result[] = $row;
		}
	}
	return $result;
}

function insertpost($data) {
	if(isset($data['tid'])) {
		$tableid = DB::result_first("SELECT posttableid FROM ".DB::table('forum_thread')." WHERE tid='{$data['tid']}'");
	} else {
		$tableid = getposttableid('p');
		$data['tid'] = 0;
	}
	$pid = DB::insert('forum_post_tableid', array('pid' => null), true);

	if(!$tableid) {
		$tablename = 'forum_post';
	} else {
		$tablename = "forum_post_$tableid";
	}

	$data = array_merge($data, array('pid' => $pid));

	DB::insert($tablename, $data);
	if($pid % 1024 == 0) {
		DB::delete('forum_post_tableid', "pid<$pid");
	}
	save_syscache('max_post_id', $pid);
	return $pid;
}

function updatepost($data, $condition, $unbuffered = false) {
	global $_G;
	loadcache('posttableids');
	$affected_rows = 0;
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	foreach($posttableids as $id) {
		if($id == 0) {
			DB::update('forum_post', $data, $condition, $unbuffered);
		} else {
			DB::update("forum_post_$id", $data, $condition, $unbuffered);
		}
		$affected_rows += DB::affected_rows();
	}
	return $affected_rows;
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
//	return preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($accesslist, '/')).")/", $ip);
	return preg_match("/^(".str_replace(array("\r\n", ' ', "\n"), array('|', '', '|'), preg_quote($accesslist, '/')).")/", $ip);
}

function ipbanned($onlineip) {
	global $_G;

	if($_G['setting']['ipaccess'] && !ipaccess($onlineip, $_G['setting']['ipaccess'])) {
		return TRUE;
	}

	loadcache('ipbanned');
	if(empty($_G['cache']['ipbanned'])) {
		return FALSE;
	} else {
		if($_G['cache']['ipbanned']['expiration'] < TIMESTAMP) {
			require_once libfile('function/cache');
			updatecache('ipbanned');
		}
		return preg_match("/^(".$_G['cache']['ipbanned']['regexp'].")$/", $onlineip);
	}
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

function forumperm($permstr, $groupid = 0) {
	global $_G;

	$groupidarray = array($_G['groupid']);
	if($groupid) {
		return preg_match("/(^|\t)(".$groupid.")(\t|$)/", $permstr);
	}
	foreach(explode("\t", $_G['member']['extgroupids']) as $extgroupid) {
		if($extgroupid = intval(trim($extgroupid))) {
			$groupidarray[] = $extgroupid;
		}
	}
	if($_G['setting']['verify']['enabled']) {
		getuserprofile('verify1');
		for($i = 1; $i < 6; $i++) {
			if($_G['member']['verify'.$i] == 1) {
				$groupidarray[] = 'v'.$i;
			}
		}
	}
	return preg_match("/(^|\t)(".implode('|', $groupidarray).")(\t|$)/", $permstr);
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

function checkperm($perm) {
	global $_G;
	return (empty($_G['group'][$perm])?'':$_G['group'][$perm]);
}

function periodscheck($periods, $showmessage = 1) {
	global $_G;

	if(!$_G['group']['disableperiodctrl'] && $_G['setting'][$periods]) {
		$now = dgmdate(TIMESTAMP, 'G.i');
//		foreach(explode("\r\n", str_replace(':', '.', $_G['setting'][$periods])) as $period) {
//		}
		foreach(preg_split('/(\r\n|\n)/', str_replace(':', '.', $_G['setting'][$periods])) as $period) {
			list($periodbegin, $periodend) = explode('-', $period);
			if(($periodbegin > $periodend && ($now >= $periodbegin || $now < $periodend)) || ($periodbegin < $periodend && $now >= $periodbegin && $now < $periodend)) {
//				$banperiods = str_replace("\r\n", ', ', $_G['setting'][$periods]);
				$banperiods = str_replace(array("\r\n", "\n"), ', ', $_G['setting'][$periods]);
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

function cknewuser($return=0) {
	global $_G;

	$result = true;

	if(!$_G['uid']) return true;

	if(checkperm('disablepostctrl')) {
		return $result;
	}
	$ckuser = $_G['member'];

	if($_G['setting']['newbiespan'] && $_G['timestamp']-$ckuser['regdate']<$_G['setting']['newbiespan']*60) {
		if(empty($return)) showmessage('no_privilege_newbiespan', '', array('newbiespan' => $_G['setting']['newbiespan']), array('return' => true));
		$result = false;
	}
	if($_G['setting']['need_avatar'] && empty($ckuser['avatarstatus'])) {
		if(empty($return)) showmessage('no_privilege_avatar', '', array(), array('return' => true));
		$result = false;
	}
	if($_G['setting']['need_email'] && empty($ckuser['emailstatus'])) {
		if(empty($return)) showmessage('no_privilege_email', '', array(), array('return' => true));
		$result = false;
	}
	if($_G['setting']['need_friendnum']) {
		space_merge($ckuser, 'count');
		if($ckuser['friends'] < $_G['setting']['need_friendnum']) {
			if(empty($return)) showmessage('no_privilege_friendnum', '', array('friendnum' => $_G['setting']['need_friendnum']), array('return' => true));
			$result = false;
		}
	}
	return $result;
}

function manyoulog($logtype, $uids, $action, $fid = '') {
	global $_G;

	$action = daddslashes($action);
	if($logtype == 'user') {
		$values = array();
		$uids = is_array($uids) ? $uids : array($uids);
		foreach($uids as $uid) {
			$uid = intval($uid);
			$values[$uid] = "('$uid', '$action', '".TIMESTAMP."')";
		}
		if($values) {
			DB::query("REPLACE INTO ".DB::table('common_member_log')." (`uid`, `action`, `dateline`) VALUES ".implode(',', $values));
		}
	}
}

function getuserapp($panel = 0) {
	require_once libfile('function/manyou');
	manyou_getuserapp($panel);
	return true;
}

function getmyappiconpath($appid, $iconstatus=0) {
	if($iconstatus > 0) {
		return getglobal('setting/attachurl').'./'.'myapp/icon/'.$appid.'.jpg';
	}
	return 'http://appicon.manyou.com/icons/'.$appid;
}

function getexpiration() {
	global $_G;
	$date = getdate($_G['timestamp']);
	return mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']) + 86400;
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

?>