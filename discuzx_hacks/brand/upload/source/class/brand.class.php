<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: db.class.php 4067 2010-07-30 08:38:14Z fanshengshuai $
 */

class brand {
	var $db = null;
	var $mem = null;
	var $config = array();
	var $superglobal = array(
		'GLOBALS' => 1,
		'_GET' => 1,
		'_POST' => 1,
		'_REQUEST' => 1,
		'_COOKIE' => 1,
		'_SERVER' => 1,
		'_ENV' => 1,
		'_FILES' => 1,
	);

	function __construct() {
	}
	function brand() {
	}
	function init() {
		global $_G;
		$brand = new brand();
		$brand->_init_env();
		$brand->_init_config();
		$brand->check_install();
		$brand->_init_input();
		$brand->_init_db();
		$brand->_init_cache();
		if(!defined('IN_BRAND_UPDATE')) {
			$brand->auth();
		}

	}
	function _init_env() {

		error_reporting(E_ERROR);
		if(phpversion() < '5.3.0') {
			set_magic_quotes_runtime(0);
		}

		define('MAGIC_QUOTES_GPC', function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc());
		define('ICONV_ENABLE', function_exists('iconv'));
		define('MB_ENABLE', function_exists('mb_convert_encoding'));
		define('FORMHASH', formhash());

		define('TIMESTAMP', time());
		$_SERVER['HTTP_USER_AGENT'] = empty($_SERVER['HTTP_USER_AGENT'])?'':$_SERVER['HTTP_USER_AGENT'];
		foreach ($GLOBALS as $key => $value) {
			if (!isset($this->superglobal[$key])) {
				$GLOBALS[$key] = null; unset($GLOBALS[$key]);
			}
		}

		global $_G;
		$_G = array(
			'uid' => 0,
			'username' => 'Guest',
			'formhash' => '',
			'timestamp' => TIMESTAMP,
			'starttime' => array_sum(explode(' ', microtime())),
			'clientip' => $this->_get_client_ip(),
			'referer' => '',
			'charset' => '',
			'timenow' => array(),
			'cookiepre' => '',

			'PHP_SELF' => '',
			'siteurl' => '',
			'siteroot' => '',
			
			'authkey' => '',

			'config' => array(),
			'setting' => array('sitetheme'=>'default'),
			'member' => array(),
			'cookie' => array(),
			'style' => array(),
			'cache' => array()
		);
		$_G['PHP_SELF'] = htmlspecialchars($_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']);
		$_G['basescript'] = CURSCRIPT;
		$_G['basefilename'] = basename($_G['PHP_SELF']);
		$_G['siteurl'] = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api)?\/*$/i", '', substr($_G['PHP_SELF'], 0, strrpos($_G['PHP_SELF'], '/'))).'/');
		$_G['siteroot'] = substr($_G['PHP_SELF'], 0, -strlen($_G['basefilename']));
	}
	function _init_config() {
		global $_G, $_SC;
		@include B_ROOT.'./config.php';
		//@include_once B_ROOT.'./data/system/g.cache.php';
		@include_once B_ROOT.'./data/system/config.cache.php';
		define('B_URL', $_SC['siteurl']);
		!$_G['setting']['sitetheme'] && $_G['setting']['sitetheme']='default';
		$_G['charset'] = $_SC['charset'];
		$_G['config']['cookie'] =  array('cookiepre' => $_SC['cookiepre'], 'cookiepath'=>$_SC['cookiepath'], 'cookiedomain'=>$_SC['cookiedomain']);
		$_G['sitekey'] = $_G['setting']['sitekey'];
		$_G['authkey'] = md5($_G['sitekey'].UC_KEY);
		$_G['config']['db'][1] = array('dbhost' => $_SC['dbhost'],
			'dbuser' => $_SC['dbuser'],
			'dbpw' => $_SC['dbpw'],
			'dbcharset' => $_SC['dbcharset'],
			'dbname' => $_SC['dbname'],
			'tablepre' => $_SC['tablepre'],
			'pconnect' => $_SC['pconnect']);
		$_G['config']['bbs'] = array('dbhost' => $_SC['bbs_dbhost'],
			'dbuser' => $_SC['bbs_dbuser'],
			'dbpw' => $_SC['bbs_dbpw'],
			'dbcharset' => $_SC['bbs_dbcharset'],
			'dbname' => $_SC['bbs_dbname'],
			'tablepre' => $_SC['bbs_dbpre'],
			'pconnect' => $_SC['bbs_pconnect'],
			'bbs_url' => $_SC['bbs_url'],
			'bbs_version' => $_SC['bbs_version']
		);
		
		$this->config = &$_G['config'];
	}
	function _init_input() {
		global $_G;
		if(!MAGIC_QUOTES_GPC) {
			$_GET = saddslashes($_GET);
			$_POST = saddslashes($_POST);
			$_COOKIE = saddslashes($_COOKIE);
			$_FILES = saddslashes($_FILES);
		}
		$prelength = strlen($_G['config']['cookie']['cookiepre']);
		foreach($_COOKIE as $key => $value) {
			if(substr($key, 0, $prelength) == $_G['config']['cookie']['cookiepre']) {
				$_G['cookie'][(substr($key, $prelength))] = $value;
			}
		}
		$_G['inajax'] = empty($_GET['inajax'])?0:intval($_GET['inajax']);
		$_G['page'] = $_GET['page'] = isset($_GET['page']) && intval($_GET['page'])>0?intval($_GET['page']):1;
		if(substr($_G['setting']['attachmentdir'], 0, 2) == './') {
			define('A_DIR', B_ROOT.$_G['setting']['attachmentdir']);
		} else {
			define('A_DIR', $_G['setting']['attachmentdir']);
		}
		if(empty($_G['setting']['attachmenturl']) && substr($_G['setting']['attachmentdir'], 0, 2) == './') {
			$_G['setting']['attachmenturl'] = substr($_G['setting']['attachmentdir'], 2);
		}
		if(empty($_G['setting']['attachmenturl'])) {
			$_G['setting']['attachmenturl'] = 'attachments';
		}
		
		// 外部调用带绝对地址
		define('A_URL', B_URL.'/'.$_G['setting']['attachmenturl']);
	}
	function _init_cache() {
		global $_G;
		data_cache_start();
		if (defined('CURSCRIPT')) {
			require_once(B_ROOT.'./source/function/cache.func.php');
			updatebrandadscache(false, 86430);
		}
	}
	function _init_db() {
		include_once(B_ROOT.'./source/class/db.class.php');
		$this->db = & DB::object();
		$this->db->set_config($this->config['db']);
		$this->db->connect();
	}
	function auth() {
		global $_G;
		$cookie = $_G['cookie']['auth'];
		if($cookie) {
			@list($password, $uid) = explode("\t", authcode($cookie, 'DECODE'));
	
			$uid = intval($uid);
			$password = addslashes($password);

			$member = DB::fetch_first('SELECT * FROM '.tname('members').' WHERE uid=\''.$uid.'\' AND password=\''.$password.'\'');
			if($member) {
				$_G['uid'] = $uid;
				$_G['username'] = addslashes($member['username']);
				$_G['email'] = addslashes($member['email']);
				$_G['myshopid'] = intval($member['myshopid']);
				$_G['member']['shopcount'] = 0;
				$_G['member'] = $member;

				if ($_G['myshopid'] > 0) {
					require_once B_ROOT."./source/class/shop.class.php";
					$_G['myshopsarr'] = shop::ls_myshops();
					$_G['member']['shopcount'] = count($_G['myshops']);
				}
			}
		}
		//if(!$_G['uid']) { sclearcookie(); return ;}
		if (IN_STORE === true) {
			if(pkperm('isadmin')) {
				showmessage('admin_no_perm_to_panel', 'index.php');
			} elseif ($_G['myshopid'] < 0) {
				showmessage('no_perm', 'index.php');
			} else {
				$shop_grade = $_G['myshopsarr'][$_G['myshopid']]['grade'];
				if ($shop_grade <= 1){
					$_G['myshopstatus'] = 'unverified';
				} elseif ($shop_grade > 1) {
					$_G['myshopstatus'] = 'verified';
				}
			}
		}
	}
	function _update_cache() {
		
	}
	function _run_cron () {
		global $_G, $_SGLOBAL;
		// 计划任务
		@include_once(B_ROOT.'./data/system/cron.cache.php');
		if(empty($_SGLOBAL['cronnextrun']) || $_SGLOBAL['cronnextrun'] <= $_G['timestamp']) {
			include_once(B_ROOT.'./source/function/cron.func.php');
			runcron();
		}
	}
	function _get_client_ip() {
		$clientip = '';
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$clientip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$clientip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$clientip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$clientip = $_SERVER['REMOTE_ADDR'];
		}

		preg_match("/[\d\.]{7,15}/", $clientip, $clientipmatches);
		$clientip = $clientipmatches[0] ? $clientipmatches[0] : 'unknown';
		return $clientip;
	}
	function error($msg, $halt = true) {
		$this->error_log($msg);
		echo $msg;
		$halt && exit();
	}

	function error_log($message) {
		$time = TIMESTAMP;
		$file =  DISCUZ_ROOT.'./data/log/'.date("Ym").'_errorlog.php';
		$hash = md5($message);
		$message = "<?PHP exit;?>\t{$time}\t".str_replace(array("\t", "\r", "\n"), " ", $message)."\t$hash\n";
		if($fp = @fopen($file, 'rb')) {
			$lastlen = 10000;
			$maxtime = 100;
			$offset = filesize($file) - $lastlen;
			if($offset > 0) {
				fseek($fp, $offset);
			}
			if($data = fread($fp, $lastlen)) {
				$array = explode("\n", $data);
				if(is_array($array)) foreach($array as $key => $val) {
					$row = explode("\t", $val);
					if($row[0] != '<?PHP exit;?>') continue;
					if($row[3] == $hash && ($row[1] > $time - $maxtime)) {
						return;
					}
				}
			}
		}
		error_log($message, 3, $file);
	}
	function check_install(){
		if(!@include_once(B_ROOT.'./config.php')) {
			header("Location: install/index.php");
			exit();
		}
	}
}
?>