<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: common.func.php 4490 2010-09-15 09:31:46Z bihuizi $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

function lang($file, $langvar = null, $vars = array()) {
	global $_G, $lang;

	list($path, $file) = explode('/', $file);
	if(!$file) {
		return isset($lang[$path]) ? $lang[$path] : ($langvar === null ? $path : '');
	} else {
		$key = $path == '' ? $file : $path.'_'.$file;
		if(!isset($_G['lang'][$key])) {
			include B_ROOT.'./language/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';
			$_G['lang'][$key] = $lang;
		}

		$returnvalue = &$_G['lang'];
	}
	
	$return = $langvar !== null ? (!empty($returnvalue[$key][$langvar]) ? $returnvalue[$key][$langvar] : '') : $returnvalue[$key];
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

function error($message, $vars = array(), $return = false) {
	global $_G, $lang;
	$message = str_replace(array_keys($vars), $vars, lang($message));

	//discuz_core::error_log($message);
	if(!$return) {
		global $_G;
		@header('Content-Type: text/html; charset='.$_G['config']['output']['charset']);
		exit($message);
	} else {
		return $message;
	}
}

function saddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$newkey = addslashes(strip_tags($key));
			if($newkey != $key) {
				unset($string[$key]);
			}
			$string[$newkey] = saddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function parseparameter($param, $nofix=1) {
	global $_G;

	$paramarr = array();

	if($nofix && !empty($_G['setting']['pagepostfix'])) {
		if(strrpos($param, $_G['setting']['pagepostfix'])) {
			$param = substr($param, 0, strrpos($param, $_G['setting']['pagepostfix']));
		}
	}

	$sarr = explode('/', $param);
	if(empty($sarr)) return $paramarr;
	if(is_numeric($sarr[0])) $sarr = array_merge(array('uid'), $sarr);
	if(count($sarr)%2 != 0) $sarr = array_slice($sarr, 0, -1);
	for($i=0; $i<count($sarr); $i=$i+2) {
		if(!empty($sarr[$i+1])) $paramarr[$sarr[$i]] = addslashes(str_replace(array('/', '\\'), '', rawurldecode(stripslashes($sarr[$i+1]))));
	}
	return $paramarr;
}

function arraytostring($array, $dot='/', $dot2='/') {
	$result = $comma = '';
	foreach ($array as $key => $value) {
		$value = trim($value);
		if($value != '') {
			$result .= $comma.$key.$dot.rawurlencode($value);
			$comma = $dot2;
		}
	}
	return $result;
}

//將數組加上單引號,並整理成串
function simplode($sarr, $comma=',') {
	return '\''.implode('\''.$comma.'\'', $sarr).'\'';
}

function gethtmlfile($parray) {

	$htmlarr = array();
	$dirarr = array();
	$id = 0;

	if(empty($parray['page'])) {
		unset($parray['page']);
	} elseif($parray['page'] < 2) {
		unset($parray['page']);
	}
	if(!empty($parray['uid'])) {
		$id = $parray['uid'];
		if(!empty($parray['action'])) {
			if($parray['action'] == 'space' || $parray['action'] == 'spacelist') {
				unset($parray['action']);
			} elseif ($parray['action'] == 'viewspace') {
				unset($parray['action']);
			}
		}
	} elseif(!empty($parray['itemid'])) {
		$id = $parray['itemid'];
	} elseif(!empty($parray['tid'])) {
		$id = $parray['tid'];
	} elseif(!empty($parray['tagid'])) {
		$id = $parray['tagid'];
	} elseif(!empty($parray['catid'])) {
		$id = $parray['catid'];
	} elseif(!empty($parray['fid'])) {
		$id = $parray['fid'];
	}

	$htmlfilename = str_replace(array('action-', 'uid-', 'itemid-'), array('', '', ''), arraytostring($parray, '-'));
	if(!empty($id)) {
		$idvalue = ($id>9)?substr($id, -2, 2):$id;
		$thedir = $idvalue;
		if(!empty($parray['action'])) {
			if($parray['action'] == 'viewnews') {
				$htmlfilename = "n-{$id}";
				if(!empty($parray['page'])) $htmlfilename .= '-'.$parray['page'];
			} elseif($parray['action'] == 'viewthread') {
				$htmlfilename = "t-{$id}";
			}
		}
	}

	if(is_dir(H_DIR) || (!is_dir(H_DIR) && @mkdir(H_DIR))) {
		if(empty($id)) {
			$htmlarr['path'] = H_DIR.'/'.$htmlfilename.'.html';
			$htmlarr['url'] = H_URL.'/'.$htmlfilename.'.html';
		} else {
			$htmldir = H_DIR.'/'.$thedir;
			if(is_dir($htmldir) || (!is_dir($htmldir) && @mkdir($htmldir))) {
				$htmlarr['path'] = H_DIR.'/'.$thedir.'/'.$htmlfilename.'.html';
				$htmlarr['url'] = H_URL.'/'.$thedir.'/'.$htmlfilename.'.html';
			} else {
				$htmlarr['path'] = H_DIR.'/'.$htmlfilename.'.html';
				$htmlarr['url'] = H_URL.'/'.$htmlfilename.'.html';
			}
		}
	} else {
		$htmlarr['path'] = $htmlfilename.'.html';
		$htmlarr['url'] = $htmlfilename.'.html';
	}

	return $htmlarr;
}

function geturl($pstring, $urlmode=0) {

	global $_G, $_SGLOBAL, $spaceself;

	//生成HTML
	if(defined('CREATEHTML')) {
		$theurl = gethtmlurl($pstring);
		if(!empty($theurl)) {
			return $theurl;
		}
	}

	//URL緩存
	$cachekey = $pstring.$urlmode;
	if(empty($_SGLOBAL['url_cache'])) $_SGLOBAL['url_cache'] = array();
	if(!empty($_SGLOBAL['url_cache'][$cachekey])) {
		return $_SGLOBAL['url_cache'][$cachekey];
	}

	//url結果
	$theurl = '';

	//強制php模式
	$isphp = !empty($spaceself)?1:strexists($pstring, 'php/1');

	//首頁鏈接
	if($pstring == 'action/index') $pstring = '';

	//搜索友好模式
	if(!empty($_G['setting']['htmlmode']) && $_G['setting']['htmlmode'] == 2 && !$isphp && $urlmode != 1) {
		$htmlarr = array('uid'=>'', 'action'=>'', 'catid'=>'', 'fid'=>'', 'tagid'=>'', 'itemid'=>'', 'tid'=>'', 'type'=>'', 'view'=>'', 'mode'=>'', 'showpro'=>'', 'itemtypeid'=>'', 'page'=>'');
		$sarr = explode('/', $pstring);

		if(empty($sarr)) $sarr = array('action'=>'index');

		$htmlurlcheck = true;
		for($i=0; $i<count($sarr); $i=$i+2) {
			if(!empty($sarr[$i+1])) {
				if(key_exists($sarr[$i], $htmlarr)) {
					$htmlarr[$sarr[$i]] = addslashes(str_replace(array('/', '\\'), '', rawurldecode(stripslashes($sarr[$i+1]))));
				} else {
					$htmlurlcheck = false;
					break;
				}
			}
		}
		if($htmlurlcheck) {
			$htmls = gethtmlfile($htmlarr);
			if(file_exists($htmls['path'])) {
				$theurl = $htmls['url'];
			}
		}
	}

	//普通模式
	if(empty($theurl)) {
		if(empty($pstring)) {
			if($urlmode == 1) {
				$theurl = B_URL_ALL;
			} else {
				$theurl = B_URL;
			}
		} else {
			$pre = '';
			$para = str_replace('/', '-', $pstring);
			if($isphp || defined('S_ISPHP')) {
				$pre = '/index.php?';
			} else {
				if ($_G['setting']['urltype'] == 5) {
					$pre = '/index.php/';
				} else {
					$pre = '/?';
				}
			}
			if(empty($para)) $pre = '/';

			if($urlmode == 1) {
				//全部路徑
				$theurl = B_URL_ALL.$pre.$para;
			} elseif($urlmode == 2) {
				//處理
				$theurl = B_URL.$pre.$para;
				$theurl = url_remake($theurl);
			} else {
				//常規
				$theurl = B_URL.$pre.$para;
			}
		}
	}

	//url緩存
	$_SGLOBAL['url_cache'][$cachekey] = $theurl;

	return $theurl;
}

function ob_out() {
	global $_G, $_SGLOBAL, $_SC;

	$_SGLOBAL['content'] = ob_get_contents();

	$preg_searchs = $preg_replaces = $str_searchs = $str_replaces = array();

	if($_G['setting']['urltype'] != 4 && $_G['setting']['urltype'] != 5) {
		$preg_searchs[] = "/href\=\"(\S*?)\/(index\.php)?\?uid\-([0-9]+)\-?(\S*?)\"/i";
		$preg_replaces[] = 'href="\\1/?\\3/\\4"';
		$preg_searchs[] = "/href\=\"\S*?\/(index\.php)?\?(\S+?)\"/ie";
		$preg_replaces[] = "url_replace('\\2')";
		$preg_searchs[] = "@\"(".B_URL."/)?store\.php\?id=(\d+)(&action=(\w+)(&xid=(\d+))?)?\"@is";
		$preg_replaces[] = '"'.B_URL.'/store-\\2-\\4-\\6S-H-O-P-E-N-D.html"';
		$preg_searchs[] = "@\"(".B_URL."/)?street\.php\?catid=(\d+)(&tagid=(\d+))?\"@is";
		$preg_replaces[] = '"'.B_URL.'/street-\\2-\\4T-A-G-E-N-D.html"';
		$str_searchs[] = '--S-H-O-P-E-N-D';
		$str_replaces[] = '';
		$str_searchs[] = '-S-H-O-P-E-N-D';
		$str_replaces[] = '';
		$str_searchs[] = 'S-H-O-P-E-N-D';
		$str_replaces[] = '';
		$str_searchs[] = '-T-A-G-E-N-D';
		$str_replaces[] = '';
		$str_searchs[] = 'T-A-G-E-N-D';
		$str_replaces[] = '';
	}

	if($preg_searchs) {
		$_SGLOBAL['content'] = preg_replace($preg_searchs, $preg_replaces, $_SGLOBAL['content']);
	}
	if($str_searchs) {
		$_SGLOBAL['content'] = trim(str_replace($str_searchs, $str_replaces, $_SGLOBAL['content']));
	}

	obclean();
	if(!$_G['inajax']) {
		if($_G['setting']['headercharset']) {
			@header('Content-Type: text/html; charset='.$_G['charset']);
		}
		echo $_SGLOBAL['content'];
		if(D_BUG && !defined('CREATEHTML')) {
			@include_once(B_ROOT.'./source/include/debug.inc.php');
		}
	}
}

function url_replace($para, $quote=1) {
	global $_G;

	$para = str_replace(
	array(
			'action-viewnews-itemid',
			'action-viewthread-tid',
			'action-category-catid',
			'action-index'
			),
			array(
			'viewnews',
			'viewthread',
			'category',
			''
			),
			$para
			);

			if($_G['setting']['urltype'] == 3) {
				$pre = '/';
			} elseif($_G['setting']['urltype'] == 2) {
				$pre = '/index.php/';
			} else {
				$pre = '/?';
			}

			if(empty($para)) {
				$para = '/';
			} elseif(substr($para, -1, 1) == '/' || $_G['setting']['urltype'] == 3) {
				$para = $pre.$para;
				if($_G['setting']['urltype'] == 3 && substr($para, -1, 1) != '/') {
					$para .= $_G['setting']['pagepostfix'];
				}
			} else {
				$para = $pre.$para.$_G['setting']['pagepostfix'];
			}

			return empty($quote)?B_URL.$para:'href="'.B_URL.$para.'"';
}

function url_remake($url) {
	$url = preg_replace("/(\S*)\/(index\.php)?\?uid\-([0-9]+)\-?(\S*)/i", '\\1/?\\3/\\4', $url);
	$url = preg_replace("/\S*\/(index\.php)?\?(\S+)/ie", "url_replace('\\2', 0)", $url);
	return $url;
}

function sgmdate($timestamp, $dateformat='', $format=0) {
	global $_G, $_SGLOBAL, $lang;

	if(empty($dateformat)) {
		$dateformat = 'Y-m-d H:i:s';
	}

	if(empty($timestamp)) {
		$timestamp = $_G['timestamp'];
	}

	$result = '';
	if($format) {
		$time = $_G['timestamp'] - $timestamp;
		if($time > 24*3600) {
			$result = gmdate($dateformat, $timestamp + $_G['setting']['timeoffset'] * 3600);
		} elseif ($time > 3600) {
			$result = intval($time/3600).$lang['hour'].$lang['before'];
		} elseif ($time > 60) {
			$result = intval($time/60).$lang['minute'].$lang['before'];
		} elseif ($time > 0) {
			$result = $time.$lang['second'].$lang['before'];
		} else {
			$result = $lang['now'];
		}
	} else {
		$result = gmdate($dateformat, $timestamp + $_G['setting']['timeoffset'] * 3600);
	}
	return $result;
}

//獲得表
function tname($name, $mode=0) {
	global $_G, $_SC, $lang;

	$name = saddslashes($name);
	if(!in_array($name, array('adminsession', 'albumitems', 'attachments', 'attribute', 'attrvalue', 'attrvalue_text', 'blocks', 'brandlinks', 'cache', 'cachenotes', 'categories', 'commentmodels', 'commentscores', 'consumeitems', 'consumemessage', 'crons', 'data', 'gooditems', 'goodmessage', 'goodrelated', 'groupbuyitems', 'groupbuyjoin', 'groupbuymessage', 'itemattr', 'itemattribute', 'itemupdates', 'managelog', 'members', 'modelcolumns', 'models', 'nav', 'noticeitems', 'noticemessage', 'photoitems', 'relatedinfo', 'reportlog', 'reportreasons', 'scorestats', 'settings', 'shopgroup', 'shopitems', 'shopmessage', 'shopupdate', 'spacecomments', 'admincp_group', 'admincp_member', 'admincp_perm'))) {
		if(!preg_match("/^cache_[a-f0-9]+$/", $name)) {
			exit($name.$lang['select_database_error']);
		}
	}
	return $_G['config']['db'][1]['tablepre'].$name;
}

function authcode($string, $operation, $key = '', $expiry = 0) {
	global $_G, $_SGLOBAL;

	$ckey_length = 4;	// 隨機密鑰長度 取值 0-32;
	// 加入隨機密鑰，可以令密文無任何規律，即便是原文和密鑰完全相同，加密結果也會每次不同，增大破解難度。
	// 取值越大，密文變動規律越大，密文變化 = 16 的 $ckey_length 次方
	// 當此值為 0 時，則不產生隨機密鑰

	$key = md5($key ? $key : $_G['authkey']);
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

/**
 * 從 Cookie 中取出登錄信息
 */
function getcookie() {
	global $_G, $_SGLOBAL, $_SCET;

	$_G['uid'] = 0;
	$_G['username'] = 'Guest';
	$_G['member'] = $_G['member'] = array(
		'uid' => 0,
		'groupid' => 2,
		'username' => 'Guest',
		'password' => ''
	);
	$cookie = $_G['cookie']['auth'];
	if($cookie) {
		@list($password, $uid) = explode("\t", authcode($cookie, 'DECODE'));

		$uid = intval($uid);
		$password = addslashes($password);
		$_G['uid'] = $uid;
		$query = DB::query('SELECT * FROM '.tname('members').' WHERE uid=\''.$_G['uid'].'\' AND password=\''.$password.'\'');
		if($member = DB::fetch($query)) {
			$_G['member'] = $_G['member'] = $member;
			$_G['username'] = addslashes($member['username']);
			$_G['email'] = addslashes($member['email']);
			$_G['myshopid'] = $member['myshopid'];
			$_G['member']['shopcount'] = 0;

			if ($_G['myshopid'] > 0) {
				require_once B_ROOT."./source/class/shop.class.php";
				$_G['myshops'] = shop::ls_myshops();
				$_G['member']['shopcount'] = count($_G['myshops']);
			}
		} else {
			$_G['uid'] = 0;
		}
	}
	if(empty($_G['uid'])) sclearcookie();
	if(empty($_G['member']['timeoffset'])) $_G['member']['timeoffset'] = $_G['member']['timeoffset'] = $_G['setting']['timeoffset'];
}

//數組轉換成字串
function arrayeval($array, $level = 0) {
	$space = '';
	$evaluate = "Array $space(";
	$comma = $space;
	foreach($array as $key => $val) {
		$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
		$val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12) ? addcslashes($val, '\'\\') : $val;
		if(is_array($val)) {
			$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
		} else {
			$evaluate .= "$comma$key => '$val'";
		}
		$comma = ",$space";
	}
	$evaluate .= "$space)";
	return $evaluate;
}

function sclearcookie() {
	ssetcookie('sid', '', -86400 * 365);
	ssetcookie('auth', '', -86400 * 365);
	ssetcookie('sauth', '', -86400 * 365);
}

function ssetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {
	global $_G;

	$config = $_G['config']['cookie'];

	$_G['cookie'][$var] = $value;
	$var = ($prefix ? $config['cookiepre'] : '').$var;
	$_COOKIE[$var] = $value;

	if($value == '' || $life < 0) {
		$value = '';
		$life = -1;
	}

	$life = $life > 0 ? $_G['timestamp'] + $life : ($life < 0 ? $_G['timestamp'] - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'].'; HttpOnly' : $config['cookiepath'];

	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	if(PHP_VERSION < '5.2.0') {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure);
	} else {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
	}
}

/**
 * 連接數據庫
 */
function dbconnect($mode=0) {
	global $_G, $_SGLOBAL, $_SC;

	$__SC['db'][1] = $_SC;
	if(empty($_G['db'])) {
		include_once(B_ROOT.'./source/class/db.class.php');
		$_G['db'] = DB::object();
		$_G['db']->set_config($__SC['db']);
		$_G['db']->connect();
	}
}

function stripsearchkey($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = stripsearchkey($val);
		}
	} else {
		$string = trim($string);
		$string = str_replace('*', '%', addcslashes($string, '%_'));
		$string = str_replace('_', '\_', $string);
	}
	return $string;
}

function postget($var) {
	$value = '';
	if(isset($_POST[$var])) {
		$value = $_POST[$var];
	} elseif (isset($_GET[$var])) {
		$value = $_GET[$var];
	}
	return $value;
}

function obclean() {
	global $_G;

	ob_end_clean();
	if ($_G['setting']['gzipcompress'] && function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}

function showxml($text, $title='') {
	global $_G, $lang;

	if(!empty($title)) {
		$text = '<h5><a href="javascript:;" onclick="document.getElementById(\'xspace-ajax-div\').style.display=\'none\';">'.$lang['close'].'</a>'.$title.'</h5><div class="xspace-ajaxcontent">'.$text.'</div>';
	}
	obclean();
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", false);
	@header("Pragma: no-cache");
	header("Content-type: application/xml");
	echo "<?xml version=\"1.0\" encoding=\"$_G[setting][charset]\"?>\n";
	echo "<root><![CDATA[";
	echo $text;
	echo "]]></root>";
	exit;
}

//顯示信息
function showmessage($message, $url_forward='', $second=3, $vars=array(), $checkresults = array()) {
	global $_G, $_SGLOBAL, $lang, $mlang, $_SC, $_SSCONFIG;

	if($message == "requirefiled_not_complate") {
		$error_details = "<ul>";
		foreach($checkresults as $key => $value) {
			foreach ($value as $k => $v) {
				$error_details .= '<li>'.$v.'</li>';
			}
		}
		$error_details .= "</ul>";
	}

	include_once(B_ROOT.'./language/message.lang.php');
	$message = ($mlang[$message]?$mlang[$message]:($_SGLOBAL['mlang'][$message] ? $_SGLOBAL['mlang'][$message] : $message));
	foreach ($vars as $key => $val) {
		$message = str_replace('{'.$key.'}', $val, $message);
	}
	if($_G['inajax'] == 1) {
		showxmlheader($_G['charset']);
		echo '<root>';
		if(!empty($checkresults)) {
			echo '<status>FAILED</status>';
			foreach($checkresults as $error) {
				echo showarraytoxml($error, $_G['charset'], 1);
			}
		} else {
			echo '<status>OK</status>';
			echo '<url><![CDATA['.$url_forward.']]></url>';
		}
		echo '<message><![CDATA['.$message.']]></message>';
		echo '</root>';
	} else {
		if($url_forward && empty($second)) {
			//直接301跳轉
			obclean();
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: $url_forward");
		} else {
			$tpl_file = 'templates/site/default/showmessage.html.php';
			$fullpath = 1;
			//顯示
			obclean();
			if(!empty($url_forward)) {
				$second = $second * 1000;
				$message .= "<script>setTimeout(\"window.location.href ='$url_forward';\", $second);</script><ajaxok>";
			}

			include template($tpl_file, $fullpath);
			ob_out();
		}
	}
	exit();
}

function secho($array, $eixt=1) {
	if(is_array($array)) {
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	} else {
		echo '<br>';
		echo shtmlspecialchars($array);
		echo '<br>';
	}
	if($eixt) exit();
}

function submitcheck($var, $checksec=0) {
	global $_G, $_SGLOBAL;

	if(!empty($_POST[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if((empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) && $_POST['formhash'] == formhash()) {
			if($_G['setting']['seccode'] && $checksec) {
				if(!empty($_POST['seccode'])) {
					if(ckseccode($_POST['seccode'])) {
						return true;
					}
					showmessage('incorrect_code');
				}
				return false;
			} else {
				return true;
			}
		} else {
			showmessage('submit_invalid');
		}
	} elseif(!empty($_GET[$var])) {
		if((empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) && $_GET['formhash'] == formhash()) {
			if($_G['setting']['seccode'] && $checksec) {
				if(!empty($_GET['seccode'])) {
					if(ckseccode($_GET['seccode'])) {
						return true;
					}
					showmessage('incorrect_code');
				}
				return false;
			} else {
				return true;
			}
		} else {
			showmessage('submit_invalid');
		}
	} else {
		return false;
	}
}

function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === false);
}

function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

function sheader($url){
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $url");
	exit();
}

function replacetable($tablename, $insertsqlarr) {
	global $_G, $_SGLOBAL;

	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ($insertsqlarr as $insert_key => $insert_value) {
		$insertkeysql .= $comma.$insert_key;
		$insertvaluesql .= $comma.'\''.$insert_value.'\'';
		$comma = ', ';
	}
	DB::query('REPLACE INTO '.tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.') ');
}

//簡單跳轉的函數
function jumpurl($url, $time=1000, $mode='js') {
	if($mode == 'js') {
		echo "<script>
			function redirect() {
				window.location.replace('$url');
			}
			setTimeout('redirect();', $time);
			</script>";
	} else {
		$time = $time/1000;
		echo "<html><head><title></title><meta http-equiv=\"refresh\" content=\"$time;url=$url\"></head><body></body></html>";
	}
	exit;
}

//獲取文件名後綴
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

function censor($message, $mod=0) {
	global $_G, $_SGLOBAL;
	@include_once(B_ROOT.'/data/system/censor.cache.php');
	if(!empty($_SGLOBAL['censor']) && is_array($_SGLOBAL['censor'])) {
		if($mod == 0) {
			if($_SGLOBAL['censor']['banned'] && preg_match($_SGLOBAL['censor']['banned'], $message)) {
				showmessage('words_can_not_publish_the_shield');
			}
			if($_SGLOBAL['censor']['mod'] && preg_match($_SGLOBAL['censor']['mod'], $message)) {
				showmessage('words_can_not_publish_the_shield');
			}
		} else {
			if(!empty($_SGLOBAL['censor']['banned'])) {
				$message = @preg_replace($_SGLOBAL['censor']['banned'], '**', $message);
			}

			if(!empty($_SGLOBAL['censor']['mod'])) {
				$message = @preg_replace($_SGLOBAL['censor']['mod'], '**', $message);
			}
		}

		if(!empty($_SGLOBAL['censor']['filter'])) {
			$message = @preg_replace($_SGLOBAL['censor']['filter']['find'], $_SGLOBAL['censor']['filter']['replace'], $message);
		}

	}
	return $message;
}

//讀文件
function sreadfile($filename, $mode='r', $remote=0, $maxsize=0, $jumpnum=0) {
	if($jumpnum > 5) return '';
	$contents = '';

	if($remote) {
		$httpstas = '';
		$urls = initurl($filename);
		if(empty($urls['url'])) return '';

		$fp = @fsockopen($urls['host'], $urls['port'], $errno, $errstr, 20);
		if($fp) {
			if(!empty($urls['query'])) {
				fputs($fp, "GET $urls[path]?$urls[query] HTTP/1.1\r\n");
			} else {
				fputs($fp, "GET $urls[path] HTTP/1.1\r\n");
			}
			fputs($fp, "Host: $urls[host]\r\n");
			fputs($fp, "Accept: */*\r\n");
			fputs($fp, "Referer: $urls[url]\r\n");
			fputs($fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 5.00; Windows 98)\r\n");
			fputs($fp, "Pragma: no-cache\r\n");
			fputs($fp, "Cache-Control: no-cache\r\n");
			fputs($fp, "Connection: Close\r\n\r\n");

			$httpstas = explode(" ", fgets($fp, 128));
			if($httpstas[1] == 302 || $httpstas[1] == 302) {
				$jumpurl = explode(" ", fgets($fp, 128));
				return sreadfile(trim($jumpurl[1]), 'r', 1, 0, ++$jumpnum);
			} elseif($httpstas[1] != 200) {
				fclose($fp);
				return '';
			}

			$length = 0;
			$size = 1024;
			while (!feof($fp)) {
				$line = trim(fgets($fp, 128));
				$size = $size + 128;
				if(empty($line)) break;
				if(strexists($line, 'Content-Length')) {
					$length = intval(trim(str_replace('Content-Length:', '', $line)));
					if(!empty($maxsize) && $length > $maxsize) {
						fclose($fp);
						return '';
					}
				}
				if(!empty($maxsize) && $size > $maxsize) {
					fclose($fp);
					return '';
				}
			}
			fclose($fp);

			if(@$handle = fopen($urls['url'], $mode)) {
				if(function_exists('stream_get_contents')) {
					$contents = stream_get_contents($handle);
				} else {
					$contents = '';
					while (!feof($handle)) {
						$contents .= fread($handle, 8192);
					}
				}
				fclose($handle);
			} elseif(@$ch = curl_init()) {
				curl_setopt($ch, CURLOPT_URL, $urls['url']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);//timeout
				$contents = curl_exec($ch);
				curl_close($ch);
			} else {
				//無法遠程上傳
			}
		}
	} else {
		if(@$handle = fopen($filename, $mode)) {
			$contents = fread($handle, filesize($filename));
			fclose($handle);
		}
	}

	return $contents;
}

//寫文件
function writefile($filename, $writetext, $filemod='text', $openmod='w', $eixt=1) {
	if(!@$fp = fopen($filename, $openmod)) {
		if($eixt) {
			exit('File :<br>'.srealpath($filename).'<br>Have no access to write!');
		} else {
			return false;
		}
	} else {
		$text = '';
		if($filemod == 'php') {
			$text = "<?php\r\n\r\nif(!defined('IN_BRAND')) exit('Access Denied');\r\n\r\n";
		}
		$text .= $writetext;
		if($filemod == 'php') {
			$text .= "; \r\n\r\n?>";
		}
		flock($fp, 2);
		fwrite($fp, $text);
		fclose($fp);
		return true;
	}
}

function initurl($url) {

	$newurl = '';
	$blanks = array('url'=>'');
	$urls = $blanks;

	if(strlen($url)<10) return $blanks;
	$urls = @parse_url($url);
	if(empty($urls) || !is_array($urls)) return $blanks;
	if(empty($urls['scheme'])) return $blanks;
	if($urls['scheme'] == 'file') return $blanks;

	if(empty($urls['path'])) $urls['path'] = '/';
	$newurl .= $urls['scheme'].'://';
	$newurl .= empty($urls['user'])?'':$urls['user'];
	$newurl .= empty($urls['pass'])?'':':'.$urls['pass'];
	$newurl .= empty($urls['host'])?'':((!empty($urls['user']) || !empty($urls['pass']))?'@':'').$urls['host'];
	$newurl .= empty($urls['port'])?'':':'.$urls['port'];
	$newurl .= empty($urls['path'])?'':$urls['path'];
	$newurl .= empty($urls['query'])?'':'?'.$urls['query'];
	$newurl .= empty($urls['fragment'])?'':'#'.$urls['fragment'];

	$urls['port'] = empty($urls['port'])?'80':$urls['port'];
	$urls['url'] = $newurl;

	return $urls;
}

//編譯模板文件
function template($tplfile, $fullpath=0) {
	global $_G;

	if(empty($fullpath)) {
		$filename = 'templates/'.$_G['setting']['template'].'/'.$tplfile.'.html.php';
		$objfile = B_ROOT.'./data/cache/tpl/tpl_'.$_G['setting']['template'].'_'.$tplfile.'.php';
		$tplfile = B_ROOT.'./'.$filename;
	} else {
		$filename = $tplfile;
		$objfile = str_replace('/', '_', $filename);
		$objfile = B_ROOT.'./data/cache/tpl/tpl_'.$objfile.'.php';
		$tplfile = B_ROOT.'./'.$filename;
	}

	$tplrefresh = 1;
	if(file_exists($objfile)) {
		if(empty($_G['setting']['tplrefresh'])) {
			$tplrefresh = 0;
		} else {
			if(@filemtime($tplfile) <= @filemtime($objfile)) {
				$tplrefresh = 0;
			}
		}
	}

	if($tplrefresh) {
		include_once(B_ROOT.'./source/function/template.func.php');
		parse_template($tplfile, $objfile);
	}

	return $objfile;
}

//格式化路徑
function srealpath($path) {
	$path = str_replace('./', '', $path);
	if(DIRECTORY_SEPARATOR == '\\') {
		$path = str_replace('/', '\\', $path);
	} elseif(DIRECTORY_SEPARATOR == '/') {
		$path = str_replace('\\', '/', $path);
	}
	return $path;
}

//模塊
function block($thekey, $param) {
	global $_G, $_SGLOBAL, $_SBLOCK, $lang, $_BCACHE;

	$havethekey = false;
	$needcache = $multineedcache = 0;

	$block_func = 'block_'.$thekey;
	if(!function_exists($block_func)) {
		$block_func = 'block_sql';
		$thekey = 'sql';
	}
	$_SBLOCK[$thekey] = array();

	//緩存key
	$cachekey = $multicachekey = smd5($thekey.$param);

	$paramarr = parseparameter($param, 0);
	if(!empty($paramarr['uid'])) {
		$uid = $paramarr['uid'];
	} elseif (!empty($paramarr['authorid'])) {
		$uid = $paramarr['authorid'];
	} else {
		$uid = 0;
	}

	if(!empty($paramarr['perpage']) && !empty($_G['page'])) {
		//分頁
		$cachekey = smd5($thekey.$param.$_G['page']);
	}

	if(!empty($paramarr['cachetime']) && $paramarr['cachetime']>0) {
		$cacheupdatetime = $paramarr['cachetime'];
	} else {
		$cacheupdatetime = 3600*24*30+rand(0, 1000);
		//默認緩存時間一個月
	}

	if($cacheupdatetime) {
		//獲取緩存

		if(!empty($paramarr['perpage'])) {
			//獲取分頁計數緩存
			$_BCACHE->get($multicachekey);
			if(!isset($_SBLOCK[$multicachekey])) {
				$multineedcache = 1;//沒有分頁計數緩存
			} else {
				//創建下次更新時?
				if(!empty($_SBLOCK[$multicachekey]['filemtime'])) $_SBLOCK[$multicachekey]['updatetime'] = $_SBLOCK[$multicachekey]['filemtime'] + $cacheupdatetime; //文件緩存方式
				if(!empty($_SBLOCK[$multicachekey]['updatetime']) && $_SBLOCK[$multicachekey]['updatetime'] < $_G['timestamp']) {
					$multineedcache = 2; //需要更新
				}
			}
		}

		$_BCACHE->get($cachekey);
		if(!isset($_SBLOCK[$cachekey])) {
			$needcache = 1;//沒有緩存
		} else {
			//創建下次更新時?
			if(!empty($_SBLOCK[$cachekey]['filemtime'])) $_SBLOCK[$cachekey]['updatetime'] = $_SBLOCK[$cachekey]['filemtime'] + $cacheupdatetime; //文件緩存方式
			if(!empty($_SBLOCK[$multicachekey]['updatetime']) && $_SBLOCK[$cachekey]['updatetime'] < $_G['timestamp']) {
				$needcache = 2;//需要更新
			}
		}
	}

	if($multineedcache && !empty($paramarr['perpage'])) {
		//分頁計數緩存
		require_once(B_ROOT.'./source/function/block.func.php');
		$multicount = $block_func($paramarr, $multicachekey, 1);
		$_SBLOCK[$multicachekey]['value'] = serialize($multicount);
		$_SBLOCK[$multicachekey]['updatetime'] = $_G['timestamp'] + $cacheupdatetime;

		if($multineedcache == 1 || $multineedcache == 2) {
			$_BCACHE->set($multicachekey, $_SBLOCK[$multicachekey]['value'], $_SBLOCK[$multicachekey]['updatetime'], $paramarr['pagetype'], $paramarr['usetype'], $paramarr['shopid'], $paramarr['infoid']);
		}
	}

	if($needcache) {
		$theblockarr = array();

		require_once(B_ROOT.'./source/function/block.func.php');

		$theblockarr = $block_func($paramarr, $multicachekey);

		$_SBLOCK[$thekey] = $theblockarr;
		$havethekey = true;
		$_SBLOCK[$cachekey]['value'] = serialize($theblockarr);
		$_SBLOCK[$cachekey]['updatetime'] = $_G['timestamp'] + $cacheupdatetime;

		if($needcache == 1 || $needcache == 2) {
			//INSERT-UPDATE
			$_BCACHE->set($cachekey, $_SBLOCK[$cachekey]['value'], $_SBLOCK[$cachekey]['updatetime'], $paramarr['pagetype'], $paramarr['usetype'], $paramarr['shopid'], $paramarr['infoid']);
		}
	}

	if(!$havethekey) {
		$_SBLOCK[$thekey] = empty($_SBLOCK[$cachekey]['value'])?array():unserialize($_SBLOCK[$cachekey]['value']);
	}

	$iarr = $_SBLOCK[$thekey];
	if(!empty($paramarr['cachename'])) {
		$_SBLOCK[$paramarr['cachename'].'_listcount'] = !empty($_SBLOCK[$thekey]['listcount'])?$_SBLOCK[$thekey]['listcount']:0;
		$_SBLOCK[$paramarr['cachename'].'_multipage'] = empty($_SBLOCK[$thekey]['multipage'])?'':$_SBLOCK[$thekey]['multipage'];
		$_SBLOCK[$paramarr['cachename']] = $_SBLOCK[$thekey];
		unset($_SBLOCK[$paramarr['cachename']]['multipage'], $_SBLOCK[$paramarr['cachename']]['listcount']);
	}

	if(!empty($paramarr['tpl']) && $paramarr['tpl'] != 'data') {
		$paramarr['tpl'] = 'static/blockstyle/'.$paramarr['tpl'].'.html.php';
		include template($paramarr['tpl'], 1);
	}

	return $cachekey;

}

function smd5($str) {
	return substr(md5($str), 8, 16);
}

function snl2br($message) {
	return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}

//替換字符串中的特殊字符
//去掉指定字符串中\\或\'前的\
function sstripslashes($string) {

	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = sstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

function inserttable($tablename, $insertsqlarr, $returnid=0, $replace = false, $silent=0) {
	global $_G, $_SGLOBAL;

	$insertkeysql = $insertvaluesql = $comma = '';
	foreach ($insertsqlarr as $insert_key => $insert_value) {
		$insertkeysql .= $comma.'`'.$insert_key.'`';
		$insertvaluesql .= $comma.'\''.$insert_value.'\'';
		$comma = ', ';
	}
	$method = $replace?'REPLACE':'INSERT';
	DB::query($method.' INTO '.tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.') ', $silent?'SILENT':'');
	if($returnid && !$replace) {
		return DB::insert_id();
	}
}

function deletetable($tablename, $wheresqlarr) {
	global $_G, $_SGLOBAL;

	if(empty($wheresqlarr)) {
		DB::query('TRUNCATE TABLE '.tname($tablename));
	} else {
		DB::query('DELETE FROM '.tname($tablename).' WHERE '.getwheresql($wheresqlarr));
	}
}

function updatetable($tablename, $setsqlarr, $wheresqlarr) {
	global $_G, $_SGLOBAL;

	$setsql = $comma = '';
	foreach ($setsqlarr as $set_key => $set_value) {
		$setsql .= $comma.$set_key.'=\''.$set_value.'\'';
		$comma = ', ';
	}
	DB::query('UPDATE '.tname($tablename).' SET '.$setsql.' WHERE '.getwheresql($wheresqlarr));
}

function getwheresql($wheresqlarr) {
	$result = $comma = '';
	if(empty($wheresqlarr)) {
		$result = '1';
	} elseif(is_array($wheresqlarr)) {
		foreach ($wheresqlarr as $key => $value) {
			$result .= $comma.$key.'=\''.$value.'\'';
			$comma = ' AND ';
		}
	} else {
		$result = $wheresqlarr;
	}
	return $result;
}

//格式化大小函數,根據字節數自動顯示成'KB','MB'等等
function formatsize($size, $prec=3) {
	$size = round(abs($size));
	$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	$unit = min(4, floor(log($size)/log(2)/10));
	$size = $size * pow(2, -10*$unit);
	$digi = $prec - 1 - floor(log($size)/log(10));
	$size = round($size * pow(10, $digi)) * pow(10, -$digi);
	return $size.$units[$unit];
}

//寫錯誤日誌函數
function errorlog($type, $message, $halt = 0) {
	global $_G, $_SGLOBAL;
	@$fp = fopen(B_ROOT.'./log/errorlog.php', 'a');
	@fwrite($fp, "<?exit?>$_G[timestamp]\t$type\t$_G[uid]\t".str_replace(array("\r", "\n"), array(' ', ' '), trim(shtmlspecialchars($message)))."\n");
	@fclose($fp);
	if($halt) {
		exit();
	}
}

//調試信息,顯示進程處理時間
function debuginfo($echo=1) {
	global $_G, $_SGLOBAL, $_BCACHE;

	$info = '';
	if(1==1 /*$_G['setting']['debug']*/) {
		$mtime = explode(' ', microtime());
		$totaltime = number_format(($mtime[1] + $mtime[0] - $_G['starttime']), 6);
		$info .= 'Processed in '.$totaltime.' second(s), ' . $_G['debug']['querynum'] .' queries. Cache '.
		$_BCACHE->getcachemode().' '.$_BCACHE->getallowcache().'.';
		$info .= '';
	}
	if($echo) {
		echo $info;
	} else {
		return $info;
	}
}

/**
 * 品牌空間的字符串長度計算（漢字和英文都算1個）
 * @param $string='', 需要計算的字符串
 * @param $charset, 字符集, gbk或utf8
 * @return 文字長度
 */
function bstrlen($string, $charset='') {
	global $_G, $_SC;
	if(empty($charset)) {
		$charset = $_G['charset'];
	}
	if(strtolower($charset) == 'gbk') {
		$charset = 'gbk';
	} else {
		$charset = 'utf8';
	}
	if(function_exists('mb_strlen')) {
		return mb_strlen($string, $charset);
	} else {
		$n = $noc = 0;
		$strlen = strlen($string);

		if($charset == 'utf8') {

			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$n += 2; $noc++;
				} elseif(224 <= $t && $t <= 239) {
					$n += 3; $noc++;
				} elseif(240 <= $t && $t <= 247) {
					$n += 4; $noc++;
				} elseif(248 <= $t && $t <= 251) {
					$n += 5; $noc++;
				} elseif($t == 252 || $t == 253) {
					$n += 6; $noc++;
				} else {
					$n++;
				}
			}

		} else {

			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t>127) {
					$n += 2; $noc++;
				} else {
					$n++; $noc++;
				}
			}

		}

		return $noc;
	}
}

/**
 * 品牌空間的字符串長度截斷（漢字和英文都算1個）
 * @param $string='', 需要計算的字符串
 * @param $length='', 需要截斷的字符長度
 * @param $havedot='', 截斷後是否顯示省略符號
 * @param $charset, 字符集, gbk或utf8
 * @return 文字長度
 */
function cutstr($string, $length, $havedot=0, $charset='') {
	global $_G, $_SC;
	if(empty($charset)) {
		$charset = $_G['charset'];
	}
	if(strtolower($charset) == 'gbk') {
		$charset = 'gbk';
	} else {
		$charset = 'utf8';
	}
	if(bstrlen($string, $charset) <= $length) {
		return $string;
	}
	if(function_exists('mb_strcut')) {
		$string = mb_substr($string, 0, $length, $charset);
	} else {
		$pre = '{%';
		$end = '%}';
		$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

		$strcut = '';
		$strlen = strlen($string);

		if($charset == 'utf8') {
			$n = $tn = $noc = 0;
			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1; $n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2; $n += 2; $noc++;
				} elseif(224 <= $t && $t <= 239) {
					$tn = 3; $n += 3; $noc++;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4; $n += 4; $noc++;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5; $n += 5; $noc++;
				} elseif($t == 252 || $t == 253) {
					$tn = 6; $n += 6; $noc++;
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
			while($n < $strlen) {
				$t = ord($string[$n]);
				if($t > 127) {
					$tn = 2; $n += 2; $noc++;
				} else {
					$tn = 1; $n++; $noc++;
				}
				if($noc >= $length) {
					break;
				}
			}
			if($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		}
		$string = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
	}

	if($havedot) {
		$string = $string.$havedot;
	}

	return $string;
}

/**
 * 去除ubb代碼
 * @param $str=''，字符串， $length截斷長度
 * @return 字符串
 */
function messagecutstr($str, $length=255) {
	$bbcodes = 'b|i|u|p|color|size|font|align|list|indent|float';
	$bbcodesclear = 'url|email|code|free|table|tr|td|img|swf|flash|attach|media|audio|payto';
	$str = cutstr(strip_tags(preg_replace(array(
			"/\[hide=?\d*\](.+?)\[\/hide\]/is",
			"/\[quote](.*?)\[\/quote]/si",
			"/\[($bbcodesclear)=?.*?\].+?\[\/\\1\]/si",
			"/\[($bbcodes)=?.*?\]/i",
			"/\[\/($bbcodes)\]/i",
	), array(
			' !hidden! ',
			'',
			'',
			'',
			''
			), $str)), $length);
			return trim($str);
}

//生成分頁URL地址集合
function multi($num, $perpage, $curpage, $mpurl, $phpurl=1) {

	global $_G, $_SHTML, $lang, $_SGLOBAL;
	if(($curpage-1)*$perpage > $num) showmessage('start_listcount_error');

	$maxpages = $_SGLOBAL['maxpages'];
	$multipage = $a_name = '';
	if($phpurl) {
		$mpurl .= strpos($mpurl, '?') ? '&' : '?';
	} else {
		$urlarr = $mpurl;
		unset($urlarr['php']);
		unset($urlarr['modified']);
	}
	if($num > $perpage) {
		$page = 10;
		$offset = 2;
		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;
		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $curpage + $page - $offset - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if(($to - $from) < $page && ($to - $from) < $pages) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $curpage - $pages + $to;
				$to = $pages;
				if(($to - $from) < $page && ($to - $from) < $pages) {
					$from = $pages - $page + 1;
				}
			}
		}

		if($phpurl) {
			$url = $mpurl.'page=1'.$a_name;
			$url2 = $mpurl.'page='.($curpage - 1).$a_name;
		} else {
			$urlarr['page'] = 1;
			$url = geturl(arraytostring($urlarr)).$a_name;
			$urlarr['page'] = $curpage - 1;
			$url2 = geturl(arraytostring($urlarr)).$a_name;
		}

		$multipage = '<div class="pages"><div>'.($curpage - $offset > 1 && $pages > $page ? '<a href="'.$url.'">1...</a>' : '').($curpage > 1 ? '<a class="prev" href="'.$url2.'">'.$lang['pre_page'].'</a>' : '');
		for($i = $from; $i <= $to; $i++) {
			if($phpurl) {
				$url = $mpurl.'page='.$i.$a_name;
			} else {
				$urlarr['page'] = $i;
				if($urlarr['page'] == 1) unset($urlarr['page']);
				$url = geturl(arraytostring($urlarr)).$a_name;
			}
			$multipage .= $i == $curpage ? '<strong>'.$i.'</strong>' : '<a href="'.$url.'">'.$i.'</a>';
		}

		if($phpurl) {
			$url = $mpurl.'page='.($curpage + 1).$a_name;
			$url2 = $mpurl.'page='.$pages.$a_name;
		} else {
			$urlarr['page'] = $curpage + 1;
			if($urlarr['page'] == 1) unset($urlarr['page']);
			$url = geturl(arraytostring($urlarr)).$a_name;
			$urlarr['page'] = $pages;
			if($urlarr['page'] == 1) unset($urlarr['page']);
			$url2 = geturl(arraytostring($urlarr)).$a_name;
		}

		$multipage .= ($to < $pages && $curpage < $maxpages ? '<a href="'.$url2.'" target="_self">...'.$realpages.'</a>' : '').
		($curpage < $pages ? '<a class="next" href="'.$url.'">'.$lang['next_page'].'</a>' : '').
		($pages > $page ? '' : '');
		$multipage .= '</div></div>';
	}
	return $multipage;
}

//跟SGMDATE函數對應
function sdate($dateformat, $timestamp, $format=0) {
	echo sgmdate($timestamp, $dateformat, $format);
}

//生成下拉框
function getselectstr($var, $optionarray, $value='', $other='') {
	global $_G, $_SGET;

	$selectstr = '<select id="'.$var.'" name="'.$var.'"'.$other.'>';
	foreach ($optionarray as $optionkey => $optionvalue) {
		$selectstr .= '<option value="'.$optionkey.'">'.$optionvalue.'</option>';
	}
	if($value=='' && isset($_SGET[$var])) {
		$value = $_SGET[$var];
	}
	$selectstr = str_replace('value="'.$value.'"', 'value="'.$value.'" selected', $selectstr);
	$selectstr .= '</select>';
	return $selectstr;
}

//在$sql前強制加上 SELECT
function getblocksql($sql) {
	$sql = trim($sql);
	$sql = str_replace(';', '', $sql);
	$sql = preg_replace("/^(select)/i", '', $sql);
	$sql = 'SELECT'.$sql;
	$sql = stripslashes($sql);
	return $sql;
}

//讀取指定目錄下的文件
function sreaddir($dir, $ext='') {

	$filearr = array();
	if(is_dir($dir)) {
		$filedir = dir($dir);
		while(false !== ($entry = $filedir->read())) {
			if(!empty($ext)) {
				if (strtolower(fileext($entry)) == strtolower($ext)) {
					$filearr[$entry] = $entry;
				}
			} else {
				if($entry != '.' && $entry != '..') {
					$filearr[$entry] = $entry;
				}
			}
		}
		$filedir->close();
	}
	return $filearr;
}

//TAG處理函數
function tagshow($message, $tagarr) {
	global $_G, $_SGLOBAL;
	$message = preg_replace("/\s*(\<.+?\>)\s*/ies", "tagcode('\\1')", $message);
	foreach ($tagarr as $ret) {
		$message = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($ret, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/sieU", "tagshowname('\\1', '\\2')", $message, 1);
	}
	if(empty($_SGLOBAL['tagcodecount'])) $_SGLOBAL['tagcodecount'] = 0;
	for($i = 1; $i <= $_SGLOBAL['tagcodecount']; $i++) {
		$message = str_replace("[\tSUPESITETAGCODE$i\t]", $_SGLOBAL['tagcodehtml'][$i], $message);
	}
	return $message;
}

//TAG處理(屏蔽<xxx>)
function tagcode($str) {
	global $_G, $_SGLOBAL;
	if(empty($_SGLOBAL['tagcodecount'])) $_SGLOBAL['tagcodecount'] = 0;
	$_SGLOBAL['tagcodecount']++;
	$_SGLOBAL['tagcodehtml'][$_SGLOBAL['tagcodecount']] = str_replace('\\"', '"', $str);
	return "[\tSUPESITETAGCODE$_SGLOBAL[tagcodecount]\t]";
}

//TAG處理函數
function tagshowname($thename, $thetext) {
	$name = rawurlencode($thename);
	$thetext = str_replace('\\"', '"', $thetext);
	if(cutstr($thetext,1) != '<') {
		return '<a href="javascript:;" onClick="javascript:tagshow(event, \''.$name.'\');" target="_self"><u><strong>'.$thename.'</strong></u></a>'.$thetext;
	} else {
		return $thename.$thetext;
	}
}

//如果$string不是變量，則返回加上『』的字符串
function getdotstring($string, $vartype, $allownull=false, $varscope=array(), $sqlmode=1, $unique=true) {

	if(is_array($string)) {
		$stringarr = $string;
	} else {
		if(substr($string, 0, 1) == '$') {
			return $string;
		}
		$string = str_replace('，', ',', $string);
		$string = str_replace(' ', ',', $string);
		$stringarr = explode(',', $string);
	}

	$newarr = array();
	foreach ($stringarr as $value) {
		$value = trim($value);
		if($vartype == 'int') {
			$value = intval($value);
		}
		if(!empty($varscope)) {
			if(in_array($value, $varscope)) {
				$newarr[] = $value;
			}
		} else {
			if($allownull) {
				$newarr[] = $value;
			} else {
				if(!empty($value)) $newarr[] = $value;
			}
		}
	}

	if($unique) $newarr = sarray_unique($newarr);

	if($vartype == 'int') {
		$string = implode(',', $newarr);
	} else {
		if($sqlmode) {
			$string = '\''.implode('\',\'', $newarr).'\'';
		} else {
			$string = implode(',', $newarr);
		}
	}
	return $string;
}

//將數組中相同的值去掉,同時將後面的鍵名也忽略掉
function sarray_unique($array) {
	$newarray = array();
	if(!empty($array) && is_array($array)) {
		$array = array_unique($array);
		foreach ($array as $value) {
			$newarray[] = $value;
		}
	}
	return $newarray;
}

//返回標準零時區時間戳
function sstrtotime($timestamp) {
	global $_G;

	$timestamp = trim($timestamp);	//過濾首尾空格
	if(empty($timestamp)) return 0;
	$hour = $minute = $second = $month = $day = $year = 0;
	$exparr = $timearr = array();
	if(strpos($timestamp, ' ') !== false && strpos($timestamp, '-') !== false) {
		$timearr = explode(' ', $timestamp);
		$exparr = explode('-', $timearr[0]);
		$year = empty($exparr[0])?0:intval($exparr[0]);
		$month = empty($exparr[1])?0:intval($exparr[1]);
		$day = empty($exparr[2])?0:intval($exparr[2]);
		$exparr = explode(':', $timearr[1]);
		$hour = empty($exparr[0])?0:intval($exparr[0]);
		$minute = empty($exparr[1])?0:intval($exparr[1]);
		$second = empty($exparr[2])?0:intval($exparr[2]);
	} elseif(strpos($timestamp, '-') !== false && strpos($timestamp, ' ') === false) {
		$exparr = explode('-', $timestamp);
		$year = empty($exparr[0])?0:intval($exparr[0]);
		$month = empty($exparr[1])?0:intval($exparr[1]);
		$day = empty($exparr[2])?0:intval($exparr[2]);
	} elseif(!strpos($timestamp, '-') === false && strpos($timestamp, ' ') !== false) {
		$exparr = explode(':', $timestamp);
		$hour = empty($exparr[0])?0:intval($exparr[0]);
		$minute = empty($exparr[1])?0:intval($exparr[1]);
		$second = empty($exparr[2])?0:intval($exparr[2]);
	} else {
		return 0;
	}
	return gmmktime($hour, $minute, $second, $month, $day, $year) - $_G['setting']['timeoffset'] * 3600;
}

//獲取系統分類
function getcategory($type='', $space='|----', $delbase=0) {
	global $_G, $_SGLOBAL;

	include_once(B_ROOT.'./source/class/tree.class.php');
	$tree = new Tree($type);
	$typestr = empty($type) ? '' : ' WHERE type=\''.$type.'\' ';
	$query = DB::query('SELECT * FROM '.tname('categories').$typestr.' ORDER BY upid, displayorder');
	$miniupid = '';
	$delid = array();
	if($delbase) {
		$delid[] = $delbase;
	}
	while ($value = DB::fetch($query)) {
		if($miniupid == '') $miniupid = $value['upid'];
		$tree->setNode($value['catid'], $value['upid'], $value);
	}
	//根目錄
	$listarr = array();
	if(DB::num_rows($query) > 0) {
		$categoryarr = $tree->getChilds($miniupid);
		foreach ($categoryarr as $key => $catid) {
			$cat = $tree->getValue($catid);
			$cat['pre'] = $tree->getLayer($catid, $space);
			if(!empty($delid) && (in_array($cat['upid'], $delid) || $cat['catid'] == $delbase)) {
				$delid[] = $cat['catid'];
			} else {
				if(empty($typestr)) {
					$listarr[$cat['type']][$cat['catid']] = $cat;
				} else {
					$listarr[$cat['catid']] = $cat;
				}
			}
		}
	}
	return $listarr;

}

//獲取模型分類
function getmodelcategory($name, $space='|----') {
	global $_G, $_SGLOBAL;
	$listarr = array();
	if(in_array($name, array('shop', 'region', 'good', 'album', 'consume', 'notice', 'groupbuy'))) {
		include_once(B_ROOT.'./source/class/tree.class.php');
		$tree = new Tree($name);
		$miniupid = '';
		foreach($_SGLOBAL[$name.'cates'] as $catid=>$category) {
			if($miniupid === '') $miniupid = $category['upid'];
			$tree->setNode($category['catid'], $category['upid'], $category);
		}
		//根目錄
		if(count($_SGLOBAL[$name.'cates']) > 0) {
			$categoryarr = $tree->getChilds($miniupid);
			foreach ($categoryarr as $key => $catid) {
				$cat = $tree->getValue($catid);
				$cat['pre'] = $tree->getLayer($catid, $space);
				$cat['havechild'] = $tree->getChild($catid) ? 1: 0;
				$listarr[$cat['catid']] = $cat;
			}
		}
	}
	return $listarr;
}

//獲取本店舖允許使用的模型分類
function mymodelcategory($mname, $pre='|----') {
	global $_G, $_SGLOBAL;
	$catlist = array();
	$categorylist = getmodelcategory($mname, $pre);

	if(isset($_SGLOBAL['panelinfo']) && $_SGLOBAL['panelinfo']['group'][$mname.'_field'] == 'all') {
		foreach($categorylist as $cat) {
			//if($cat['havechild']==0) {
				$catlist[$cat['catid']] = $cat;
			//}
		}
		return $catlist;
	} elseif(isset($_SGLOBAL['panelinfo'])) {
		$mycatidarr = explode(',', $_SGLOBAL['panelinfo']['group'][$mname.'_field']);
		foreach($mycatidarr as $catid) {
			//if($catid == $categorylist[$catid]['subcatid']) {
				$catlist[$catid] = $categorylist[$catid];
			//}
		}
		return $catlist;
	} else {
		return array();
	}
}

//獲取點評模型
function getcommentmodel($cmid) {
	global $_G, $_SGLOBAL;

	$commentmodelarr = array();
	$query = DB::query('SELECT * FROM '.tname('commentmodels').' WHERE `cmid`=\''.$cmid.'\'');
	if($commentmodelarr = DB::fetch($query)) {
		$commentmodelarr['scorename'] = unserialize($commentmodelarr['scorename']);
	}

	return $commentmodelarr;
}

//獲取店長ID
function getshopuid($type) {
	global $_G, $_SGLOBAL, $item;

	if($type == 'shop') {
		return $item['uid'];
	} else {
		$shopuid = DB::result_first('SELECT uid FROM '.tname('shopitems').' WHERE `itemid`=\''.$item['shopid'].'\'');
		return $shopuid;
	}
}

//獲取論壇附件文件的url地址
function getbbsattachment($attach) {
	global $_G;

	if(strpos($attach['attachment'], '://') === false) {
		$attachurl = empty($_G['setting']['bbs_ftp']['attachurl'])?B_A_URL:(empty($attach['remote'])?B_A_URL:$_G['setting']['bbs_ftp']['attachurl']);
		if(empty($item['thumb'])) {
			return $attachurl.'/'.$attach['attachment'];
		} else {
			return $attachurl.'/'.$attach['attachment'].'.thumb.jpg';
		}
	} else {
		return $attach['attachment'];
	}
}

//切割url
function cuturl($url, $length=65) {
	$urllink = "<a href=\"".(substr(strtolower($url), 0, 4) == 'www.' ? "http://$url" : $url).'" target="_blank">';
	if(strlen($url) > $length) {
		$url = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
	}
	$urllink .= $url.'</a>';
	return $urllink;
}

//資訊標題樣式生成函數
function mktitlestyle($styletitle) {
	if(empty($styletitle)) {
		return '';
	} else {
		$return = '';
		substr($styletitle,8,1) == 1?$em = 'italic':$em = 'none';
		substr($styletitle,9,1) == 1?$strong = 'bold':$strong = 'none';
		substr($styletitle,10,1) == 1?$underline = 'underline':$underline = 'none';
		$color = trim(substr($styletitle,0,6));
		$size = trim(substr($styletitle,6,2));
		if(!empty($color)){
			$return .= 'color:#'.$color.";";
		}
		if(!empty($size)){
			$return .= 'font-size:'.$size."px;";
		}
		$return .= 'font-style:'.$em.';font-weight:'.$strong.';text-decoration:'.$underline;
		return $return;
	}
}

function strim($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = strim($val);
		}
	} else {
		$string = trim($string);
	}
	return $string;
}

function scensor($string, $mod=0) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = scensor($val, $mod);
		}
	} else {
		$string = censor($string, $mod);
	}
	return $string;
}

//獲取用戶數據
function getpassport($username, $password) {
	$passport = array();
	if(!@include_once B_ROOT.'./uc_client/client.php') {
		showmessage('system_error');
	}

	$ucresult = uc_user_login($username, $password);
	if($ucresult[0] > 0) {
		$passport['uid'] = $ucresult[0];
		$passport['username'] = $ucresult[1];
		$passport['email'] = $ucresult[3];
	}
	return $passport;
}

//產生form防偽碼
function formhash() {
	global $_G, $_SGLOBAL;

	if(empty($_SGLOBAL['formhash'])) {
		$hashadd = (defined('IN_ADMIN') || defined('IN_STORE')) ? 'Only For BRAND Admin OR Panel' : '';
		$_SGLOBAL['formhash'] = substr(md5(substr($_G['timestamp'], 0, -7).'|'.$_G['uid'].'|'.md5($_G['setting']['sitekey']).'|'.$hashadd), 8, 8);
	}
	return $_SGLOBAL['formhash'];
}

//獲取用戶
function getmember() {
	global $_G, $_SGLOBAL, $_SC;

	$_G['uid'] = 0;
	$_G['username'] = '';
	$_G['member'] = array();
	if($_G['cookie']['auth']) {
		@list($password, $uid) = explode("\t", authcode($_G['cookie']['auth'], 'DECODE'));
		$query = DB::query("SELECT * FROM ".tname('members')." m  LEFT JOIN ".tname('shopitem')." s on m.uid=s.uid WHERE m.uid='".intval($uid)."' AND m.password='".addslashes($password)."'");
		if($member = DB::fetch($query)) {
			$_G['member'] = $member;
			$_G['uid'] = $member['uid'];
			$_G['username'] = addslashes($member['username']);
			$_SGLOBAL['groupid'] = $member['groupid'];
		} else {
			sclearcookie();
		}
	}
}

function ckstart($start, $perpage) {
	global $_G;

	$maxstart = $perpage*intval($_G['setting']['maxpage']);
	if($start < 0 || ($maxstart > 0 && $start >= $maxstart)) {
		showmessage('length_is_not_within_the_scope_of');
	}
}

//處理頭像
function avatar($uid, $size='small') {
	return UC_API.'/avatar.php?uid='.$uid.'&size='.$size;
}

//檢查驗證碼
function ckseccode($seccode) {
	global $_G, $_SCOOKIE;

	$check = true;
	$cookie_seccode = empty($_G['cookie']['seccode'])?'':authcode($_G['cookie']['seccode'], 'DECODE');
	if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
		$check = false;
	}
	return $check;
}

//判斷是否設置論壇
function exists_discuz() {
	global $_G, $_SC;
	if(!empty($_SC['bbs_url'])) {
		return true;
	} else {
		return false;
	}
}

//檢查郵箱是否有效
function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

function updatecredit($action, $uids){
	global $_G, $_SGLOBAL;

	@include_once(B_ROOT.'./data/system/creditrule.cache.php');
	$rule = $_SGLOBAL['creditrule'][$action];

	$uidm = array();
	foreach ($uids as $uid) {
		if(empty($uidm[$uid])) $uidm[$uid] = 0;
		$uidm[$uid]++;
	}

	$uidnum = array();
	foreach ($uidm as $uid=>$value) {
		$uidnum[$value][] = $uid;
	}

	$credit = 0;
	foreach ($uidnum as $num=>$uidarr) {
		$credit = $rule['credit'] * $num;
		$experience = $rule['experience'] * $num;
		$uidstr = simplode($uidarr);
		if($rule['rewardtype'] == 1) {
			DB::query('UPDATE '.tname('members')." SET credit=credit+$credit, experience=experience+$experience  WHERE uid IN ($uidstr)");
		} elseif ($rule['rewardtype'] == 0) {
			DB::query('UPDATE '.tname('members')." SET credit=credit-$credit, experience=experience+$experience WHERE uid IN ($uidstr)");
		} elseif ($rule['rewardtype'] == 2) {
			DB::query('UPDATE '.tname('members')." SET credit=credit-$credit, experience=experience-$experience WHERE uid IN ($uidstr)");
		}
	}

}

//通過某物件得到所屬店舖的店舖組ID
function getgroupid($type, $itemid) {
	global $_G, $_SGLOBAL;

	if($type == 'shop') {
		$shopid = $itemid;
	} else {
		$shopid = DB::result_first('SELECT shopid FROM '.tname($type.'items').' WHERE itemid=\''.$itemid.'\'');
	}
	$groupid = DB::result_first('SELECT groupid FROM '.tname('shopitems').' WHERE itemid=\''.$shopid.'\'');

	return $groupid;
}

/**
 * 檢查是否操作創始人
 */
function ckfounder($uid) {
	global $_G, $_SC;

	$founders = empty($_SC['founder'])?array():explode(',', $_SC['founder']);
	return in_array($uid, $founders);
}

function formatcomment($comment, $repeatids = array(), $style=0) {
	global $_G, $lang;

	include_once(B_ROOT.'./source/function/misc.func.php');
	$searcharr = $replacearr = array();
	$comment['message'] = snl2br($comment['message']);

	if(empty($comment['author'])) $comment['author'] = 'Guest';
	$comment['hideauthor'] = (!empty($comment['hideauthor']) && !empty($_G['setting']['commanonymous'])) ? 1 : 0;
	$comment['hideip'] = (!empty($comment['hideip']) && !empty($_G['setting']['commhideip'])) ? 1 : 0;
	$comment['hidelocation'] = (!empty($comment['hidelocation']) && !empty($_G['setting']['commhidelocation'])) ? 1 : 0;
	$comment['iplocation'] = str_replace(array('-', ' '), '', convertip($comment['ip']));
	$comment['ip'] = preg_replace("/^(\d{1,3})\.(\d{1,3})\.\d{1,3}\.\d{1,3}$/", "\$1.\$2.*.*", $comment['ip']);

	$_G['setting']['commfloornum'] = intval($_G['setting']['commfloornum']);
	$comment['floornum'] = intval($comment['floornum']);
	if(!$style) {

		if(!empty($_G['setting']['commfloornum'])) {
			//削樓功能
			if($_G['setting']['commfloornum'] < $comment['floornum']) {
				$cutfloor = $comment['floornum'] - $_G['setting']['commfloornum'];
				$searchstr = "/\<div id=\"cid_{$comment['cid']}_$cutfloor\".*?\<div id=\"cid_{$comment['cid']}_".($cutfloor+1)."_title\"/is";
				$replacestr = "<div id=\"cid_{$comment['cid']}_".($cutfloor+1)."_title\"";
				$comment['message'] = preg_replace($searchstr, $replacestr, $comment['message']);
			}

		} else {
			//高層電梯
			if($comment['floornum'] > 49) {
				$elevatordetail = <<<EOF
						<div id="cid_{$comment['cid']}_elevator" class="floor_op">
							<div class="old_title "><span class="author">$lang[comment_elevator]</span><span class="color_red">$lang[comment_floor_hide]</span></div>
							<p class="detail "><span><a class="color_red" href="javascript:;" onclick="elevator($comment[cid], 2);" title="$lang[comment_floor_up_title]">[{$lang['comment_floor_up']}]</a>
							<a class="color_red" href="javascript:;" onclick="elevator($comment[cid], 1);" title="$lang[comment_floor_down_title]">[{$lang['comment_floor_down']}]</a></span>
							$lang[comment_floor_total]{$comment['floornum']}$lang[comment_floor_total_2]</p>
							<input type="hidden" id="cid_{$comment['cid']}_elevatornum" value="40">
							<input type="hidden" id="cid_{$comment['cid']}_floornum" value="$comment[floornum]">
						</div>
EOF;
							$searcharr[] = '<div id="cid_'.$comment['cid'].'_'.($comment['floornum']-8).'_title"';
							$replacearr[] = $elevatordetail.'<div id="cid_'.$comment['cid'].'_'.($comment['floornum']-8).'_title"';
							if(!in_array($comment['firstcid'], $repeatids)) {
								for ($i=41; $i < $comment['floornum']-8; $i++) {
									$searcharr[] = "id=\"cid_{$comment['cid']}_{$i}\" class=\"old\"";
									$searcharr[] = "id=\"cid_{$comment['cid']}_{$i}_title\" class=\"old_title\"";
									$searcharr[] = "id=\"cid_{$comment['cid']}_{$i}_detail\" class=\"detail\"";
									$replacearr[] = "id=\"cid_{$comment['cid']}_{$i}\" class=\"hideold\"";
									$replacearr[] = "id=\"cid_{$comment['cid']}_{$i}_title\" class=\"hideelement\"";
									$replacearr[] = "id=\"cid_{$comment['cid']}_{$i}_detail\" class=\"hideelement\"";
								}
							}
			}

		}

		//隱藏重複蓋樓
		if(!empty($_G['setting']['commhidefloor']) && in_array($comment['firstcid'], $repeatids)) {
			$tipdetail = "<p id=\"cid_{$comment['cid']}_tip_detail\" class=\"hidetip\">$lang[comment_floor_repeat] <a class=\"color_red\" href=\"javascript:;\" onclick=\"operatefloor({$comment['cid']});\">[{$lang['comment_floor_view_repeat']}]</a><p></div>";
			$searcharr[] = 'class="old"';
			$searcharr[] = 'class="old_title"';
			$searcharr[] = 'class="detail"';
			$searcharr[] = 'class="floor_op"';
			$searcharr[] = '_1" class="hideold"';
			$searcharr[] = '_tip" class="hideold"';
			$searcharr[] = '_1_title" class="hideelement"';
			$searcharr[] = '_1_detail" class="hideelement"';
			$searcharr[] = '<div class="new"';

			$replacearr[] = 'class="hideold"';
			$replacearr[] = 'class="hideelement"';
			$replacearr[] = 'class="hideelement"';
			$replacearr[] = 'class="hideelement"';
			$replacearr[] = '_1" class="old"';
			$replacearr[] = '_tip" class="old"';
			$replacearr[] = '_1_title" class="old_title"';
			$replacearr[] = '_1_detail" class="detail"';
			$replacearr[] = $tipdetail.'<div class="new"';

			$comment['message'] = "<div id=\"cid_{$comment['cid']}_tip\" class=\"old\">".$comment['message'];

		}

		$comment['message'] = str_replace($searcharr, $replacearr, $comment['message']);

	} else {

		preg_match_all ("/\<div class=\"new\">(.+)?\<\/div\>/is", $comment['message'], $currentmessage, PREG_SET_ORDER);
		if(!empty($currentmessage)) $comment['message'] = $currentmessage[0][0];
		$comment['message'] = preg_replace("/\<div class=\"quote\"\>\<blockquote.+?\<\/blockquote\>\<\/div\>/is", '',$comment['message']);

	}
	return $comment;
}
//公告標題樣式生成函數
function pktitlestyle($styletitle) {
	$styletitle = substr($styletitle, 1);
	if(empty($styletitle)) {
		return '';
	} else {
		$return = '';
		substr($styletitle,6,1) == 1?$em = 'italic':$em = 'none';
		substr($styletitle,7,1) == 1?$strong = 'bold':$strong = 'none';
		substr($styletitle,8,1) == 1?$underline = 'underline':$underline = 'none';
		$color = trim(substr($styletitle,0,6));
		if(!empty($color)){
			$return .= 'color:#'.$color.";";
		}
		$return .= 'font-style:'.$em.';font-weight:'.$strong.';text-decoration:'.$underline;
		return $return;
	}
}

if(!function_exists('htmlspecialchars_decode')) {
	//兼容php 4
	function htmlspecialchars_decode($text){
		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}

//取得系統設置
function refreshbrandsetting() {
	global $_G, $_SGLOBAL, $_SSCONFIG, $_SC, $lang;

	$cachefile = B_ROOT.'./data/system/config.cache.php';

	if(!file_exists($cachefile)) {
		require_once(B_ROOT.'./source/function/cache.func.php');
		updatesettingcache();
		@header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}
}

// 檢查各種過期狀態
function ck_item_status($itemid, $mname, $showmessage = 1, $shopid = 0) {
	global $_G, $_SGLOBAL;
	$selectsql = $wheresql = '';
	$itemid = intval($itemid);
	$is_out_date = false;

	if($mname=='index') $mname='shop';

	if(empty($mname) || !in_array($mname, array('shop','good','notice','consume','groupbuy'))) {
		$selectsql = 'SELECT grade FROM ';
	} else {
		$selectsql = 'SELECT grade, validity_end FROM ';
	}

	if($mname == 'shop') {
		$wheresql = " itemid='$itemid'";
	} else {
		$wheresql = " itemid='$itemid' AND shopid='$shopid'";
	}

	$sql = $selectsql.tname($mname.'items')." WHERE $wheresql";

	$status = DB::fetch(DB::query($sql));
	if(!$status) {
		if($showmessage > 0) {
			showmessage('not_found', 'index.php');
		}
		return false;
	}

	if(intval($status['grade']) < 3){
		$is_out_date = true;
	} elseif(!in_array($mname, array('album', 'photo'))) {
		if($status['validity_end'] != '0' && ($status['validity_end'] < $_G['timestamp'])){
			DB::query('UPDATE '.tname($mname.'items')." SET grade='2' WHERE itemid='$itemid'");
			$is_out_date = true;
		}
	}

	if($is_out_date && $showmessage == 1){
		if($mname == 'shop'){
			showmessage('shop_close', 'index.php');
		} else {
			showmessage('not_found', 'index.php');
		}
	}
	return $is_out_date;
}

/**
 * 加載 Class
 *
 * @param $m 類名
 * @param $param 實例化參數
 */
function loadClass($m, $param = ''){
	if(!file_exists(B_ROOT.'./source/class/'.$m.'.class.php')){
		die('Class load error : no file exists ! the file must be the same name as classname and replaced in ./source/class/ ');
	}

	@require_once B_ROOT.'./source/class/'.$m.'.class.php';
	eval('$m = new '.$m.'('.$param.');');
	return $m;
}

/**
 * 初始化 緩存的class
 */
function data_cache_start() {
	global $_G, $_BCACHE;
	if(empty($_BCACHE)) {
		require_once(B_ROOT.'./source/class/data_cache.class.php');
		$_BCACHE = new bcache($_G['setting']['cachemode'], $_G['setting']['cachegrade'], $_G['setting']['allowcache']);
	}
}

/**
 * 取得附件圖片URL路徑（多服務器冗余）
 * @param $url=''，附件表記錄的圖片相對位置
 * @param $thumb, 檢測並返回縮略圖
 * @param $nopicurl，檢測到圖片不存在時使用的無圖提示圖片URL
 * @param $force，強制返回圖片URL
 * @return 圖片URL
 */
function getattachurl($url='', $thumb=0, $nopicurl='', $force=0) {
	global $_G;
	$url = trim($url);
	$nopicurl = trim($nopicurl);
	if(!$nopicurl) {
		$nopicurl = B_URL.'/static/image/nophoto.gif';
	}
	if($force || strpos($url, 'http://')!==false) {
		$url = $url;
	} elseif($url && file_exists(A_DIR.'/'.$url)) {
		//原始圖片檢查
		if($_G['setting']['attachmenturlcount']>1) {
			//附件多域名，基本平均分佈算法
			$this_a = ord(substr($url, -12, -11))%$_G['setting']['attachmenturlcount'];
			$this_aurl = $_G['setting']['attachmenturlarr'][$this_a];
		} else {
			$this_aurl = A_URL;
		}

		if($thumb && file_exists(A_DIR.'/' . $url . '.thumb.jpg')) {
			$url = $this_aurl.'/' . $url . '.thumb.jpg';
		}elseif($thumb && file_exists(A_DIR.'/'.substr($url,0,-4).'.thumb.jpg')) {
			//調用縮略圖檢查
			$url = $this_aurl.'/'.substr($url,0,-4).'.thumb.jpg';
		} else {
			$url = $this_aurl.'/'.$url;
		}
	} else {
		$url = $nopicurl;
	}
	return $url;
}

//取GB2312字符串首字母,原理是GBK漢字是按拼音順序編碼的
//如果是utf8的則轉為gbk的，再進行取就行了
$dict=array(
	'a'=>0xB0C4,
	'b'=>0xB2C0,
	'c'=>0xB4ED,
	'd'=>0xB6E9,
	'e'=>0xB7A1,
	'f'=>0xB8C0,
	'g'=>0xB9FD,
	'h'=>0xBBF6,
	'j'=>0xBFA5,
	'k'=>0xC0AB,
	'l'=>0xC2E7,
	'm'=>0xC4C2,
	'n'=>0xC5B5,
	'o'=>0xC5BD,
	'p'=>0xC6D9,
	'q'=>0xC8BA,
	'r'=>0xC8F5,
	's'=>0xCBF9,
	't'=>0xCDD9,
	'w'=>0xCEF3,
	'x'=>0xD188,
	'y'=>0xD4D0,
	'z'=>0xD7F9,
);

function getletter($input) {
	global $_G, $dict;
	$str_1 = substr($input, 0, 1);
	if($str_1 >= chr(0x81) && $str_1 <= chr(0xfe)) {
		$num = hexdec(bin2hex(substr($input, 0, 2)));
		foreach ($dict as $k=>$v){
			if($v>=$num) {
				break;
			}
		}
		return $k;
	} else {
		if(is_numeric($str_1)) {
			switch($str_1) {
				case '1':
					$str_1 = 'y';
					break;
				case '2':
					$str_1 = 'e';
					break;
				case '3':
				case '4':
					$str_1 = 's';
					break;
				case '5':
					$str_1 = 'w';
					break;
				case '6':
				case '0':
					$str_1 = 'l';
					break;
				case '7':
					$str_1 = 'q';
					break;
				case '8':
					$str_1 = 'b';
					break;
				case '9':
					$str_1 = 'j';
					break;
				default:
					$str_1 = 'u';
					break;
			}
		}
		return $str_1;
	}
}

function showxmlheader($encoding='GBK') {
	global $_G;
	obclean();
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", false);
	@header("Pragma: no-cache");
	@header('Content-type: application/xml; charset='.$encoding);
	echo "<?xml version=\"1.0\" encoding=\"$encoding\"?>\n";
}

function showarraytoxml($phparray = array(), $encoding='GBK', $unityitem=0) {
	global $_G, $_SC;
	$phparray = biconv($phparray, $_G['charset'], $encoding, 1);
	$retxml = '';
	foreach($phparray as $key=>$value) {
		if($key && $value) {
			if(is_numeric($key)) { $key = 'key_'.$key;}
			if(is_array($value)) {
				$retxml .= "\n<$key>\n";
				$retxml .= showarraytoxml($value);
				$retxml .= "</$key>\n";
			} else {
				if($unityitem) {
					$retxml .= "<item name='$key'><![CDATA[$value]]></item>\n";
				} else {
					$retxml .= "<$key><![CDATA[$value]]></$key>\n";
				}
			}
		}
	}
	return $retxml;
}

function biconv($string, $from='GBK', $to='UTF-8', $ignore=1) {
	if(strncasecmp($from, $to, 3)==0) {
		//兩字符串前3個字節忽略大小寫相等，不需要轉換編碼
		return $string;
	}
	if(is_array($string)) {
		foreach($string as $key=>$value) {
			$string[$key] = biconv($value, $from, $to, $ignore);
		}
	} else {
		if(function_exists('mb_convert_encoding')) {
			$string = mb_convert_encoding($string, $to, $from);
		} else {
			$string = iconv($from, $to.($ignore?'//IGNORE':''), $string);
		}
	}
	return $string;
}

/**
 * 將地區分類轉成json
 */
function json_encode_region($sourcearr) {
	global $_G, $_SC;
	if(!function_exists("json_encode")) {
		$com = "";
		$json = "{";
		if(!empty($sourcearr)){
			foreach($sourcearr as $key=>$value) {
				if(is_array($value)) {
					$json_node = "\"$key\":".json_encode_region($value);
				} else {
					$json_node = "\"$key\":\"$value\"";
				}
				$json .= $com. $json_node;
				$com = ", ";
			}
		}
		return $json."}";
	} else {
		$sourcearr = biconv($sourcearr, $_G['charset'], 'UTF-8');
		return json_encode($sourcearr);
	}
}

/**
 * 更新信息統計
 */
function updateshopitemnum($type, $shopid = '') {
	global $_G, $_SGLOBAL, $_POST;

	$pernum = intval($_POST[$type.'_updateshopitemnum']);
	$cronshopid = !empty($shopid) ? $shopid : 0;
	$resultarr = array();
	$shopnum = DB::result_first('SELECT COUNT(itemid) FROM '.tname('shopitems').' WHERE itemid>\''.$cronshopid.'\' ORDER BY itemid ASC');
	$query = DB::query('SELECT itemid FROM '.tname('shopitems').' WHERE itemid>\''.$cronshopid.'\' ORDER BY itemid ASC LIMIT '.$pernum);
	while($value = DB::fetch($query)) {
		$itemnum = 0;
		if($type == 'brandlinks') {
			$itemnum = DB::result_first('SELECT COUNT(linkid) FROM '.tname('brandlinks').' WHERE shopid=\''.$value['itemid'].'\'');
		} else {
			$othersql = $type == 'album' ? 'AND frombbs=0' : '';
			$itemnum = DB::result_first('SELECT COUNT(itemid) FROM '.tname($type.'items').' WHERE shopid=\''.$value['itemid'].'\' '.$othersql);
		}
		DB::query('UPDATE '.tname('shopitems').' SET itemnum_'.$type.'=\''.$itemnum.'\' WHERE itemid=\''.$value['itemid'].'\'');
		$resultarr[] = $value;
	}
	if(($shopnum > $pernum)) {
		$cronlastshop = array_pop($resultarr);
		$cronshopid = $cronlastshop['itemid'];
		updateshopitemnum($type, $cronshopid);
	}
	cpmsg('message_success', $_SERVER['SCRIPT_NAME'].'?action=tool&operation=updateshopitemnum');
}

function array_delete(&$ary, $key_to_be_deleted) {

	$new = array();
	if(is_string($key_to_be_deleted)) {
		if(!array_key_exists($key_to_be_deleted,$ary)) {
			return;
		}
		foreach($ary as $key => $value) {
			if($key != $key_to_be_deleted) {
				$new[$key] = $value;
			}
		}
		$ary = $new;
	}
	if(is_array($key_to_be_deleted)) {
		foreach($key_to_be_deleted as $del) {
			array_delete(&$ary, $del);
		}
	}
}

function sizecount($filesize) {
	if($filesize >= 1073741824) {
		$filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
	} elseif($filesize >= 1048576) {
		$filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
	} elseif($filesize >= 1024) {
		$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
	} else {
		$filesize = $filesize . ' Bytes';
	}
	return $filesize;
}

/**
 * 循環建立文件夾
 *
 * @param string $dir
 * @param string $mode
 */
function dmkdir($dir, $mode = 0777){
	if(!is_dir($dir)) {
		dmkdir(dirname($dir));
		@mkdir($dir, $mode);
		@touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);
	}
	return true;
}

/**
 * 寫日誌
 *
 * @param $file 日誌文件
 * @param $log 日誌內容
 */
function writelog($file, $log) {
	global $_G, $_SGLOBAL;

	$yearmonth = sgmdate($_G['timestamp'], 'Ym');
	$logdir = B_ROOT.'./data/log/';
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

function implodearray($array, $skip = array()) {

	$return = '';
	if(is_array($array) && !empty($array)) {
		foreach ($array as $key => $value) {
			if(empty($skip) || $key === 0 || !in_array($key, $skip)) {
				if(is_array($value)) {
					$return .= "$key={".implodearray($value, $skip)."}; ";
				} else {
					$return .= "$key=$value; ";
				}
			}
		}
	}
	return $return;
}

function clearlogstring($str) {

	if(!empty($str)) {
		if(!is_array($str)) {
			$str = shtmlspecialchars(trim($str));
			$str = str_replace(array("\t", "\r\n", "\n", "   ", "  "), ' ', $str);
		} else {
			foreach ($str as $key => $val) {
				$str[$key] = clearlogstring($val);
			}
		}
	}
	return $str;
}

function getrootcatid($catid) {
	global $_G, $_SGLOBAL;

	if($_SGLOBAL['shopcates'][$catid]['upid'] != 0) {
		$catid = getrootcatid($_SGLOBAL['shopcates'][$catid]['upid']);
	}

	return $catid;
}

function libfile($libname, $folder = '') {
	$libpath = B_ROOT.'/source/'.$folder;
	if(strstr($libname, '/')) {
		list($pre, $name) = explode('/', $libname);
		return realpath("{$libpath}/{$pre}/{$pre}_{$name}.php");
	} else {
		return realpath("{$libpath}/{$libname}.php");
	}
}
?>