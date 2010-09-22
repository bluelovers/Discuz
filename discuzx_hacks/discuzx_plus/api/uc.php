<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: uc.php 616 2010-09-08 12:13:02Z yexinhao $
 */

define('UC_CLIENT_VERSION', '1.5.1');	//note UCenter ঱ʶ
define('UC_CLIENT_RELEASE', '20100501');

define('API_DELETEUSER', 1);		//Ӄ막 API 퓿ڿ깘
define('API_RENAMEUSER', 1);		//Ӄ맃됞脠API 퓿ڿ깘
define('API_GETTAG', 1);		//뱈ᱪǩ API 퓿ڿ깘
define('API_SYNLOGIN', 1);		//ͬ⽵ǂ적PI 퓿ڿ깘
define('API_SYNLOGOUT', 1);		//ͬ⽵ǳ柳PI 퓿ڿ깘
define('API_UPDATEPW', 1);		//輸ēu烜« 調؊define('API_UPDATEBADWORDS', 1);	//輐1ؼ엖P᭠調؊define('API_UPDATEHOSTS', 1);		//輐OST΄쾠調؊define('API_UPDATEAPPS', 1);		//輐擃P᭠調؊define('API_UPDATECLIENT', 1);		//輐¿ͻ綋뺴樑깘
define('API_UPDATECREDIT', 1);		//輐u綻癘調؊define('API_GETCREDIT', 1);	//ϲ UC ̡駐��� 調؊define('API_GETCREDITSETTINGS', 1);	//ϲ UC ̡駐���ɨփ 調؊define('API_UPDATECREDITSETTINGS', 1);	//輐擃뽷։薃 調؊define('API_ADDFEED', 1);	//ϲ UCHome ̭쓦eed 調؊
define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '1');
define('IN_DISCUZ', true);
define('DISCUZ_ROOT', dirname(dirname(__FILE__)).'/');
define('CURSCRIPT', 'api');

error_reporting(0);
require_once DISCUZ_ROOT.'./config/config_global.php';
require_once DISCUZ_ROOT.'./config/config_ucenter.php';
require_once DISCUZ_ROOT.'./source/function/function_core.php';
require_once DISCUZ_ROOT.'./source/class/class_core.php';

$discuz = & discuz_core::instance();
$discuz->init();

require DISCUZ_ROOT.'./config/config_ucenter.php';

$get = $post = array();

$code = @$_GET['code'];
parse_str(authcode($code, 'DECODE', UC_KEY), $get);

if(time() - $get['time'] > 3600) {
	exit('Authracation has expiried');
}
if(empty($get)) {
	exit('Invalid Request');
}

include_once DISCUZ_ROOT.'./uc_client/lib/xml.class.php';
$post = xml_unserialize(file_get_contents('php://input'));

if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcredit', 'getcreditsettings', 'updatecreditsettings', 'addfeed'))) {
	$uc_note = new uc_note();
	echo $uc_note->$get['action']($get, $post);
	exit();
} else {
	exit(API_RETURN_FAILED);
}

class uc_note {

	var $dbconfig = '';
	var $db = '';
	var $tablepre = '';
	var $appdir = '';

	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once DISCUZ_ROOT.'./uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() {

	}

	function test($get, $post) {
		return API_RETURN_SUCCEED;
	}

	function deleteuser($get, $post) {
		global $_G;
		if(!API_DELETEUSER) {
			return API_RETURN_FORBIDDEN;
		}
		$uids = str_replace("'", '', stripcslashes($get['ids']));
		$ids = 0;
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE uid IN ($uids)");
		while($row = DB::fetch($query)) {
			$ids .= $row['uid'];
		}
		$tables = array(
			'common_member' => array('id' => 'uid'),
			'common_admincp_member' => array('id' => 'uid'),
			'common_admincp_session' => array('id' => 'uid'),
			'common_adminsession' => array('id' => 'uid'),
			'common_member_credit' => array('id' => 'uid'),
			'common_member_status' => array('id' => 'uid')
		);

		foreach($tables as $table => $conf) {
			DB::query("DELETE FROM ".DB::table($table)." WHERE `$conf[id]` IN ($ids)", 'UNBUFFERED');
		}

		return API_RETURN_SUCCEED;
	}

	function renameuser($get, $post) {
		global $_G;

		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}
		$tables = array(
			'common_member' => array('id' => 'uid', 'name' => 'username'),
		);

		foreach($tables as $table => $conf) {
			DB::query("UPDATE ".DB::table($table)." SET `$conf[name]`='$get[newusername]' WHERE `$conf[id]`='$get[uid]' AND `$conf[name]`='$get[oldusername]'");
		}
		return API_RETURN_SUCCEED;
	}

	function gettag($get, $post) {
		global $_G;
		if(!API_GETTAG) {
			return API_RETURN_FORBIDDEN;
		}
		return $this->_serialize(array($get['id'], array()), 1);
	}

	function synlogin($get, $post) {
		global $_G;

		if(!API_SYNLOGIN) {
			return API_RETURN_FORBIDDEN;
		}

		//note ͬ⽵ǂ적PI 퓿ڊ		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$cookietime = 31536000;
		$uid = intval($get['uid']);
		$query = DB::query("SELECT uid, username, password FROM ".DB::table('common_member')." WHERE uid='$uid'");
		if($member = DB::fetch($query)) {
			dsetcookie('auth', authcode("$member[password]\t$member[uid]", 'ENCODE'), $cookietime);
		} elseif(!empty($_G['setting']['autoactivationuser'])) {
			//ה毼反
			require_once libfile('function/login');
			$result = autoactivationuser($uid);
			if($result) {
				setloginstatus($result, $cookietime);
			}
		}
	}

	function synlogout($get, $post) {
		global $_G;

		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}

		//note ͬ⽵ǳ柳PI 퓿ڊ		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

		dsetcookie('auth', '', -31536000);
	}

	function updatepw($get, $post) {
		global $_G;

		if(!API_UPDATEPW) {
			return API_RETURN_FORBIDDEN;
		}

		$username = $get['username'];
		$newpw = md5(time().rand(100000, 999999));
		DB::query("UPDATE ".DB::table('common_member')." SET password='$newpw' WHERE username='$username'");

		return API_RETURN_SUCCEED;
	}

	function updatebadwords($get, $post) {
		global $_G;

		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}

		$data = array();
		if(is_array($post)) {
			foreach($post as $k => $v) {
				$data['findpattern'][$k] = $v['findpattern'];
				$data['replace'][$k] = $v['replacement'];
			}
		}
		$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updatehosts($get, $post) {
		global $_G;

		if(!API_UPDATEHOSTS) {
			return API_RETURN_FORBIDDEN;
		}

		$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updateapps($get, $post) {
		global $_G;

		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}

		$UC_API = '';
		if($post['UC_API']) {
			$UC_API = $post['UC_API'];
			unset($post['UC_API']);
		}

		$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		//Ťփ΄쾊		if($UC_API && is_writeable(DISCUZ_ROOT.'./config/config_ucenter.php')) {
			if(preg_match('/^https?:\/\//is', $UC_API)) {
				$configfile = trim(file_get_contents(DISCUZ_ROOT.'./config/config_ucenter.php'));
				$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
				$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '".addslashes($UC_API)."');", $configfile);
				if($fp = @fopen(DISCUZ_ROOT.'./config/config_ucenter.php', 'w')) {
					@fwrite($fp, trim($configfile));
					@fclose($fp);
				}
			}
		}
		return API_RETURN_SUCCEED;
	}

	function updateclient($get, $post) {
		global $_G;

		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}

		$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function updatecredit($get, $post) {
		global $_G;

		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}

		$credit = $get['credit'];
		$amount = $get['amount'];
		$uid = $get['uid'];
		if(!DB::result_first("SELECT count(*) FROM ".DB::table('common_member')." WHERE uid='$uid'")) {
			return API_RETURN_SUCCEED;
		}

		updatemembercount($uid, array($credit => $amount));
		DB::insert('common_credit_log', array('uid' => $uid, 'operation' => 'ECU', 'relatedid' => $uid, 'dateline' => time(), 'extcredits'.$credit => $amount));

		return API_RETURN_SUCCEED;
	}

	function getcredit($get, $post) {
		global $_G;

		if(!API_GETCREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		$uid = intval($get['uid']);
		$credit = intval($get['credit']);
		$_G['uid'] = $uid;
		return getuserprofile('extcredits'.$credit);
	}

	function getcreditsettings($get, $post) {
		global $_G;

		if(!API_GETCREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}

		$credits = array();
		foreach($_G['setting']['extcredits'] as $id => $extcredits) {
			$credits[$id] = array(strip_tags($extcredits['title']), $extcredits['unit']);
		}

		return $this->_serialize($credits);
	}

	function updatecreditsettings($get, $post) {
		global $_G;

		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}

		$outextcredits = array();
		foreach($get['credit'] as $appid => $credititems) {
			if($appid == UC_APPID) {
				foreach($credititems as $value) {
					$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
						'appiddesc' => $value['appiddesc'],
						'creditdesc' => $value['creditdesc'],
						'creditsrc' => $value['creditsrc'],
						'title' => $value['title'],
						'unit' => $value['unit'],
						'ratiosrc' => $value['ratiosrc'],
						'ratiodesc' => $value['ratiodesc'],
						'ratio' => $value['ratio']
					);
				}
			}
		}
		$tmp = array();
		foreach($outextcredits as $value) {
			$key = $value['appiddesc'].'|'.$value['creditdesc'];
			if(!isset($tmp[$key])) {
				$tmp[$key] = array('title' => $value['title'], 'unit' => $value['unit']);
			}
			$tmp[$key]['ratiosrc'][$value['creditsrc']] = $value['ratiosrc'];
			$tmp[$key]['ratiodesc'][$value['creditsrc']] = $value['ratiodesc'];
			$tmp[$key]['creditsrc'][$value['creditsrc']] = $value['ratio'];
		}
		$outextcredits = $tmp;

		$cachefile = DISCUZ_ROOT.'./uc_client/data/cache/creditsettings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'creditsettings\'] = '.var_export($outextcredits, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		return API_RETURN_SUCCEED;
	}

	function addfeed($get, $post) {
		global $_G;

		if(!API_ADDFEED) {
			return API_RETURN_FORBIDDEN;
		}
		return API_RETURN_SUCCEED;
	}
}