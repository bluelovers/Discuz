<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: tools.php 2471 2011-04-20 01:22:54Z songlixin $
 */

define('CURSCRIPT', 'tools');
require './source/class/class_core.php';
require_once './source/function/function_cache.php';
require_once './source/plugin/tools/function/tools.func.php';

$tools = & discuz_core::instance();
//error_reporting(E_ALL);
$toolspw = '';   
/* 
   如果不需要在数据库中设置，请修改上面一行单引号中的值为想要的密码
   例如 $toolspw = 'QWESASDGy';  则在登陆的时候需要输入的密码为：QWESASDGy
*/
$toolspw = $toolspw ? md5($toolspw) : '';
$toolskey = '';
foreach($_G['config']['db'][1] as $key => $value) {
	$$key = $value;
}


//模板语言包处理 避免没有生产关于语言包缓存的时候英文
if(!file_exists('./data/plugindata/tools.lang.php')){
	require DISCUZ_ROOT.'./data/plugindata/tools.lang.'.CHARSET.'.php';
} else {
	require DISCUZ_ROOT.'./data/plugindata/tools.lang.php';
}
foreach($scriptlang['tools'] as $key => $value){
	$key = 'toolss_'.$key;
	$$key = $value;
}
foreach($templatelang['tools'] as $key => $value){
	$key = 'tools_'.$key;
	$$key = $value;
}
//模板语言包处理 end

//数据库连接判断
$dbtest = getcookie(dbtest);
if(empty($toolspw)) {
	$authtype = 'B';
	if($dbtest){
		$tools->_init_db();
		$toolskey = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='toolskey'");
		$toolspw = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='toolspw'");
	}
}
if($_G['gp_configsubmit'] && getcookie('toolsauth') ==  $toolspw && $toolspw!=''){
	require './config/config_global.php';
	$_config['db'][1]['dbhost'] = $_G['gp_dbhost'];
	$_config['db'][1]['dbname'] = $_G['gp_dbname'];
	$_config['db'][1]['dbpw'] = $_G['gp_dbpw'];
	$_config['db'][1]['dbuser'] = $_G['gp_dbusername'];
	$_config['db'][1]['tablepre'] = $_G['gp_tablepre'];
	save_config_file('./config/config_global.php',$_config,'./config/config_global.php');
	toolsmessage(toolslang('success'),"$_G[basescript].php");
}
if((empty($dbtest) || $dbtest==0) && (getcookie('toolsauth') ==  $toolspw)){
	
	if(!$siteping = @mysql_connect($dbhost,$dbuser,$dbpw)) {
		echo toolslang('configerror');
		echo "<br/>";
		echo toolslang('configerrorcontent')."<font color=red>".toolslang('dblinkerror')."</font>";
		dsetcookie('dbtest','0');
		if(is_writable('./config/config_global.php')){
			echo '   '.toolslang('modiconfig');
			include template('tools:configform');
		} else {
			echo toolslang('editconfig');	
		}
		exit;
	} elseif(!mysql_select_db($dbname,$siteping)) {
		echo toolslang('configerror');
		echo "<br/>";
		echo toolslang('configerrorcontent')."<font color=red>".toolslang('dbnameerror')."</font>";
		dsetcookie('dbtest','0');
		if(is_writable('./config/config_global.php')){
			echo '   '.toolslang('modiconfig');
			include template('tools:configform');
		} else {
			echo toolslang('editconfig');	
		}
		exit;
	} else {
		dsetcookie('dbtest','1');
		//toolsmessage('tools:successconfig',"$_G[basescript].php");
	}
}
//数据库连接判断 end


//密码判断
	
	if($toolspw == ''){
		toolsmessage(toolslang('emptypw'),"$_G[basescript].php");
	}
	
	if($toolskey){
		if(!file_exists(DISCUZ_ROOT."./$toolskey")){
			toolsmessage(toolslang('nokeyfile'),NULL);
		}
	}
	
	if($_G['gp_loginsubmit']){
		if($_G['gp_toolspassword'] == ''){
			toolsmessage(toolslang('emptypw'),"$_G[basescript].php");
		} elseif(md5($_G['gp_toolspassword']) == $toolspw){
			dsetcookie('toolsauth',$toolspw,'86400');
			toolsmessage(toolslang('loginsuccess'),"$_G[basescript].php");
		} else {
			toolsmessage(toolslang('error'),"$_G[basescript].php");	
		}
	} elseif(getcookie('toolsauth') !=  $toolspw) {
		$_G['gp_action'] = 'login';
	}
//密码判断 end

if(!$tools->initated && $dbtest){
	$tools->_init_db();

	DB::query('REPAIR TABLE '.DB::table('common_syscache'));
	DB::query('REPAIR TABLE '.DB::table('common_setting'));
	
	if($_G['gp_action'] != 'recoverdb'){
		$tools->_init_setting();
	}
	$tools->var['setting']['bbclosed'] = 0;
	$tools->_init_misc();
	if($_G['gp_action'] != 'recoverdb'){
		$tools->_init_session();
	}
}

//右侧菜单
$menu = array(
		'setadmin' => toolslang('setadmin'),
		'closesite' => toolslang('closesite'),
		'config' => toolslang('config'),
		'repairdb' => toolslang('repairdb'),
		'closesecode' => toolslang('closesecode'),
		'updatecache' => toolslang('updatecache'),
		'recoverdb' => toolslang('recoverdb'),
		'logout' => toolslang('logout'),
	);
//右侧菜单 end

$action = $_G['gp_action'];
$type = $_G['gp_type'];
if($action != NULL){
	$des = toolslang($action.'des');
	$title = toolslang($action);
} else {
	$title = toolslang('index');
}


//流程开始
if($action == NULL) {
	$action = 'welcome';
} elseif($action == 'login') {
} elseif($action == 'setadmin') {
	$founders = @explode(',',$_G['config']['admincp']['founder']);
	foreach($founders as $userid) {
		$foundername[] = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE `uid`='$userid'");	
	}
	
	if($_G['gp_submit']){
		if($_G['gp_where'] == NULL){
			toolsmessage(toolslang('setadminallow'),"$_G[basescript].php?action=$action");
		}

		if($_G['gp_loginfield'] == 'username'){
			$uid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE `username`='$_G[gp_where]'");
			$username = $_G[gp_where];
		} elseif($_G['gp_loginfield'] == 'uid') {
			$uid = 	$_G[gp_where];
			$username = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE `uid`='$_G[gp_where]'");
		}
		loaducenter();
		uc_user_deleteprotected($username);
		if($uid && $username) {
			DB::query("UPDATE ".DB::table('common_member')." SET `groupid`='1',`adminid`='1' WHERE `uid`='$uid'");
			if(!in_array($uid,$founders)){
				DB::query("REPLACE INTO ".DB::table('common_admincp_member')." (`uid`, `cpgroupid`, `customperm`) VALUES ('$uid', '0', '')");
			}
		} else {
			toolsmessage($toolss_nouser,"$_G[basescript].php?action=$action");
		}
		

		if($_G['gp_password'] != NULL){
			uc_user_edit($username,'',$_G['gp_password'],'',1);
		}
		if($_G['gp_issecques'] == 1){
			uc_user_edit($username,'','','',1,0,0);
		}
		toolsmessage($toolss_setadminsuccess,"$_G[basescript].php?action=$action",array('username'=>$username));
	}
	
	$foundernames = @implode(', ',$foundername);

	$adminmember = DB::query("SELECT cpm.uid,m.username FROM ".DB::table('common_admincp_member')." cpm,.".DB::table('common_member')." m WHERE cpm.cpgroupid = 0 AND cpm.uid = m.uid");
	while($data = DB::fetch($adminmember)) {
		$adminmembers[$data[uid]] = $data[username];
	}
	$adminmember = @implode(', ',$adminmembers);
} elseif($action == 'closesite') {
	$bbclosed = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE `skey`='bbclosed'");
	$closereason = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE `skey`='closedreason'");
	
	if($_G['gp_submit']){
		if($_G['gp_close'] == 1) {
			DB::query("UPDATE ".DB::table('common_setting')." SET `svalue`='0' WHERE `skey`='bbclosed'");
			DB::query("UPDATE ".DB::table('common_setting')." SET `svalue`='' WHERE `skey`='closedreason'");
		} else {
			DB::query("UPDATE ".DB::table('common_setting')." SET `svalue`='1' WHERE `skey`='bbclosed'");	
			DB::query("UPDATE ".DB::table('common_setting')." SET `svalue`='$_G[gp_closereason]' WHERE `skey`='closedreason'");
		}
		updatecache('setting');
		toolsmessage($toolss_success,"$_G[basescript].php?action=$action");
	}
} elseif($action == 'updatecache') {
	$type = $_G['gp_type'];
	if($type == 'data'){
		updatecache();
		include_once libfile('function/block');
		blockclass_cache();
		//note 清除群组缓存
		require_once libfile('function/group');
		$groupindex['randgroupdata'] = $randgroupdata = grouplist('lastupdate', array('ff.membernum', 'ff.icon'), 80);
		$groupindex['topgrouplist'] = $topgrouplist = grouplist('activity', array('f.commoncredits', 'ff.membernum', 'ff.icon'), 10);
		$groupindex['updateline'] = TIMESTAMP;
		$groupdata = DB::fetch_first("SELECT SUM(todayposts) AS todayposts, COUNT(fid) AS groupnum FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub'");
		$groupindex['todayposts'] = $groupdata['todayposts'];
		$groupindex['groupnum'] = $groupdata['groupnum'];
		save_syscache('groupindex', $groupindex);
		DB::query("TRUNCATE ".DB::table('forum_groupfield'));
		toolsmessage($toolss_success,"$_G[basescript].php?action=$action");
	} elseif($type == 'tpl') {
		$tpl = dir(DISCUZ_ROOT.'./data/template');
		while($entry = $tpl->read()) {
			if(preg_match("/\.tpl\.php$/", $entry)) {
				@unlink(DISCUZ_ROOT.'./data/template/'.$entry);
			}
		}
		$tpl->close();
		toolsmessage($toolss_success,"$_G[basescript].php?action=$action");
	}
} elseif($action == 'config') {
	require_once DISCUZ_ROOT.'./config/config_ucenter.php';
	$uctabpre = @explode('.',UC_DBTABLEPRE);
	$uctabpre = $uctabpre[1];
	$ucdbstatu = $ucdbext = $ucapi = 0;
	if($_G['gp_submit']){
		$ucconfig = array(UC_KEY,UC_APPID,$_G['gp_dbhost'],$_G['gp_dbname'],$_G['gp_dbusername'],$_G['gp_dbpw'],UC_DBCHARSET,$_G['gp_tablepre'],UC_CHARSET,$_G['gp_ucapi'],$_G['gp_ucip']);
		$ucconfig = @implode('|',$ucconfig);
		save_uc_config($ucconfig,DISCUZ_ROOT.'./config/config_ucenter.php');
		toolsmessage($toolss_success,"$_G[basescript].php?action=$action");
	}
	if($ucping = @mysql_connect(UC_DBHOST,UC_DBUSER,UC_DBPW)) {
		$ucdbstatu = 1;
		if(mysql_select_db(UC_DBNAME,$ucping)){
			$ucdbext = 1;	
		}
	}
} elseif($action == 'repairdb') {
	if($type == 'repair'){
		$repairresult = DB::fetch_first("REPAIR TABLE `$_G[gp_table]`");
		toolsmessage(toolslang('repair').$repairresult['Msg_text'],"$_G[basescript].php?action=$action");
	}
	
	$tablelist = DB::query("SHOW TABLE STATUS");
	while($list = DB::fetch($tablelist)){
		if($type == 'allrepair'){
			$repairresult = DB::fetch_first("REPAIR TABLE `$list[Name]`");
		} elseif($type == 'allcheck'){
			if($list['Engine'] != 'MEMORY' && $list['Engine'] != 'HEAP'){
				$checkresult = DB::fetch_first("CHECK TABLE $list[Name]");	
				$tablelists[$list['Name']]['statu'] = $checkresult['Msg_text'];
			} else {
				$tablelists[$list['Name']]['statu'] = toolslang('notsupportcheck');	
			}
			$tablelists[$list['Name']]['size'] = round(($list['Data_length'] + $list['Index_length'])/1024,2);
		} else {
			$tablelists[$list['Name']]['size'] = round(($list['Data_length'] + $list['Index_length'])/1024,2);
		}
	}
	if($type == 'check'){
		$checkresult = DB::fetch_first("CHECK TABLE `$_G[gp_table]`");
		$tablelists[$_G[gp_table]]['statu'] = $checkresult['Msg_text'];
	}
	if($type == 'allrepair'){
		toolsmessage(toolslang('success').toolslang('forward'),"$_G[basescript].php?action=$action");
	}
} elseif($action == 'logout') {
	dsetcookie('toolsauth','');
	toolsmessage(toolslang('success'),"$_G[basescript].php");
} elseif($action == 'convert') {
	toolsmessage(toolslang('nohave'),"$_G[basescript].php");	
} elseif($action == 'closesecode') {
	if(submitcheck('submit')){
		DB::query("UPDATE ".DB::table('common_setting')." SET `svalue`='0' WHERE `skey`='seccodestatus'");
		updatecache('setting');
		toolsmessage(toolslang('success'),"$_G[basescript].php?action=$action");
	}
	$secode	= DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='seccodestatus'");
} elseif($action == 'recoverdb') {
	$backfiledir = DISCUZ_ROOT.'data/';
	$detailarray = array();
	require './config/config_global.php';
	$a = & DB::object();
	if(!$a->select_db($_config['db'][1]['dbname'])){
		$dbname = $_config['db'][1]['dbname'];
		DB::query("CREATE DATABASE $dbname;");
	}
	if(!$_G['gp_importbak'] && !$_G['gp_nextfile']){
		$exportlog = array();
		$dir = dir($backfiledir);
		while($entry = $dir->read()) {
			$entry = $backfiledir."/$entry";
			$num = 0;
			if(is_dir($entry) && preg_match("/backup\_/i", $entry)) {
				
				$bakdir = dir($entry);
				while($bakentry = $bakdir->read()) {
					$bakentry = "$entry/$bakentry";
					if(is_file($bakentry) && preg_match("/(.*)\-(\d)\.sql/i", $bakentry,$match)) {
						if($_G['gp_detail']){
							$detailarray[] = $match['1'];
						}
						$num++;	
					}
					if(is_file($bakentry) && preg_match("/\-1\.sql/i", $bakentry)) {
						@$fp = fopen($bakentry, 'rb');
						@$bakidentify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
						@fclose ($fp);
						
						if(preg_match("/\-1\.sql/i", $bakentry) || $bakidentify[3] == 'shell') {
							$identify['bakentry'] = $bakentry;
						}
					}
				}
				$detailarray = array_reverse(array_unique($detailarray));
				if($num != 0){
					$exportlog[$entry] = array(	
								'dateline' => date('Y-m-d H:i:s',$bakidentify[0]),
								'version' => $bakidentify[1],
								'type' => $bakidentify[2],
								'method' => $bakidentify[3],
								'volume' => $num,
								'bakentry' => $identify['bakentry'],
								'filename' => str_replace($backfiledir.'/','',$entry));
				}
			}
		}	
	} else {
		$bakfile = $_G['gp_nextfile'] ? $_G['gp_nextfile'] : $_G['gp_importbak'];
		if(!file_exists($bakfile)){
			if($_G['gp_nextfile']){
				updatecache();
				$tpl = dir(DISCUZ_ROOT.'./data/template');
				while($entry = $tpl->read()) {
					if(preg_match("/\.tpl\.php$/", $entry)) {
						@unlink(DISCUZ_ROOT.'./data/template/'.$entry);
					}
				}
				$tpl->close();
				toolsmessage(toolslang('recoversuccess'),"$_G[basescript].php?action=$action");
			}
			toolsmessage(toolslang('filenoexists'),NULL);
		}
		if(!is_readable($bakfile)){
			toolsmessage(toolslang('noread'),NULL);	
		} else {
			@$fp = fopen($bakfile, "r");
			@flock($fp, 3);
			$sqldump = @fread($fp, filesize($bakfile));
			@fclose($fp);
		}
		@$bakidentify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", substr($sqldump, 0, 256))));
		include_once(DISCUZ_ROOT.'/source/discuz_version.php');
		if($bakidentify[1] != DISCUZ_VERSION){
			toolsmessage(toolslang('wrongver'),NULL);		
		}
		$vol = $bakidentify[4];
		$nextfile = addslashes(str_replace("-$vol.sql","-".($vol+1).'.sql',$bakfile));
		$result = tools_runquery($sqldump);
		if($result) {
			toolsmessage(toolslang('recoveing').$vol,"$_G[basescript].php?action=$action&nextfile=$nextfile",'',array('refreshtime' => '1'));	
		}
	}
}

//流程结束
include template('tools:tools');



?>