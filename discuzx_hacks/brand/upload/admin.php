<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admin.php 4397 2010-09-10 10:07:01Z fanshengshuai $
 */

@define('IN_ADMIN', true);
// 自定義後台頁面地址
@define('ADMINSCRIPT', 'admin.php');
require_once('./common.php');
require_once(B_ROOT.'./source/adminfunc/tpl.func.php');
require_once(B_ROOT.'./source/function/cache.func.php');

if(!pkperm('isadmin')) {
	showmessage('no_permission', 'index.php');
}

//刪除安裝程序
if(@file_exists(DISCUZ_ROOT.'./install/index.php') && !DISCUZ_DEBUG) {
	@unlink(DISCUZ_ROOT.'./install/index.php');
	if(@file_exists(DISCUZ_ROOT.'./install/index.php')) {
		dexit('Please delete install/index.php via FTP!');
	}
}

//允許的模型
$models = array('shop', 'good', 'notice', 'consume', 'album', 'photo', 'brandlinks', 'groupbuy');

$BASESCRIPT = 'admin.php';
//讀入商家後台與店長後台的公共文件，包含變量初始化/常見id處理/載入語言包/grade關係等
require_once (B_ROOT.'./source/admininc/common.inc.php');

//調入不同的action
if($_GET['action']=='ajax') {
	$_GET['inajax'] = 1;
	@header('Content-Type: text/html; charset='.$_G['charset']);
	require_once(B_ROOT.'./source/admin/ajax.inc.php'); //站長使用的ajax數據調用
	exit;
}
if($_GET['action']=='ajax_editor') {
	require_once(B_ROOT.'./source/adminfunc/editor_ajax_img.func.php');
	geteditcont($_GET['cont'], 0);
	//站長與店長公用的ajax編輯器調用
	exit;
}

//二次登錄確認(半個小時)
$cpaccess = 0;
$query = DB::query("SELECT errorcount FROM ".tname('adminsession')." WHERE uid='$_G[uid]' AND dateline+1800>='$_G[timestamp]'");
if($session = DB::fetch($query)) {
	if($session['errorcount'] == -1) {
		DB::query("UPDATE ".tname('adminsession')." SET dateline='$_G[timestamp]' WHERE uid='$_G[uid]'");
		$cpaccess = 2;
	} elseif($session['errorcount'] <= 3) {
		$cpaccess = 1;
	}
} else {
	DB::query("DELETE FROM ".tname('adminsession')." WHERE uid='$_G[uid]' OR dateline+1800<'$timestamp'");
	DB::query("INSERT INTO ".tname('adminsession')." (uid, ip, dateline, errorcount)
		VALUES ('$_G[uid]', '".$_G['clientip']."', '$_G[timestamp]', '0')");
	$cpaccess = 1;
}

switch ($cpaccess) {
	case '1'://可以登錄
		if(submitcheck('dologin', 1)) {
			if(!$passport = getpassport($_G['username'], $_POST['admin_password'])) {
				DB::query("UPDATE ".tname('adminsession')." SET errorcount=errorcount+1 WHERE uid='$_G[uid]'");
				showmessage('enter_the_password_is_incorrect', $BASESCRIPT);
			} else {
				DB::query("UPDATE ".tname('adminsession')." SET errorcount='-1' WHERE uid='$_G[uid]'");
				$refer = empty($_G['cookie']['_refer'])?$_SGLOBAL['refer']:rawurldecode($_G['cookie']['_refer']);
				if(empty($refer) || preg_match("/(login)/i", $refer)) {
					$refer = $BASESCRIPT;
				}

				showmessage('login_success', $refer, 0);
			}
		} else {
			if($_SERVER['REQUEST_METHOD'] == 'GET') {
				ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			} else {
				ssetcookie('_refer', rawurlencode($BASESCRIPT));
			}
			
			$_G['login_type'] = 'manage';
			include_once template('templates/site/default/login.html.php', 1);
			exit();
		}
		break;
	case '2'://登錄成功
		break;
	default://嘗試次數太多禁止登錄
		showmessage('excessive_number_of_attempts_to_sign');
		break;
}

if(!ckfounder($_G['uid'])) {
	check_cpaccess();
	$_SGLOBAL['adminsession']['perms'] = load_admin_perms();
}
if(empty($_GET['action']) || isset($_GET['frames'])) {
	$extra = cpurl('url');
	$extra = $extra && $_GET['action'] ? $extra : (!empty($runwizard) ? 'action=runwizard' : 'action=index');
	require_once B_ROOT.'./source/admininc/main.inc.php'; //Frame框架
} elseif(in_array($_GET['action'], array('index', 'batchmod', 'list', 'edit', 'add', 'global', 'field', 'ads', 'category', 'tool', 'map', 'theme', 'censor', 'comment', 'nav', 'commentmodel', 'remark', 'cron', 'attribute','discuz', 'attach', 'import', 'group', 'report', 'brandlinks', 'managelog','attr','block', 'modifypasswd', 'db', 'logs', 'perm'))) {
	if($_G['inajax'] != 1) {
		cpheader();
	}
	if(!ckfounder($_G['uid'])) {
		if(!permallow($_GET['action'],$_SGLOBAL['adminsession']['perms'])) {
			cpmsg('noaccess');
		}
	}
	require_once B_ROOT.'./source/admin/'.$_GET['action'].'.inc.php'; //後台功能模塊
	$title = 'cplog_'.$_GET['action'].(!empty($_GET['operation']) ? '_'.$_GET['operation'] : '');
} else {
	if($_G['inajax'] != 1) {
		cpheader();
	}
	cpmsg('noaccess');
}

cpfooter();

if(!empty($_GET['action']) && !isset($_GET['frames'])) {
	ob_out();
}
?>