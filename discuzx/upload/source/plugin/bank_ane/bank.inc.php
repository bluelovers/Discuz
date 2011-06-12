<?php
/*
	Info: Bank Hack for discuz! 7.2.0
	Version: 7.2
	Web: http://www.webcm.cn
	Designed by webcm,Last Modified: 2009-12-28 
*/

define('IN_BANKHACK', TRUE);
define('UC_API', TRUE);


require_once DISCUZ_ROOT.'./source/plugin/bank_ane/bank.func.php';
require_once DISCUZ_ROOT.'./data/plugindata/bank_ane.lang.php';
$bankmsglang=$scriptlang['bank_ane'];
$banktmplang=$templatelang['bank_ane'];
$hackSettings = hack_getConfig();
if(!isset($hackSettings['vars'])&&0) {
	showmessage($bankmsglang['config_error']);
}
$hackVersion = '1.1';
$hackName = $navigation = $hackSettings['name'];
$hackCopyright = $hackSettings['copyright'] ? $hackSettings['copyright'] : 'Designed by webcm.cn';
$hackVars = $hackSettings;
$extcredits = $_G['setting']['extcredits'];
getuserprofile('extcredits1');
$user_extcredits = $_G['member'];
unset($hackSettings);
if(isset($copyright)) {
	showmessage("<p>$hackName $hackVersion</p><p>$hackCopyright</p>");
}

if(!$_G['uid']) {
	showmessage('not_loggedin', '', '', array('login' => 1));
}
if($_G['adminid']!=1 && $hackVars['close']) {
	showmessage($hackVars['closemessage']);
}
$moneycredits = trim($hackVars['moneycredits']);
$moneycreditsnum = intval(substr($moneycredits, -1));
if(!isset($extcredits[$moneycreditsnum]['title'])) {
	showmessage($bankmsglang['config_error']);
}
$moneytype = $extcredits[$moneycreditsnum]['title'];
$moneyname = $extcredits[$moneycreditsnum]['unit'];
$mycash = $_G['member'][$moneycredits];
$depositcredits = trim($hackVars['depositcredits']);
$depositcreditsnum = 0;
$myfund = 0;
if($depositcredits!='none') {
	if($depositcredits==$moneycredits) {
		showmessage($bankmsglang['config_error']);
	}
	$depositcreditsnum = intval(substr($depositcredits, -1));
	if(!isset($extcredits[$depositcreditsnum]['title'])) {
		$depositcredits = 'none';
	} else {
		$myfund = $GLOBALS[$depositcredits];
	}
}

$issupbankadmin = $isbankadmin = 0;
if($hackVars['bankadmin']!='') $issupbankadmin=hack_checkStr($hackVars['bankadmin'], $_G['member']['username']);
if($hackVars['adminisbankadm']==1 && $adminid==1) $issupbankadmin=1;

$pernum = 15;
$bankid = $_G['gp_bankid'] ? abs(intval(trim($_G['gp_bankid']))) : 0;
$hackVars['onlybankid'] = intval($hackVars['onlybankid']);
if($hackVars['onlybankid']>0 && !$issupbankadmin) $bankid = $hackVars['onlybankid'];
$banknum = $banknum ? abs(intval(trim($_G['gp_banknum']))) : 0;
$page = isset($_G['gp_page']) ? max(1, intval($_G['gp_page'])) : 1;
$start_limit = ($page-1)*$pernum;

if($bankid>0) {
	include_once libfile('function/discuzcode');
	$query = DB::query("SELECT * FROM ".DB::table('plugin_banklist')." WHERE id='$bankid'");
	if($bankinfo = DB::fetch($query)) {
		$bankinfo['opentimeshow'] = gmdate("{$_G[setting][dateformat]}", $bankinfo['opentime']+$_G['setting']['timeoffset']*3600);
		$bankinfo['allmoneynum'] = $bankinfo['bankroll']+$bankinfo['deposit'];
		$bankinfo['noticeshow'] = discuzcode($bankinfo['notice'], 1, 0);
		$bankinfo['currentrate'] = unserialize($bankinfo['currentrate']);
		$bankinfo['currentrateshow'] = hack_showCurInfo();
		$bankinfo['fixedrateshow'] = $bankinfo['fixedrate']*1000;
		$bankinfo['lendingrateshow'] = $bankinfo['lendingrate']*1000;
		$bankinfo['changetaxshow'] = $bankinfo['changetax']*1000;
		if($bankinfo['bankadmin']!='') $isbankadmin=hack_checkStr($bankinfo['bankadmin'], $_G['username']);
		if($_G['username']==$bankinfo['creator'] || $issupbankadmin==1) $isbankadmin=2;
		if($bankinfo['bankroll']<0 && $isbankadmin<1) {
			showmessage($bankmsglang['bankroll_error']);
		}
		if($bankinfo['bankstatus']==1 && $isbankadmin<1) {
			showmessage(dhtmlspecialchars($bankinfo['notice']));
		}
	} else {
		$bankid = 0;
	}
}

if($_G['gp_mode']=='setup') {
	if($issupbankadmin!=1) {
		showmessage('undefined_action');
	}
	$loginpass = trim($_G['gp_loginpass']);
	include DISCUZ_ROOT.'./source/plugin/bank_ane/setup.inc.php';
} elseif($_G['gp_mode']=='admin') {
	if($bankid==0 || $isbankadmin<1) {
		showmessage('undefined_action');
	}
	$loginpass = trim($_G['gp_loginpass']);
	include DISCUZ_ROOT.'./source/plugin/bank_ane/admin.inc.php';
} elseif($_G['gp_mode']=='other') {
	include DISCUZ_ROOT.'./source/plugin/bank_ane/other.inc.php';
} else {
	$bankpass = trim($_G['gp_bankpass']);
	if($bankpass!='') $bankpass=md5($bankpass);
	include DISCUZ_ROOT.'./source/plugin/bank_ane/basic.inc.php';
}
include template('bank_ane:bank');


?>
