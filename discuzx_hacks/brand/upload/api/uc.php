<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: uc.php 4374 2010-09-08 08:58:55Z fanshengshuai $
 */

define('UC_CLIENT_VERSION', '1.5.0');	//note UCenter 版本标识
define('UC_CLIENT_RELEASE', '20081031');

define('API_DELETEUSER', 1);		//note 用户删除 API 接口开关
define('API_RENAMEUSER', 1);		//note 用户改名 API 接口开关
define('API_GETTAG', 1);		//note 获取标签 API 接口开关
define('API_SYNLOGIN', 1);		//note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1);		//note 同步登出 API 接口开关
define('API_UPDATEPW', 1);		//note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1);	//note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1);		//note 更新域名解析缓存 开关
define('API_UPDATEAPPS', 1);		//note 更新应用列表 开关
define('API_UPDATECLIENT', 1);		//note 更新客户端缓存 开关
define('API_UPDATECREDIT', 1);		//note 更新用户积分 开关
define('API_GETCREDIT', 1);	//向 UC 提供积分 开关
define('API_GETCREDITSETTINGS', 1);	//note 向 UCenter 提供积分设置 开关
define('API_UPDATECREDITSETTINGS', 1);	//note 更新应用积分设置 开关
define('API_ADDFEED', 1);	//向 UCHome 添加feed 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('IN_BRAND', true);
define('B_ROOT', substr(dirname(__FILE__), 0, -3));

//获取时间
$_G['timestamp'] = time();

if(defined('IN_UC')) {

	global $_G, $_SGLOBAL, $_SC, $_SCOOKIE;

	include_once(B_ROOT.'./common.php');
	include_once(B_ROOT.'./source/function/common.func.php');
	include_once(B_ROOT.'./data/system/config.cache.php');

	//链接数据库
	dbconnect();

} else {

	error_reporting(0);
	set_magic_quotes_runtime(0);

	defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

	include_once(B_ROOT.'./common.php');
	include_once(B_ROOT.'./source/function/common.func.php');
	include_once(B_ROOT.'./data/system/config.cache.php');

	//链接数据库
	dbconnect();

	$get = $post = array();

	$code = @$_GET['code'];
	parse_str(authcode($code, 'DECODE', UC_KEY), $get);
	if(MAGIC_QUOTES_GPC) {
		$get = sstripslashes($get);
	}

	if($_G['timestamp'] - $get['time'] > 3600) {
		exit('Authracation has expiried');
	}
	if(empty($get)) {
		exit('Invalid Request');
	}

	include_once B_ROOT.'./uc_client/lib/xml.class.php';
	$post = xml_unserialize(file_get_contents('php://input'));

	if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings', 'addfeed'))) {
		$uc_note = new uc_note();
		echo $uc_note->$get['action']($get, $post);
		exit();
	} else {
		exit(API_RETURN_FAILED);
	}

}

class uc_note {

	var $dbconfig = '';
	var $db = '';
	var $tablepre = '';
	var $appdir = '';

	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once B_ROOT.'./uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() {
		global $_G, $_SGLOBAL, $_SC;
		$this->appdir = substr(dirname(__FILE__), 0, -3);
		$this->dbconfig = B_ROOT.'./config.php';
		//$this->db = $_SGLOBAL['db'];
		$this->tablepre = $_SC['tablepre'];
	}

	function test($get, $post) {
		return API_RETURN_SUCCEED;
	}

	function deleteuser($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_DELETEUSER) {
			return API_RETURN_FORBIDDEN;
		}

		//note 用户删除 API 接口
		include_once B_ROOT.'./source/function/admin.func.php';

		//获得用户
		$uids = $get['ids'];
		$query = DB::query("SELECT uid FROM ".tname('members')." WHERE uid IN ($uids)");
		while ($value = DB::fetch($query)) {
			deletespace($value['uid']);
		}
		return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}

		//编辑用户
		$old_username = $get['oldusername'];
		$new_username = $get['newusername'];

		DB::query("UPDATE ".tname('members')." SET username='$new_username' WHERE username='$old_username'");
		return API_RETURN_SUCCEED;
	}

	function gettag($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_GETTAG) {
			return API_RETURN_FORBIDDEN;
		}

		return API_RETURN_SUCCEED;
	}

	function synlogin($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_SYNLOGIN) {
			return API_RETURN_FORBIDDEN;
		}

		//note 同步登录 API 接口
		obclean();
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$uid = intval($get['uid']);

		$cookietime = 2592000;
		$ss_auth_key = md5($_G['setting']['sitekey'].$_SERVER['HTTP_USER_AGENT']);
		include_once(B_ROOT.'./source/class/db_mysql.class.php');
		//链接数据库
		dbconnect();

		$query = DB::query("SELECT * FROM ".tname('members')." WHERE uid='$uid'");
		if($member = DB::fetch($query)) {
			ssetcookie('sid', '', 86400 * 365);
			ssetcookie('cookietime', $cookietime, 31536000);
			ssetcookie('auth', authcode("$member[password]\t$member[uid]", 'ENCODE'), $cookietime, 1, true);
		} else {
			ssetcookie('cookietime', $cookietime, 31536000);
			ssetcookie('loginuser', $get['username'], $cookietime);
			ssetcookie('activationauth', authcode($get['username'], 'ENCODE'), $cookietime);
		}
	}

	function synlogout($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}

		//note 同步登出 API 接口
		obclean();
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		ssetcookie('auth', '', -86400 * 365);
		ssetcookie('sid', '', -86400 * 365);
		ssetcookie('loginuser', '', -86400 * 365);
		ssetcookie('activationauth', '', -86400 * 365);
	}

	function updatepw($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_UPDATEPW) {
			return API_RETURN_FORBIDDEN;
		}

		//note 同步登出 API 接口
		obclean();
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		ssetcookie('auth', '', -86400 * 365);
		ssetcookie('sid', '', -86400 * 365);
		ssetcookie('loginuser', '', -86400 * 365);
		ssetcookie('activationauth', '', -86400 * 365);

		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}

		$cachefile = UC_CLIENT_ROOT.'./data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($post, true).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_UPDATEHOSTS) {
			return API_RETURN_FORBIDDEN;
		}

		$cachefile = B_ROOT.'./uc_client/data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, true).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}

		$UC_API = '';
		if($post['UC_API']) {
			$UC_API = $post['UC_API'];
			unset($post['UC_API']);
		}

		$cachefile = B_ROOT.'./uc_client/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, true).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		//配置文件
		if($UC_API && is_writeable(B_ROOT.'./config.php')) {
			$configfile = trim(file_get_contents(B_ROOT.'./config.php'));
			$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
			$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$UC_API');", $configfile);
			if($fp = @fopen(B_ROOT.'./config.php', 'w')) {
				@fwrite($fp, trim($configfile));
				@fclose($fp);
			}
		}
		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}

		$cachefile = B_ROOT.'./uc_client/data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, true).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updatecredit($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}

		$amount = $get['amount'];
		$uid = intval($get['uid']);

		DB::query("UPDATE ".tname('members')." SET credit=credit+'$amount' WHERE uid='$uid'");

		return API_RETURN_SUCCEED;
	}

	function getcredit($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_GETCREDIT) {
			return API_RETURN_FORBIDDEN;
		}

		$uid = intval($get['uid']);
		$credit = getcount('members', array('uid'=>$uid), 'credit');
		return $credit;
	}

	function getcreditsettings($get, $post) {
		global $_G, $_SGLOBAL, $lang;

		if(!API_GETCREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}

		$credits = array();
		$credits[1] = array($lang['credit'], $lang['credit_unit']);

		return $this->_serialize($credits);
	}

	function updatecreditsettings($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}

		$outextcredits = array();

		foreach($get['credit'] as $appid => $credititems) {
			if($appid == UC_APPID) {
				foreach($credititems as $value) {
					$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
						'creditsrc' => $value['creditsrc'],
						'title' => $value['title'],
						'unit' => $value['unit'],
						'ratio' => $value['ratio']
					);
				}
			}
		}

		$cachefile = B_ROOT.'./uc_client/data/cache/creditsettings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'creditsettings\'] = '.arrayeval($outextcredits).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function addfeed($get, $post) {
		global $_G, $_SGLOBAL;

		if(!API_ADDFEED) {
			return API_RETURN_FORBIDDEN;
		}

		return API_RETURN_SUCCEED;
	}
}

?>