<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 4337 2010-09-06 04:48:05Z fanshengshuai $
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(1000);
@set_magic_quotes_runtime(0);

define('IN_COMSENZ', true);
define('ROOT_PATH', dirname(__FILE__).'/../');

require ROOT_PATH.'./install/lang.inc.php';
require ROOT_PATH.'./install/var.inc.php';
require ROOT_PATH.'./install/db.class.php';
require ROOT_PATH.'./install/func.inc.php';

file_exists(ROOT_PATH.'./install/extvar.inc.php') && require ROOT_PATH.'./install/extvar.inc.php';

$view_off = getgpc('view_off');

define('VIEW_OFF', $view_off ? true : false);

$allow_method = array('show_license', 'env_check', 'app_reg', 'db_init', 'ext_info', 'install_check', 'tablepre_check');

$step = intval(getgpc('step', 'R')) ? intval(getgpc('step', 'R')) : 0;
$method = getgpc('method');

if(empty($method) || !in_array($method, $allow_method)) {
	$method = isset($allow_method[$step]) ? $allow_method[$step] : '';
}

if(empty($method)) {
	show_msg('method_undefined', $method, 0);
}

if($install_config) {
	show_msg('install_config', '', 0);
} elseif(!ini_get('short_open_tag')) {
	show_msg('short_open_tag_invalid', '', 0);
} elseif(file_exists($lockfile) && $method != 'ext_info') {
	show_msg('install_locked', '', 0);
} elseif(!class_exists('dbstuff')) {
	show_msg('database_nonexistence', '', 0);
}

$uchidden = getgpc('uchidden');

if($method == 'show_license') {

	transfer_ucinfo($_POST);
	show_license();

} elseif($method == 'env_check') {

	VIEW_OFF && function_check($func_items);

	env_check($env_items);

	dirfile_check($dirfile_items);

	show_env_result($env_items, $dirfile_items, $func_items);

} elseif($method == 'app_reg') {
	@include CONFIG;
	if(!defined('UC_API')) {
		define('UC_API', '');
	}
	$submit = true;
	$error_msg = array();
	if(isset($form_app_reg_items) && is_array($form_app_reg_items)) {
		foreach($form_app_reg_items as $key => $items) {
			$$key = getgpc($key, 'p');
			if(!isset($$key) || !is_array($$key)) {
				$submit = false;
				break;
			}
			foreach($items as $k => $v) {
				$tmp = $$key;
				$$k = $tmp[$k];
				if(empty($$k) || !preg_match($v['reg'], $$k)) {
					if(empty($$k) && !$v['required']) {
						continue;
					}
					$submit = false;
					VIEW_OFF or $error_msg[$key][$k] = 1;
				}
			}
		}
	} else {
		$submit = false;
	}

	$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$bbserver = 'http://'.preg_replace("/\:\d+/", '', $_SERVER['HTTP_HOST']).($_SERVER['SERVER_PORT'] && $_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '');
	$default_ucapi = $bbserver.'/ucenter';
	$default_appurl = $bbserver.substr($PHP_SELF, 0, strpos($PHP_SELF, 'install/') - 1);
 	$ucapi = defined('UC_API') && UC_API ? UC_API : $default_ucapi;

	if($submit) {

		$app_type = 'BRAND';

		$app_name = $sitename ? $sitename : SOFT_NAME;
		$app_url = $siteurl ? $siteurl : $default_appurl;

		$ucapi = $ucurl ? $ucurl : (defined('UC_API') && UC_API ? UC_API : $default_ucapi);
		$ucip = isset($ucip) ? $ucip : '';
		$ucfounderpw = $ucpw;
		$app_tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
		'apptagtemplates[fields][subject]='.urlencode($lang['tagtemplates_subject']).'&'.
		'apptagtemplates[fields][uid]='.urlencode($lang['tagtemplates_uid']).'&'.
		'apptagtemplates[fields][username]='.urlencode($lang['tagtemplates_username']).'&'.
		'apptagtemplates[fields][dateline]='.urlencode($lang['tagtemplates_dateline']).'&'.
		'apptagtemplates[fields][url]='.urlencode($lang['tagtemplates_url']);

		$ucapi = preg_replace("/\/$/", '', trim($ucapi));
		if(empty($ucapi) || !preg_match("/^(http:\/\/)/i", $ucapi)) {
			show_msg('uc_url_invalid', $ucapi, 0);
		} else {
			if(!$ucip) {
				$temp = @parse_url($ucapi);
				$ucip = gethostbyname($temp['host']);
				if(ip2long($ucip) == -1 || ip2long($ucip) === false) {
					show_msg('uc_dns_error', $ucapi, 0);
				}
			}
		}
		include_once ROOT_PATH.'./uc_client/client.php';

		$ucinfo = dfopen($ucapi.'/index.php?m=app&a=ucinfo&release='.UC_CLIENT_RELEASE, 500, '', '', 1, $ucip);
		list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = explode('|', $ucinfo);
		if($status != 'UC_STATUS_OK') {
			show_msg('uc_url_unreachable', $ucapi, 0);
		} else {
			$dbcharset = strtolower($dbcharset ? str_replace('-', '', $dbcharset) : $dbcharset);
			$ucdbcharset = strtolower($ucdbcharset ? str_replace('-', '', $ucdbcharset) : $ucdbcharset);
			if(UC_CLIENT_VERSION > $ucversion) {
				show_msg('uc_version_incorrect', $ucversion, 0);
			} elseif($dbcharset && $ucdbcharset != $dbcharset) {
				show_msg('uc_dbcharset_incorrect', '', 0);
			}

			$postdata = "m=app&a=add&ucfounder=&ucfounderpw=".urlencode($ucpw)."&apptype=".urlencode($app_type)."&appname=".urlencode($app_name)."&appurl=".urlencode($app_url)."&appip=&appcharset=".CHARSET.'&appdbcharset='.DBCHARSET.'&'.$app_tagtemplates.'&release='.UC_CLIENT_RELEASE;
			$ucconfig = dfopen($ucapi.'/index.php', 500, $postdata, '', 1, $ucip);
			if(empty($ucconfig)) {
				show_msg('uc_api_add_app_error', $ucapi, 0);
			} elseif($ucconfig == '-1') {
				show_msg('uc_admin_invalid', '', 0);
			} else {
				list($appauthkey, $appid) = explode('|', $ucconfig);
				if(empty($appauthkey) || empty($appid)) {
					show_msg('uc_data_invalid', '', 0);
				} elseif($succeed = save_uc_config($ucconfig."|$ucapi|$ucip", CONFIG)) {
					$siteurl = addslashes($_POST['siteinfo']['siteurl']);
					config_edit_siteurl();
					if(VIEW_OFF) {
						show_msg('app_reg_success');
					} else {
						$step = $step + 1;
						header("Location: index.php?step=$step");
						exit;
					}
				} else {
					show_msg('config_unwriteable', '', 0);
				}
			}
		}

	}
	if(VIEW_OFF) {

		show_msg('missing_parameter', '', 0);

	} else {

		show_form($form_app_reg_items, $error_msg);

	}

} elseif($method == 'db_init') {

	@include CONFIG;
	$submit = true;
	$error_msg = array();
	if(isset($form_db_init_items) && is_array($form_db_init_items)) {
		foreach($form_db_init_items as $key => $items) {
			$$key = getgpc($key, 'p');
			if(!isset($$key) || !is_array($$key)) {
				$submit = false;
				break;
			}
			foreach($items as $k => $v) {
				$tmp = $$key;
				$$k = $tmp[$k];
				if(empty($$k) || !preg_match($v['reg'], $$k)) {
					if(empty($$k) && !$v['required']) {
						continue;
					}
					$submit = false;
					VIEW_OFF or $error_msg[$key][$k] = 1;
				}
			}
		}
	} else {
		$submit = false;
	}

	if($submit && !VIEW_OFF && $_SERVER['REQUEST_METHOD'] == 'POST') {
		if($password != $password2) {
			$error_msg['admininfo']['password2'] = 1;
			$submit = false;
		}
		$forceinstall = isset($_POST['dbinfo']['forceinstall']) ? $_POST['dbinfo']['forceinstall'] : '';
		$dbname_not_exists = true;
		if(!empty($dbhost) && empty($forceinstall)) {
			$dbname_not_exists = check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre);
			if(!$dbname_not_exists) {
				$form_db_init_items['dbinfo']['forceinstall'] = array('type' => 'checkbox', 'required' => 0, 'reg' => '/^.*+/');
				$error_msg['dbinfo']['forceinstall'] = 1;
				$submit = false;
				$dbname_not_exists = false;
			}
		}
	}

	if($submit) {

		$step = $step + 1;
		if(empty($dbname)) {
			show_msg('dbname_invalid', $dbname, 0);
		} else {
			if(!$link = @mysql_connect($dbhost, $dbuser, $dbpw)) {
				$errno = mysql_errno($link);
				$error = mysql_error($link);
				if($errno == 1045) {
					show_msg('database_errno_1045', $error, 0);
				} elseif($errno == 2003) {
					show_msg('database_errno_2003', $error, 0);
				} else {
					show_msg('database_connect_error', $error, 0);
				}
			}
			if(mysql_get_server_info() > '4.1') {
				mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET ".DBCHARSET, $link);
			} else {
				mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname`", $link);
			}

			if(mysql_errno()) {
				show_msg('database_errno_1044', mysql_error(), 0);
			}
			mysql_close($link);
		}

		if(strpos($tablepre, '.') !== false) {
			show_msg('tablepre_invalid', $tablepre, 0);
		}

		if($username && $email && $password) {
			if(strlen($username) > 15 || preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^Guest/is", $username)) {
				show_msg('admin_username_invalid', $username, 0);
			} elseif(!strstr($email, '@') || $email != stripslashes($email) || $email != htmlspecialchars($email)) {
				show_msg('admin_email_invalid', $email, 0);
			} else {
					$adminuser = check_adminuser($username, $password, $email);
					if($adminuser['uid'] < 1) {
						show_msg($adminuser['error'], '', 0);
					}
			}
		} else {
			show_msg('admininfo_invalid', '', 0);
		}
		config_edit(); //寫配置文件

		$uid = $adminuser['uid'];

		$db = new dbstuff;
		$db->connect($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);

		if(!VIEW_OFF) {
			show_header();
			show_install();
		}

		$sql = file_get_contents($sqlfile);
		$sql = str_replace("\r\n", "\n", $sql);
		runquery($sql);
		if(is_writeable(ROOT_PATH.'./config.php')) {
			$configfile = @file_get_contents(ROOT_PATH.'./config.php');
			$configfile = trim($configfile);
			$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
			$configfile = preg_replace("/[$]_SC\[[\"']founder[\"']\]\s*\=\s*[\"'].*?[\"'];/is", "\$_SC['founder'] = '$uid';", $configfile);
			@file_put_contents(ROOT_PATH.'./config.php', $configfile);
		}
		@dir_clear(ROOT_PATH.'./data/cache/tpl');
		@dir_clear(ROOT_PATH.'./data/cache/block');
		@dir_clear(ROOT_PATH.'./data/cache/model');

		@dir_clear(ROOT_PATH.'./uc_client/data');
		@dir_clear(ROOT_PATH.'./uc_client/data/cache');

		touch($lockfile);
		VIEW_OFF && show_msg('initdbresult_succ');

		//設置SITEKEY等
		$sitekey = substr(_generate_key(), 4, 16);
		runquery("REPLACE INTO ".ORIG_TABLEPRE."settings (`variable` ,`value`) VALUES ('sitekey', '$sitekey')");

		if(!VIEW_OFF) {
			echo '<script type="text/javascript">document.getElementById("laststep").disabled=false;document.getElementById("laststep").value = \''.lang('install_founder_contact').'\';</script><script type="text/javascript">setTimeout(function(){window.location=\'index.php?method=ext_info\'}, 2000);</script><iframe src="../" style="display:none"></iframe>'."\r\n";
			show_footer();
		}

	}
	if(VIEW_OFF) {

		show_msg('missing_parameter', '', 0);

	} else {

		show_form($form_db_init_items, $error_msg);

	}

} elseif($method == 'ext_info') {
	@include CONFIG;
	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);
	$skip = getgpc('skip');
	if(empty($skip)) {
		//upg_comsenz_stats();
	}
	@touch($lockfile);
	if(VIEW_OFF) {
		show_msg('ext_info_succ');
	} else {
		show_header();
		echo '</div><div class="main" style="margin-top: -123px;"><ul style="line-height: 200%; margin-left: 30px;">';
		echo '<li><a href="../">'.lang('install_succeed').'</a><br>';
		echo '<script>setTimeout(function(){window.location=\'../\'}, 2000);</script>'.lang('auto_redirect').'</li>';
		echo '</ul></div>';
		show_footer();
	}

} elseif($method == 'install_check') {

	if(file_exists($lockfile)) {
		show_msg('installstate_succ');
	} else {
		show_msg('lock_file_not_touch', $lockfile, 0);
	}

} elseif($method == 'tablepre_check') {

	$dbinfo = getgpc('dbinfo');
	extract($dbinfo);
	if(check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre)) {
		show_msg('tablepre_not_exists', 0);
	} else {
		show_msg('tablepre_exists', $tablepre, 0);
	}
}