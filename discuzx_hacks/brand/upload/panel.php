<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: panel.php 4364 2010-09-08 01:02:33Z fanshengshuai $
 */

@define('IN_STORE', true);
require_once('./common.php');
require_once(B_ROOT.'./source/adminfunc/tpl.func.php');
require_once(B_ROOT.'./source/function/cache.func.php');

if(intval($_GET['shopid']) > 0) {
	$shopid = intval($_GET['shopid']);
	$myshopid = DB::result_first("SELECT itemid FROM ".tname('shopitems')." WHERE itemid='$shopid' AND uid='$_G[uid]'");
	if(!empty($myshopid) && $myshopid > 0) {
		updatetable('members', array('myshopid' => $myshopid), array('uid'=>$_G['uid']));
		$_G['myshopid'] = $myshopid;
	} else {
		showmessage('noperm_manageshop');
	}
}

//权限检查
if(pkperm('isadmin')) {
	showmessage('admin_no_perm_to_panel', 'index.php');
} elseif($_G['myshopstatus'] == 'verified') {
	getpanelinfo();

	$models = array('shop', 'photo');

	$menuindex = array(array('menu_home', 'index'));
	$menuinfomanage = array(array('menu_report', 'report'));
	foreach(array('brandlinks', 'good', 'notice', 'consume', 'album', 'groupbuy') as $k=>$v) {
		if($_SGLOBAL['panelinfo']['enable'.$v] > 0) {
			if($v == 'brandlinks') {
				array_push($menuindex, array('menu_list_add'.$v, 'brandlinks&op=add'));
				array_push($menuinfomanage, array('menu_'.$v,'brandlinks'));
			} else {
				array_push($menuindex, array('menu_list_add'.$v, 'add&m='.$v));
				array_push($menuinfomanage, array('menu_'.$v,'list&m='.$v));
				array_push($models, $v);
			}
		}
	}
	if(!empty($_GET['m']) && !in_array($_GET['m'], $models)) {
		showmessage('no_perm');
	}

} elseif($_G['myshopstatus'] == 'unverified') {
	$models = array('shop');
	$_GET['m'] = 'shop';
	getpanelinfo();
} else {
	showmessage('no_perm', 'index.php');
}

$BASESCRIPT = 'panel.php';

// 读入商家后台与店长后台的公共文件，包含变量初始化/常见id处理/载入语言包/grade关系等
require_once (B_ROOT.'./source/admininc/common.inc.php');

//二次登录确认(半个小时)
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
	case '1'://可以登录
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
	case '2'://登录成功
		break;
	default://尝试次数太多禁止登录
		showmessage('excessive_number_of_attempts_to_sign');
		break;
}

$shop = loadClass('shop',$_G['myshopid']);

if($shop->status == 'new') {
	// 新的，没有被审核的
	$actions = array('index', 'edit', 'map', 'list');
}elseif($shop->status == 'close') {
	// 关闭的
	$actions = array('index');
}elseif($shop->status == 'normal') {
	// 正常的
	$actions = array('index', 'batchmod', 'list', 'edit', 'add', 'map', 'theme', 'nav', 'report', 'brandlinks', 'modifypasswd');
}

//店长后台新手任务
if(!$_G['member']['taskstatus'] && (($mname=="shop" && $_GET['action'] =='edit') || $_GET['action']=='add') && $_GET['intask']) {
	if($mname=="shop") {
		$taskmessage = array($lang["task_step_1_title"],$lang["task_step_1_message"]);
		//$nexttask = "panel.php?action=add&m=notice&intask=1";
		$nexttask = "panel.php?action=index";

	} elseif($mname=="good") {
		$taskmessage = array($lang["task_step_3_title"],$lang["task_step_3_message"]);
		$nexttask = "panel.php?action=add&m=consume&intask=1";
	} elseif($mname=="photo") {
		$taskmessage = array($lang["task_step_5_title"],$lang["task_step_5_message"]);
		$nexttask = "panel.php?action=index";

	} elseif($mname=="notice") {
		$taskmessage = array($lang["task_step_2_title"],$lang["task_step_2_message"]);
		$nexttask = "panel.php?action=add&m=good&intask=1";
	} elseif($mname=="consume") {

		$taskmessage = array($lang["task_step_4_title"],$lang["task_step_4_message"]);
		$nexttask = "panel.php?action=add&m=photo&intask=1";
	}
}

//调入不同的action
if($_GET['action'] == 'ajax') {
	//店长使用的ajax数据调用
	require_once(B_ROOT.'./source/panel/ajax.inc.php');

	exit;
}elseif($_GET['action']=='ajax_editor') {
	//站长与店长公用的ajax编辑器调用
	if(empty($_SGLOBAL['panelinfo']['enablealbum'])) {
		$_GET['cont']='www';
	}

	require_once(B_ROOT.'./source/adminfunc/editor_ajax_img.func.php');
	geteditcont($_GET['cont'], 0);

	exit;
}elseif(empty($_GET['action']) || isset($_GET['frames'])) {
	$extra = cpurl('url');
	$extra = $extra && $_GET['action'] ? $extra : (!empty($runwizard) ? 'action=runwizard' : 'action=index');
	require_once B_ROOT.'./source/admininc/main.inc.php'; //Frame框架
} elseif(in_array($_GET['action'], $actions)) {
	if($_G['inajax'] != 1) {
		cpheader();
	}
	require_once B_ROOT.'./source/panel/'.$_GET['action'].'.inc.php'; //后台功能模块
	$title = 'cplog_'.$_GET['action'].($_GET['operation'] ? '_'.$_GET['operation'] : '');
} else {
	cpheader();
	cpmsg('noaccess');
}

cpfooter();

if(!empty($_GET['action']) && !isset($_GET['frames'])) {
	ob_out();
}

?>