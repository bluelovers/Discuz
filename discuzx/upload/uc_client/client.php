<?php

/*
	[UCenter] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: client.php 1018 2010-09-26 07:35:47Z cnteacher $
*/

if(!defined('UC_API')) {
	exit('Access denied');
}

error_reporting(0);

define('IN_UC', TRUE);
define('UC_CLIENT_VERSION', '1.5.2');
define('UC_CLIENT_RELEASE', '20101001');
define('UC_ROOT', substr(__FILE__, 0, -10));
define('UC_DATADIR', UC_ROOT.'./data/');
define('UC_DATAURL', UC_API.'/data');
define('UC_API_FUNC', UC_CONNECT == 'mysql' ? 'uc_api_mysql' : 'uc_api_post');
$GLOBALS['uc_controls'] = array();

function uc_addslashes($string, $force = 0, $strip = FALSE) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = uc_addslashes($val, $force, $strip);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}

if(!function_exists('daddslashes')) {
	function daddslashes($string, $force = 0, $strip = FALSE) {
		return uc_addslashes($string, $force, $strip);
	}
}

function uc_stripslashes($string) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(MAGIC_QUOTES_GPC) {
		return stripslashes($string);
	} else {
		return $string;
	}
}

/**
 *  dfopen 方式取指定的模塊和動作的數據
 *
 * @param string $module	請求的模塊
 * @param string $action 	請求的動作
 * @param array $arg		參數（會加密的方式傳送）
 * @return string
 */
function uc_api_post($module, $action, $arg = array()) {
	$s = $sep = '';
	foreach($arg as $k => $v) {
		$k = urlencode($k);
		if(is_array($v)) {
			$s2 = $sep2 = '';
			foreach($v as $k2 => $v2) {
				$k2 = urlencode($k2);
				$s2 .= "$sep2{$k}[$k2]=".urlencode(uc_stripslashes($v2));
				$sep2 = '&';
			}
			$s .= $sep.$s2;
		} else {
			$s .= "$sep$k=".urlencode(uc_stripslashes($v));
		}
		$sep = '&';
	}
	$postdata = uc_api_requestdata($module, $action, $s);
	return uc_fopen2(UC_API.'/index.php', 500000, $postdata, '', TRUE, UC_IP, 20);
}

/**
 * 構造發送給用戶中心的請求數據
 *
 * @param string $module	請求的模塊
 * @param string $action	請求的動作
 * @param string $arg		參數（會加密的方式傳送）
 * @param string $extra		附加參數（傳送時不加密）
 * @return string
 */
function uc_api_requestdata($module, $action, $arg='', $extra='') {
	$input = uc_api_input($arg);
	$post = "m=$module&a=$action&inajax=2&release=".UC_CLIENT_RELEASE."&input=$input&appid=".UC_APPID.$extra;
	return $post;
}

function uc_api_url($module, $action, $arg='', $extra='') {
	$url = UC_API.'/index.php?'.uc_api_requestdata($module, $action, $arg, $extra);
	return $url;
}

function uc_api_input($data) {
	$s = urlencode(uc_authcode($data.'&agent='.md5($_SERVER['HTTP_USER_AGENT'])."&time=".time(), 'ENCODE', UC_KEY));
	return $s;
}

/**
 * MYSQL 方式取指定的模塊和動作的數據
 *
 * @param string $model		請求的模塊
 * @param string $action	請求的動作
 * @param string $args		參數（會加密的方式傳送）
 * @return mix
 */
function uc_api_mysql($model, $action, $args=array()) {
	global $uc_controls;
	if(empty($uc_controls[$model])) {
		include_once UC_ROOT.'./lib/db.class.php';
		include_once UC_ROOT.'./model/base.php';
		include_once UC_ROOT."./control/$model.php";
		eval("\$uc_controls['$model'] = new {$model}control();");
	}
	if($action{0} != '_') {
		$args = uc_addslashes($args, 1, TRUE);
		$action = 'on'.$action;
		$uc_controls[$model]->input = $args;
		return $uc_controls[$model]->$action($args);
	} else {
		return '';
	}
}

function uc_serialize($arr, $htmlon = 0) {
	include_once UC_ROOT.'./lib/xml.class.php';
	return xml_serialize($arr, $htmlon);
}

function uc_unserialize($s) {
	include_once UC_ROOT.'./lib/xml.class.php';
	return xml_unserialize($s);
}

/**
 * 字符串加密以及解密函數
 *
 * @param string $string	原文或者密文
 * @param string $operation	操作(ENCODE | DECODE), 預設為 DECODE
 * @param string $key		密鑰
 * @param int $expiry		密文有效期, 加密時候有效， 單位 秒，0 為永久有效
 * @return string		處理後的 原文或者 經過 base64_encode 處理後的密文
 *
 * @example
 *
 * 	$a = authcode('abc', 'ENCODE', 'key');
 * 	$b = authcode($a, 'DECODE', 'key');  // $b(abc)
 *
 * 	$a = authcode('abc', 'ENCODE', 'key', 3600);
 * 	$b = authcode('abc', 'DECODE', 'key'); // 在一個小時內，$b(abc)，否則 $b 為空
 */
function uc_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;	//note 隨機密鑰長度 取值 0-32;
				//note 加入隨機密鑰，可以令密文無任何規律，即便是原文和密鑰完全相同，加密結果也會每次不同，增大破解難度。
				//note 取值越大，密文變動規律越大，密文變化 = 16 的 $ckey_length 次方
				//note 當此值為 0 時，則不產生隨機密鑰

	$key = md5($key ? $key : UC_KEY);
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
 *  遠程打開URL
 *  @param string $url		打開的url，　如 http://www.baidu.com/123.htm
 *  @param int $limit		取返回的數據的長度
 *  @param string $post		要發送的 POST 數據，如uid=1&password=1234
 *  @param string $cookie	要模擬的 COOKIE 數據，如uid=123&auth=a2323sd2323
 *  @param bool $bysocket	TRUE/FALSE 是否通過SOCKET打開
 *  @param string $ip		IP地址
 *  @param int $timeout		連接超時時間
 *  @param bool $block		是否為阻塞模式
 *  @return			取到的字符串
 */
function uc_fopen2($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$__times__ = isset($_GET['__times__']) ? intval($_GET['__times__']) + 1 : 1;
	if($__times__ > 2) {
		return '';
	}
	$url .= (strpos($url, '?') === FALSE ? '?' : '&')."__times__=$__times__";
	return uc_fopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
}

function uc_fopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	!isset($matches['host']) && $matches['host'] = '';
	!isset($matches['path']) && $matches['path'] = '';
	!isset($matches['query']) && $matches['query'] = '';
	!isset($matches['port']) && $matches['port'] = '';
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;
	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
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
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}

	if(function_exists('fsockopen')) {
		$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	} elseif (function_exists('pfsockopen')) {
		$fp = @pfsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	} else {
		$fp = false;
	}

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

//		dexit('<pre>'.$out."\n\n".$return.'</pre>');

		return $return;
	}
}

function uc_app_ls() {
	$return = call_user_func(UC_API_FUNC, 'app', 'ls', array());
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 添加 feed
 *
 * @param string $icon			圖標
 * @param string $uid			uid
 * @param string $username		用戶名
 * @param string $title_template	標題模板
 * @param array  $title_data		標題內容
 * @param string $body_template		內容模板
 * @param array  $body_data		內容內容
 * @param string $body_general		保留
 * @param string $target_ids		保留
 * @param array $images		圖片
 * 	格式為:
 * 		array(
 * 			array('url'=>'http://domain1/1.jpg', 'link'=>'http://domain1'),
 * 			array('url'=>'http://domain2/2.jpg', 'link'=>'http://domain2'),
 * 			array('url'=>'http://domain3/3.jpg', 'link'=>'http://domain3'),
 * 		)
 * 	示例:
 * 		$feed['images'][] = array('url'=>$vthumb1, 'link'=>$vthumb1);
 * 		$feed['images'][] = array('url'=>$vthumb2, 'link'=>$vthumb2);
 * @return int feedid
 */
function uc_feed_add($icon, $uid, $username, $title_template='', $title_data='', $body_template='', $body_data='', $body_general='', $target_ids='', $images = array()) {
	return call_user_func(UC_API_FUNC, 'feed', 'add',
		array(  'icon'=>$icon,
			'appid'=>UC_APPID,
			'uid'=>$uid,
			'username'=>$username,
			'title_template'=>$title_template,
			'title_data'=>$title_data,
			'body_template'=>$body_template,
			'body_data'=>$body_data,
			'body_general'=>$body_general,
			'target_ids'=>$target_ids,
			'image_1'=>$images[0]['url'],
			'image_1_link'=>$images[0]['link'],
			'image_2'=>$images[1]['url'],
			'image_2_link'=>$images[1]['link'],
			'image_3'=>$images[2]['url'],
			'image_3_link'=>$images[2]['link'],
			'image_4'=>$images[3]['url'],
			'image_4_link'=>$images[3]['link']
		)
	);
}

/**
 * 每次取多少條
 *
 * @param int $limit
 * @return array()
 */
function uc_feed_get($limit = 100, $delete = TRUE) {
	$return = call_user_func(UC_API_FUNC, 'feed', 'get', array('limit'=>$limit, 'delete'=>$delete));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 添加好友
 *
 * @param int $uid		用戶ID
 * @param int $friendid		好友ID
 * @return
 * 	>0 成功
 * 	<=0 失敗
 */
function uc_friend_add($uid, $friendid, $comment='') {
	return call_user_func(UC_API_FUNC, 'friend', 'add', array('uid'=>$uid, 'friendid'=>$friendid, 'comment'=>$comment));
}

/**
 * 刪除好友
 *
 * @param int $uid		用戶ID
 * @param array $friendids	好友ID
 * @return
 * 	>0 成功
 * 	<=0 失敗,或者好友已經刪除
 */
function uc_friend_delete($uid, $friendids) {
	return call_user_func(UC_API_FUNC, 'friend', 'delete', array('uid'=>$uid, 'friendids'=>$friendids));
}

/**
 * 好友總數
 * @param int $uid		用戶ID
 * @return int
 */
function uc_friend_totalnum($uid, $direction = 0) {
	return call_user_func(UC_API_FUNC, 'friend', 'totalnum', array('uid'=>$uid, 'direction'=>$direction));
}

/**
 * 好友列表
 *
 * @param int $uid		用戶ID
 * @param int $page		當前頁
 * @param int $pagesize		每頁條目數
 * @param int $totalnum		總數
 * @param int $direction	預設為正向. 正向:1 , 反向:2 , 雙向:3
 * @return array
 */
function uc_friend_ls($uid, $page = 1, $pagesize = 10, $totalnum = 10, $direction = 0) {
	$return = call_user_func(UC_API_FUNC, 'friend', 'ls', array('uid'=>$uid, 'page'=>$page, 'pagesize'=>$pagesize, 'totalnum'=>$totalnum, 'direction'=>$direction));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 用戶註冊
 *
 * @param string $username 	用戶名
 * @param string $password 	密碼
 * @param string $email		Email
 * @param int $questionid	安全提問
 * @param string $answer 	安全提問答案
 * @return int
	-1 : 用戶名不合法
	-2 : 包含不允許註冊的詞語
	-3 : 用戶名已經存在
	-4 : email 格式有誤
	-5 : email 不允許註冊
	-6 : 該 email 已經被註冊
	>1 : 表示成功，數值為 UID
*/
function uc_user_register($username, $password, $email, $questionid = '', $answer = '', $regip = '') {
	return call_user_func(UC_API_FUNC, 'user', 'register', array('username'=>$username, 'password'=>$password, 'email'=>$email, 'questionid'=>$questionid, 'answer'=>$answer, 'regip' => $regip));
}

/**
 * 用戶登陸檢查
 *
 * @param string $username	用戶名/uid
 * @param string $password	密碼
 * @param int $isuid		是否為uid
 * @param int $checkques	是否使用檢查安全問答
 * @param int $questionid	安全提問
 * @param string $answer 	安全提問答案
 * @return array (uid/status, username, password, email)
 	數組第一項
 	1  : 成功
	-1 : 用戶不存在,或者被刪除
	-2 : 密碼錯
*/
function uc_user_login($username, $password, $isuid = 0, $checkques = 0, $questionid = '', $answer = '') {
	$isuid = intval($isuid);
	$return = call_user_func(UC_API_FUNC, 'user', 'login', array('username'=>$username, 'password'=>$password, 'isuid'=>$isuid, 'checkques'=>$checkques, 'questionid'=>$questionid, 'answer'=>$answer));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 進入同步登入代碼
 *
 * @param int $uid		用戶ID
 * @return string 		HTML代碼
 */
function uc_user_synlogin($uid) {
	$uid = intval($uid);
	if(@include UC_ROOT.'./data/cache/apps.php') {
		if(count($_CACHE['apps']) > 1) {
			$return = uc_api_post('user', 'synlogin', array('uid'=>$uid));
		} else {
			$return = '';
		}
	}
	return $return;
}

/**
 * 進入同步登出代碼
 *
 * @return string 		HTML代碼
 */
function uc_user_synlogout() {
	if(@include UC_ROOT.'./data/cache/apps.php') {
		if(count($_CACHE['apps']) > 1) {
			$return = uc_api_post('user', 'synlogout', array());
		} else {
			$return = '';
		}
	}
	return $return;
}

/**
 * 編輯用戶
 *
 * @param string $username	用戶名
 * @param string $oldpw		舊密碼
 * @param string $newpw		新密碼
 * @param string $email		Email
 * @param int $ignoreoldpw 	是否忽略舊密碼, 忽略舊密碼, 則不進行舊密碼校驗.
 * @param int $questionid	安全提問
 * @param string $answer 	安全提問答案
 * @return int
 	1  : 修改成功
 	0  : 沒有任何修改
  	-1 : 舊密碼不正確
	-4 : email 格式有誤
	-5 : email 不允許註冊
	-6 : 該 email 已經被註冊
	-7 : 沒有做任何修改
	-8 : 受保護的用戶，沒有權限修改
*/
function uc_user_edit($username, $oldpw, $newpw, $email, $ignoreoldpw = 0, $questionid = '', $answer = '') {
	return call_user_func(UC_API_FUNC, 'user', 'edit', array('username'=>$username, 'oldpw'=>$oldpw, 'newpw'=>$newpw, 'email'=>$email, 'ignoreoldpw'=>$ignoreoldpw, 'questionid'=>$questionid, 'answer'=>$answer));
}

/**
 * 刪除用戶
 *
 * @param string/array $uid	用戶的 UID
 * @return int
 	>0 : 成功
 	0 : 失敗
 */
function uc_user_delete($uid) {
	return call_user_func(UC_API_FUNC, 'user', 'delete', array('uid'=>$uid));
}

/**
 * 刪除用戶頭像
 *
 * @param string/array $uid	用戶的 UID
 */
function uc_user_deleteavatar($uid) {
	uc_api_post('user', 'deleteavatar', array('uid'=>$uid));
}

/**
 * 檢查用戶名是否為合法
 *
 * @param string $username	用戶名
 * @return int
 	 1 : 合法
	-1 : 用戶名不合法
	-2 : 包含要允許註冊的詞語
	-3 : 用戶名已經存在
 */
function uc_user_checkname($username) {
	return call_user_func(UC_API_FUNC, 'user', 'check_username', array('username'=>$username));
}

/**
 * 檢查Email地址是否正確
 *
 * @param string $email		Email
 * @return
 *  	1  : 成功
 * 	-4 : email 格式有誤
 * 	-5 : email 不允許註冊
 * 	-6 : 該 email 已經被註冊
 */
function uc_user_checkemail($email) {
	return call_user_func(UC_API_FUNC, 'user', 'check_email', array('email'=>$email));
}

/**
 * 添加保護用戶
 *
 * @param string/array $username 保護用戶名
 * @param string $admin    操作的管理員
 * @return
 * 	-1 : 失敗
 * 	 1 : 成功
 */
function uc_user_addprotected($username, $admin='') {
	return call_user_func(UC_API_FUNC, 'user', 'addprotected', array('username'=>$username, 'admin'=>$admin));
}

/**
 * 刪除保護用戶
 *
 * @param string/array $username 保護用戶名
 * @return
 * 	-1 : 失敗
 * 	 1 : 成功
 */
function uc_user_deleteprotected($username) {
	return call_user_func(UC_API_FUNC, 'user', 'deleteprotected', array('username'=>$username));
}

/**
 * 得到受保護的用戶名列表
 *
 * @param empty
 * @return
 * 	受到保護的用戶名列表
 *  	array()
 */
function uc_user_getprotected() {
	$return = call_user_func(UC_API_FUNC, 'user', 'getprotected', array('1'=>1));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 取得用戶數據
 *
 * @param string $username	用戶名
 * @param int $isuid	是否為UID
 * @return array (uid, username, email)
 */
function uc_get_user($username, $isuid=0) {
	$return = call_user_func(UC_API_FUNC, 'user', 'get_user', array('username'=>$username, 'isuid'=>$isuid));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 用戶合併最後的處理
 *
 * @param string $oldusername	老用戶名
 * @param string $newusername	新用戶名
 * @param string $uid		老UID
 * @param string $password	密碼
 * @param string $email		Email
 * @return int
	-1 : 用戶名不合法
	-2 : 包含不允許註冊的詞語
	-3 : 用戶名已經存在
	>1 : 表示成功，數值為 UID
 */
function uc_user_merge($oldusername, $newusername, $uid, $password, $email) {
	return call_user_func(UC_API_FUNC, 'user', 'merge', array('oldusername'=>$oldusername, 'newusername'=>$newusername, 'uid'=>$uid, 'password'=>$password, 'email'=>$email));
}

/**
 * 移去合併用戶記錄
 * @param string $username	用戶名
 */
function uc_user_merge_remove($username) {
	return call_user_func(UC_API_FUNC, 'user', 'merge_remove', array('username'=>$username));
}

/**
 * 獲取指定應用的指定用戶積分值
 * @param int $appid	應用Id
 * @param int $uid	用戶Id
 * @param int $credit	積分編號
 */
function uc_user_getcredit($appid, $uid, $credit) {
	return uc_api_post('user', 'getcredit', array('appid'=>$appid, 'uid'=>$uid, 'credit'=>$credit));
}

/**
 * 進入短訊息界面
 *
 * @param int $uid	用戶ID
 * @param int $newpm	是否直接進入newpm
 */
function uc_pm_location($uid, $newpm = 0) {
	$apiurl = uc_api_url('pm_client', 'ls', "uid=$uid", ($newpm ? '&folder=newbox' : ''));
	@header("Expires: 0");
	@header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	@header("location: $apiurl");
}

/**
 * 檢查新短訊息
 *
 * @param  int $uid	用戶ID
 * @param  int $more	詳細信息
 * @return int	 	是否存在新短訊息
 * 	2	詳細	(短訊息數、公共訊息數、最後訊息時間, 最後訊息內容)
 * 	1	簡單	(短訊息數、公共訊息數、最後訊息時間)
 * 	0	否
 */
function uc_pm_checknew($uid, $more = 0) {
	$return = call_user_func(UC_API_FUNC, 'pm', 'check_newpm', array('uid'=>$uid, 'more'=>$more));
	return (!$more || UC_CONNECT == 'mysql') ? $return : uc_unserialize($return);
}

/**
 * 發送短訊息
 *
 * @param int $fromuid		發件人uid 0 為系統訊息
 * @param mix $msgto		收件人 uid/username 多個逗號分割
 * @param mix $subject		標題
 * @param mix $message		內容
 * @param int $instantly	立即發送 1 立即發送(預設)  0 進入短訊息發送界面
 * @param int $replypid		回覆的訊息Id
 * @param int $isusername	0 = $msgto 為 uid、1 = $msgto 為 username
 * @return
 * 	>1	發送成功的人數
 * 	0	收件人不存在
 */
function uc_pm_send($fromuid, $msgto, $subject, $message, $instantly = 1, $replypmid = 0, $isusername = 0) {
	if($instantly) {
		$replypmid = @is_numeric($replypmid) ? $replypmid : 0;
		return call_user_func(UC_API_FUNC, 'pm', 'sendpm', array('fromuid'=>$fromuid, 'msgto'=>$msgto, 'subject'=>$subject, 'message'=>$message, 'replypmid'=>$replypmid, 'isusername'=>$isusername));
	} else {
		$fromuid = intval($fromuid);
		$subject = rawurlencode($subject);
		$msgto = rawurlencode($msgto);
		$message = rawurlencode($message);
		$replypmid = @is_numeric($replypmid) ? $replypmid : 0;
		$replyadd = $replypmid ? "&pmid=$replypmid&do=reply" : '';
		$apiurl = uc_api_url('pm_client', 'send', "uid=$fromuid", "&msgto=$msgto&subject=$subject&message=$message$replyadd");
		@header("Expires: 0");
		@header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		@header("location: ".$apiurl);
	}
}

/**
 * 刪除短訊息
 *
 * @param int $uid		用戶Id
 * @param string $folder	打開的目錄 inbox=收件箱，outbox=發件箱
 * @param array	$pmids		要刪除的訊息ID數組
 * @return
 * 	>0 成功
 * 	<=0 失敗
 */
function uc_pm_delete($uid, $folder, $pmids) {
	return call_user_func(UC_API_FUNC, 'pm', 'delete', array('uid'=>$uid, 'folder'=>$folder, 'pmids'=>$pmids));
}

/**
 * 按照用戶刪除短訊息
 *
 * @param int $uid		用戶Id
 * @param array	$uids		要刪除的訊息用戶ID數組
 * @return
 * 	>0 成功
 * 	<=0 失敗
 */
function uc_pm_deleteuser($uid, $touids) {
	return call_user_func(UC_API_FUNC, 'pm', 'deleteuser', array('uid'=>$uid, 'touids'=>$touids));
}

/**
 * 標記已讀/未讀狀態
 *
 * @param int $uid		用戶Id
 * @param array	$uids		要標記已讀狀態的用戶ID數組
 * @param array	$pmids		要標記已讀狀態的訊息ID數組
 * @param int $status		1 已讀 0 未讀
 */
function uc_pm_readstatus($uid, $uids, $pmids = array(), $status = 0) {
	return call_user_func(UC_API_FUNC, 'pm', 'readstatus', array('uid'=>$uid, 'uids'=>$uids, 'pmids'=>$pmids, 'status'=>$status));
}

/**
 * 獲取短訊息列表
 *
 * @param int $uid		用戶Id
 * @param int $page 		當前頁
 * @param int $pagesize 	每頁最大條目數
 * @param string $folder	打開的目錄 newbox=未讀訊息，inbox=收件箱，outbox=發件箱
 * @param string $filter	過濾方式 newpm=未讀訊息，systempm=系統訊息，announcepm=公共訊息
 				$folder		$filter
 				--------------------------
 				newbox
 				inbox		newpm
 						systempm
 						announcepm
 				outbox		newpm
 				searchbox	*
 * @param string $msglen 	截取的訊息文字長度
 * @return array('count' => 訊息總數, 'data' => 短訊息數據)
 */
function uc_pm_list($uid, $page = 1, $pagesize = 10, $folder = 'inbox', $filter = 'newpm', $msglen = 0) {
	$uid = intval($uid);
	$page = intval($page);
	$pagesize = intval($pagesize);
	$return = call_user_func(UC_API_FUNC, 'pm', 'ls', array('uid'=>$uid, 'page'=>$page, 'pagesize'=>$pagesize, 'folder'=>$folder, 'filter'=>$filter, 'msglen'=>$msglen));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 忽略未讀訊息提示
 *
 * @param int $uid		用戶Id
 */
function uc_pm_ignore($uid) {
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'ignore', array('uid'=>$uid));
}

/**
 * 獲取短訊息內容
 *
 * @param int $uid		用戶Id
 * @param int $pmid		訊息Id
 * @param int $touid		訊息對方用戶Id
 * @param int $daterange	日期範圍 1=今天,2=昨天,3=前天,4=上周,5=更早
 * @return array() 短訊息內容數組
 */
function uc_pm_view($uid, $pmid, $touid = 0, $daterange = 1) {
	$uid = intval($uid);
	$touid = intval($touid);
	$pmid = @is_numeric($pmid) ? $pmid : 0;
	$return = call_user_func(UC_API_FUNC, 'pm', 'view', array('uid'=>$uid, 'pmid'=>$pmid, 'touid'=>$touid, 'daterange'=>$daterange));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 獲取單條短訊息內容
 *
 * @param int $uid		用戶Id
 * @param int $pmid		訊息Id
 * @param int $type		0 = 獲取指定單條訊息
 				1 = 獲取指定用戶發的最後單條訊息
 				2 = 獲取指定用戶收的最後單條訊息
 * @return array() 短訊息內容數組
 */
function uc_pm_viewnode($uid, $type = 0, $pmid = 0) {
	$uid = intval($uid);
	$pmid = @is_numeric($pmid) ? $pmid : 0;
	$return = call_user_func(UC_API_FUNC, 'pm', 'viewnode', array('uid'=>$uid, 'pmid'=>$pmid, 'type'=>$type));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 獲取黑名單
 *
 * @param int $uid		用戶Id
 * @return string 黑名單內容
 */
function uc_pm_blackls_get($uid) {
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'blackls_get', array('uid'=>$uid));
}

/**
 * 設置黑名單
 *
 * @param int $uid		用戶Id
 * @param int $blackls		黑名單內容
 */
function uc_pm_blackls_set($uid, $blackls) {
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'blackls_set', array('uid'=>$uid, 'blackls'=>$blackls));
}

/**
 * 添加黑名單項目
 *
 * @param int $uid		用戶Id
 * @param int $username		用戶名
 */
function uc_pm_blackls_add($uid, $username) {
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'blackls_add', array('uid'=>$uid, 'username'=>$username));
}

/**
 * 刪除黑名單項目
 *
 * @param int $uid		用戶Id
 * @param int $username		用戶名
 */
function uc_pm_blackls_delete($uid, $username) {
	$uid = intval($uid);
	return call_user_func(UC_API_FUNC, 'pm', 'blackls_delete', array('uid'=>$uid, 'username'=>$username));
}

/**
 * 獲取域名解析表
 *
 * @return array()
 */
function uc_domain_ls() {
	$return = call_user_func(UC_API_FUNC, 'domain', 'ls', array('1'=>1));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 積分兌換請求
 *
 * @param int $uid		用戶ID
 * @param int $from		原積分
 * @param int $to		目標積分
 * @param int $toappid		目標應用ID
 * @param int $amount		積分數額
 * @return
 *  	1  : 成功
 *	0  : 失敗
 */
function uc_credit_exchange_request($uid, $from, $to, $toappid, $amount) {
	$uid = intval($uid);
	$from = intval($from);
	$toappid = intval($toappid);
	$to = intval($to);
	$amount = intval($amount);
	return uc_api_post('credit', 'request', array('uid'=>$uid, 'from'=>$from, 'to'=>$to, 'toappid'=>$toappid, 'amount'=>$amount));
}

/**
 * 返回指定的相關TAG數據
 *
 * @param string $tagname	TAG名稱
 * @param int $totalnum		返回數據的條目數
 * @return array() 序列化過的數組，數組內容為當前或其他應用的相關TAG數據
 */
function uc_tag_get($tagname, $nums = 0) {
	$return = call_user_func(UC_API_FUNC, 'tag', 'gettag', array('tagname'=>$tagname, 'nums'=>$nums));
	return UC_CONNECT == 'mysql' ? $return : uc_unserialize($return);
}

/**
 * 修改頭像
 *
 * @param	int		$uid	用戶ID
 * @param	string	$type	頭像類型 real OR virtual 預設為 virtual
 * @return	string
 */
function uc_avatar($uid, $type = 'virtual', $returnhtml = 1) {
	$uid = intval($uid);
	$uc_input = uc_api_input("uid=$uid");
	$uc_avatarflash = UC_API.'/images/camera.swf?inajax=1&appid='.UC_APPID.'&input='.$uc_input.'&agent='.md5($_SERVER['HTTP_USER_AGENT']).'&ucapi='.urlencode(str_replace('http://', '', UC_API)).'&avatartype='.$type.'&uploadSize=2048';
	if($returnhtml) {
		return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="450" height="253" id="mycamera" align="middle">
			<param name="allowScriptAccess" value="always" />
			<param name="scale" value="exactfit" />
			<param name="wmode" value="transparent" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#ffffff" />
			<param name="movie" value="'.$uc_avatarflash.'" />
			<param name="menu" value="false" />
			<embed src="'.$uc_avatarflash.'" quality="high" bgcolor="#ffffff" width="450" height="253" name="mycamera" align="middle" allowScriptAccess="always" allowFullScreen="false" scale="exactfit"  wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
		</object>';
	} else {
		return array(
			'width', '450',
			'height', '253',
			'scale', 'exactfit',
			'src', $uc_avatarflash,
			'id', 'mycamera',
			'name', 'mycamera',
			'quality','high',
			'bgcolor','#ffffff',
			'wmode','transparent',
			'menu', 'false',
			'swLiveConnect', 'true',
			'allowScriptAccess', 'always'
		);
	}
}

/**
 * 郵件隊列
 *
 * @param	string	$uids		用戶名id，多個用逗號(,)隔開
 * @param	string	$emails		郵件地址，多個用逗號隔開
 * @param	string	$subject	郵件標題
 * @param	string	$message	郵件內容
 * @param	string	$charset	郵件字符集，可選參數，預設為gbk
 * @param	boolean	$htmlon		是否按html格式發送郵件，可選參數，預設為否
 * @param	integer $level		郵件級別，可選參數，取值0-127，預設為1，越大發送的優先級越高，為0時不入庫，直接發送，會影響當前進程速度，慎用
 * @return	integer
 *		=0 : 失敗
 *		>0 : 成功，返回插入記錄的id，如果是多條則返回最後一條記錄的id，若level等於0，則返回1
 */
function uc_mail_queue($uids, $emails, $subject, $message, $frommail = '', $charset = 'gbk', $htmlon = FALSE, $level = 1) {
	return call_user_func(UC_API_FUNC, 'mail', 'add', array('uids' => $uids, 'emails' => $emails, 'subject' => $subject, 'message' => $message, 'frommail' => $frommail, 'charset' => $charset, 'htmlon' => $htmlon, 'level' => $level));
}

/**
 * 檢測是否存在指定頭像
 * @param	integer		$uid	用戶id
 * @param	string		$size	頭像尺寸，取值範圍(big,middle,small)，預設為 middle
 * @param	string		$type	頭像類型，取值範圍(virtual,real)，預設為virtual
 * @return	boolean
 *		true : 頭像存在
 *		false: 頭像不存在
 */
function uc_check_avatar($uid, $size = 'middle', $type = 'virtual') {
	$url = UC_API."/avatar.php?uid=$uid&size=$size&type=$type&check_file_exists=1";
	$res = uc_fopen2($url, 500000, '', '', TRUE, UC_IP, 20);
	if($res == 1) {
		return 1;
	} else {
		return 0;
	}
}

/**
 * 檢測uc_server的數據庫版本和程序版本
 * @return mixd
 *		array('db' => 'xxx', 'file' => 'xxx');
 *		null 無法調用到接口
 *		string 文件版本低於1.5
 */
function uc_check_version() {
	$return = uc_api_post('version', 'check', array());
	$data = uc_unserialize($return);
	return is_array($data) ? $data : $return;
}

// bluelovers
function uc_api_call($module, $action, $arg = array()) {
	$return = call_user_func(UC_API_FUNC, $module, $action, $arg);

	if (UC_CONNECT != 'mysql' && strpos($return, '<?xml') !== FALSE) {
		$return2 = null;
		$return2 = @uc_unserialize($return);
		$return = $return2 === null ? $return : $return2;
	}

	return $return;
}
// bluelovers

?>