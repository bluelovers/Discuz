<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update.php 27663 2012-02-09 04:59:02Z monkey $
 */

include_once('../source/class/class_core.php');
include_once('../source/function/function_core.php');

@set_time_limit(0);

$cachelist = array();
$discuz = & discuz_core::instance();

$discuz->cachelist = $cachelist;
$discuz->init_cron = false;
$discuz->init_setting = false;
$discuz->init_user = false;
$discuz->init_session = false;
$discuz->init_misc = false;

$discuz->init();
$config = array(
	'dbcharset' => $_G['config']['db']['1']['dbcharset'],
	'charset' => $_G['config']['output']['charset'],
	'tablepre' => $_G['config']['db']['1']['tablepre']
);
$theurl = 'update.php';

$lockfile = DISCUZ_ROOT.'./data/update.lock';
if(file_exists($lockfile)) {
	show_msg('请您先登录服务器ftp，手工删除 ./data/update.lock 文件，再次运行本文件进行升级。');
}

$devmode = file_exists(DISCUZ_ROOT.'./install/data/install_dev.sql');
$sqlfile = DISCUZ_ROOT.($devmode ? './install/data/install_dev.sql' : './install/data/install.sql');

if(!file_exists($sqlfile)) {
	show_msg('SQL文件 '.$sqlfile.' 不存在');
}

if($_POST['delsubmit']) {
	if(!empty($_POST['deltables'])) {
		foreach ($_POST['deltables'] as $tname => $value) {
			DB::query("DROP TABLE `".DB::table($tname)."`");
		}
	}
	if(!empty($_POST['delcols'])) {
		foreach ($_POST['delcols'] as $tname => $cols) {
			foreach ($cols as $col => $indexs) {
				if($col == 'PRIMARY') {
					DB::query("ALTER TABLE ".DB::table($tname)." DROP PRIMARY KEY", 'SILENT');
				} elseif($col == 'KEY' || $col == 'UNIQUE') {
					foreach ($indexs as $index => $value) {
						DB::query("ALTER TABLE ".DB::table($tname)." DROP INDEX `$index`", 'SILENT');
					}
				} else {
					DB::query("ALTER TABLE ".DB::table($tname)." DROP `$col`");
				}
			}
		}
	}

	show_msg('删除表和字段操作完成了', $theurl.'?step=style');
}

if(empty($_GET['step'])) $_GET['step'] = 'start';

if($_GET['step'] == 'start') {
	include_once('../config/config_ucenter.php');
	include_once('../uc_client/client.php');
	$version = uc_check_version();
	$version = $version['db'];
	if(!DB::result_first('SELECT svalue FROM '.DB::table('common_setting')." WHERE skey='bbclosed' LIMIT 1")) {
		show_msg('请先关闭站点再执行此升级操作');
	}
	if(strcmp($version, '1.5.2') <= 0) {
		show_msg('请先升级 UCenter 到 1.6.0 以上版本。<br>如果使用为Discuz! X自带UCenter，请先下载 UCenter 1.6.0, 在 utilities 目录下找到对应的升级程序，复制或上传到 Discuz! X 的 uc_server 目录下，运行该程序进行升级');
	} else {
		show_msg('说明：<br>本升级程序会参照最新的SQL文件，对数据库进行同步升级。<br>
			请确保当前目录下 ./data/install.sql 文件为最新版本。<br><br>
			升级完成后会关闭所有插件以确保正常运行，请站长逐个开启每一个插件检测是否兼容新版本。<br><br>
			<a href="'.$theurl.'?step=prepare">准备完毕，升级开始</a>');
	}

} elseif ($_GET['step'] == 'prepare') {
	if(!DB::result_first('SELECT skey FROM '.DB::table('common_setting')." WHERE skey='group_recommend' LIMIT 1")) {
		DB::query("TRUNCATE ".DB::table('forum_groupinvite'));
	}
	if(DB::fetch_first("SHOW COLUMNS FROM ".DB::table('forum_activityapply')." LIKE 'contact'")) {
		$query = DB::query("UPDATE ".DB::table('forum_activityapply')." SET message=CONCAT_WS(' 联系方式:', message, contact) WHERE contact<>''");
		DB::query("ALTER TABLE ".DB::table('forum_activityapply')." DROP contact");
	}
	if($row = DB::fetch_first("SHOW COLUMNS FROM ".DB::table('forum_postcomment')." LIKE 'authorid'")) {
		if(strstr($row['Type'], 'unsigned')) {
			DB::query("ALTER TABLE ".DB::table('forum_postcomment')." CHANGE authorid authorid mediumint(8) NOT NULL default '0'");
			DB::query("UPDATE ".DB::table('forum_postcomment')." SET authorid='-1' WHERE authorid='0'");
		}
	}
	if(!$row = DB::fetch_first("SHOW COLUMNS FROM ".DB::table('common_failedlogin')." LIKE 'username'")) {
		DB::query("TRUNCATE ".DB::table('common_failedlogin'));
		DB::query("ALTER TABLE ".DB::table('common_failedlogin')." ADD username char(15) NOT NULL default '' AFTER ip");
		DB::query("ALTER TABLE ".DB::table('common_failedlogin')." DROP PRIMARY KEY");
		DB::query("ALTER TABLE ".DB::table('common_failedlogin')." ADD PRIMARY KEY ipusername (ip,username)");
	}
	if(!$row = DB::fetch_first("SHOW COLUMNS FROM ".DB::table('forum_forumfield')." LIKE 'seodescription'")) {
		DB::query("ALTER TABLE ".DB::table('forum_forumfield')." ADD seodescription text NOT NULL default '' COMMENT '版块seo描述' AFTER keywords");
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET seodescription=description WHERE membernum='0'");
	}
	show_msg('准备完毕，进入下一步数据库结构升级', $theurl.'?step=sql');
} elseif ($_GET['step'] == 'sql') {

	$sql = implode('', file($sqlfile));
	preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	if(empty($newtables) || empty($newsqls)) {
		show_msg('SQL文件内容为空，请确认');
	}

	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$count_i = count($newtables);
	if($i>=$count_i) {
		show_msg('数据库结构升级完毕，进入下一步数据升级操作', $theurl.'?step=data');
	}
	$newtable = $newtables[$i];

	$specid = intval($_GET['specid']);
	if($specid && in_array($newtable, array('forum_post', 'forum_thread'))) {
		$spectable = $newtable;
		$newtable = get_special_table_by_num($newtable, $specid);
	}

	$newcols = getcolumn($newsqls[$i]);

	if(!$query = DB::query("SHOW CREATE TABLE ".DB::table($newtable), 'SILENT')) {
		preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*\=/is", $newsqls[$i], $maths);

		if(strpos($newtable, 'common_session')) {
			$type = mysql_get_server_info() > '4.1' ? " ENGINE=MEMORY".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" ): " TYPE=HEAP";
		} else {
			$type = mysql_get_server_info() > '4.1' ? " ENGINE=MYISAM".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" ): " TYPE=MYISAM";
		}
		$usql = $maths[1].$type;

		$usql = str_replace("CREATE TABLE IF NOT EXISTS pre_", 'CREATE TABLE IF NOT EXISTS '.$config['tablepre'], $usql);
		$usql = str_replace("CREATE TABLE pre_", 'CREATE TABLE '.$config['tablepre'], $usql);
		if(!DB::query($usql, 'SILENT')) {
			show_msg('添加表 '.DB::table($newtable).' 出错,请手工执行以下SQL语句后,再重新运行本升级程序:<br><br>'.dhtmlspecialchars($usql));
		} else {
			$msg = '添加表 '.DB::table($newtable).' 完成';
		}
	} else {
		$value = DB::fetch($query);
		$oldcols = getcolumn($value['Create Table']);

		$updates = array();
		foreach ($newcols as $key => $value) {
			if($key == 'PRIMARY') {
				if($value != $oldcols[$key]) {
					if(!empty($oldcols[$key])) {
						$usql = "RENAME TABLE ".DB::table($newtable)." TO ".DB::table($newtable.'_bak');
						if(!DB::query($usql, 'SILENT')) {
							show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
						} else {
							$msg = '表改名 '.DB::table($newtable).' 完成！';
							show_msg($msg, $theurl.'?step=sql&i='.$_GET['i']);
						}
					}
					$updates[] = "ADD PRIMARY KEY $value";
				}
			} elseif ($key == 'KEY') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['KEY'][$subkey])) {
						if($subvalue != $oldcols['KEY'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD INDEX `$subkey` $subvalue";
						}
					} else {
						$updates[] = "ADD INDEX `$subkey` $subvalue";
					}
				}
			} elseif ($key == 'UNIQUE') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['UNIQUE'][$subkey])) {
						if($subvalue != $oldcols['UNIQUE'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
						}
					} else {
						$usql = "ALTER TABLE  ".DB::table($newtable)." DROP INDEX `$subkey`";
						DB::query($usql, 'SILENT');
						$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
					}
				}
			} else {
				if(!empty($oldcols[$key])) {
					if(strtolower($value) != strtolower($oldcols[$key])) {
						$updates[] = "CHANGE `$key` `$key` $value";
					}
				} else {
					$updates[] = "ADD `$key` $value";
				}
			}
		}

		if(!empty($updates)) {
			$usql = "ALTER TABLE ".DB::table($newtable)." ".implode(', ', $updates);
			if(!DB::query($usql, 'SILENT')) {
				show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
			} else {
				$msg = '升级表 '.DB::table($newtable).' 完成！';
			}
		} else {
			$msg = '检查表 '.DB::table($newtable).' 完成，不需升级，跳过';
		}
	}

	if($specid) {
		$newtable = $spectable;
	}

	if(get_special_table_by_num($newtable, $specid+1)) {
		$next = $theurl . '?step=sql&i='.($_GET['i']).'&specid='.($specid + 1);
	} else {
		$next = $theurl.'?step=sql&i='.($_GET['i']+1);
	}
	show_msg("[ $i / $count_i ] ".$msg, $next);

} elseif ($_GET['step'] == 'data') {
	if(empty($_GET['op']) || $_GET['op'] == 'realname') {

		$nextop = 'profile';

		$p = 1000;
		$i = !empty($_GET['i']) ? intval($_GET['i']) : 0;
		$n = 0;
		if($i==0) {
			$value = DB::fetch_first('SELECT * FROM '.DB::table('common_member_profile_setting')." WHERE fieldid = 'realname'");
			if(!empty($value)) {
				show_msg("实名功能升级完毕", "$theurl?step=data&op=$nextop");
			}
			DB::query("INSERT INTO ".DB::table('common_member_profile_setting')." VALUES ('realname', '1', '0', '1', '真实姓名', '', '0', '0', '0', '0', '1', 'text', '0', '', '', '0', '0')");
		}
		$t = DB::result_first('SELECT uid FROM '.DB::table('common_member')." ORDER BY uid DESC LIMIT 1");
		$names = $uids = array();
		$query = DB::query('SELECT * FROM '.DB::table('common_member')." WHERE uid>'$i' AND realname != '' LIMIT $p");
		while($value=DB::fetch($query)) {
			$n = intval($value['uid']);
			$value['uid'] = intval($value['uid']);
			$value['realname'] = addslashes($value['realname']);
			DB::update('common_member_profile', array('realname'=>$value['realname']), array('uid'=>$value['uid']));
			DB::update('common_member', array('realname'=>''), array('uid'=>$value['uid']));
			$names[$value['uid']] = $value['realname'];
		}

		if($n>0) {
			show_msg("实名功能升级中[$n/$t]", "$theurl?step=data&op=realname&i=$n");
		} else {
			show_msg("实名功能升级完毕", "$theurl?step=data&op=$nextop");
		}

	} elseif($_GET['op'] == 'profile') {
		$nextop = 'setting';
		$value = DB::result_first('SELECT count(*) FROM '.DB::table('common_member_profile_setting')." WHERE fieldid = 'birthdist'");
		if(!$value) {
			DB::query("INSERT INTO ".DB::table('common_member_profile_setting')." VALUES ('birthdist', 1, 0, 0, '出生县', '出生行政区/县', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', '')");
			DB::query("INSERT INTO ".DB::table('common_member_profile_setting')." VALUES ('birthcommunity', 1, 0, 0, '出生小区', '', 0, 0, 0, 0, 0, 0, 0, 'select', 0, '', '')");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='出生地' WHERE fieldid = 'birthcity'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='居住地' WHERE fieldid = 'residecity'");
		}
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_district')." WHERE `level`='1' AND `usetype`>'0'");
		if(!$count) {
			DB::query("UPDATE ".DB::table('common_district')." SET `usetype`='3' WHERE `level` = '1'");
		}
		$profile = DB::fetch_first('SELECT * FROM '.DB::table('common_member_profile_setting')." WHERE fieldid = 'birthday'");
		if($profile['title'] == '出生日期') {
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='生日' WHERE fieldid = 'birthday'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='证件类型' WHERE fieldid = 'idcardtype'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='支付宝' WHERE fieldid = 'alipay'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='ICQ' WHERE fieldid = 'icq'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='QQ' WHERE fieldid = 'qq'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='MSN' WHERE fieldid = 'msn'");
			DB::query("UPDATE ".DB::table('common_member_profile_setting')." SET title='阿里旺旺' WHERE fieldid = 'taobao'");
		}
		show_msg("用户栏目升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'setting') {
		$nextop = 'admingroup';
		$settings = $newsettings = array();
		$query = DB::query('SELECT * FROM '.DB::table('common_setting')." WHERE 1");
		while($value=DB::fetch($query)) {
			$settings[$value[skey]] = $value['svalue'];
		}

		if(!isset($settings['portalstatus'])) {
			DB::insert('common_setting', array(
				'skey' => 'portalstatus',
				'svalue' => '1',
			), false, true);
		}

		if(!isset($settings['homestatus'])) {
			DB::insert('common_setting', array(
				'skey' => 'homestatus',
				'svalue' => '1',
			), false, true);
		}

		if(empty($settings['my_siteid']) && !empty($settings['connectsiteid'])) {
			DB::insert('common_setting', array(
				'skey' => 'my_siteid',
				'svalue' => $settings['connectsiteid'],
			), false, true);
			DB::delete('common_setting', "skey='connectsiteid'");
		}

		if(empty($settings['my_sitekey']) && !empty($settings['connectsitekey'])) {
			DB::insert('common_setting', array(
				'skey' => 'my_sitekey',
				'svalue' => $settings['connectsitekey'],
			), false, true);
			DB::delete('common_setting', "skey='connectsitekey'");
		}

		DB::insert('common_setting', array(
			'skey' => 'adminnotifytypes',
			'svalue' => 'verifythread,verifypost,verifyuser,verifyblog,verifydoing,verifypic,verifyshare,verifycommontes,verifyrecycle,verifyrecyclepost,verifyarticle,verifyacommont,verifymedal,verify_1,verify_2,verify_3,verify_4,verify_5,verify_6,verify_7',
		), false, true);

		if(!isset($settings['allowwidthauto'])) {
			DB::insert('common_setting', array(
				'skey' => 'allowwidthauto',
				'svalue' => '1',
			), false, true);
			DB::insert('common_setting', array(
				'skey' => 'switchwidthauto',
				'svalue' => '1',
			), false, true);
		}
		if(!$settings['activitypp']) {
			DB::insert('common_setting', array(
				'skey' => 'activitypp',
				'svalue' => '8',
			), false, true);
		}
		if(!isset($settings['allowpostcomment'])) {
			DB::insert('common_setting', array(
				'skey' => 'allowpostcomment',
				'svalue' => addslashes(serialize(array('1'))),
			), false, true);
		}

		if($settings['heatthread']) {
			$settings['heatthread'] = unserialize($settings['heatthread']);
			if(empty($settings['heatthread']['type'])) {
				$settings['heatthread']['type'] = 1;
				$settings['heatthread']['period'] = 15;
			}
			$newheatthread = addslashes(serialize($settings['heatthread']));
			DB::insert('common_setting', array(
				'skey' => 'heatthread',
				'svalue' => $newheatthread,
			), false, true);
		}

		if($settings['seotitle'] && unserialize($settings['seotitle']) === FALSE) {
			$rownew = array('forum' => $settings['seotitle']);
			DB::insert('common_setting', array(
				'skey' => 'seotitle',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['seokeywords'] && unserialize($settings['seokeywords']) === FALSE) {
			$rownew = array('forum' => $settings['seokeywords']);
			DB::insert('common_setting', array(
				'skey' => 'seokeywords',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['seodescription'] && unserialize($settings['seodescription']) === FALSE) {
			$rownew = array('forum' => $settings['seodescription']);
			DB::insert('common_setting', array(
				'skey' => 'seodescription',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['watermarkminheight'] && unserialize($settings['watermarkminheight']) === FALSE) {
			$rownew = array('portal' => $settings['watermarkminheight'], 'forum' => $settings['watermarkminheight'], 'album' => $settings['watermarkminheight']);
			DB::insert('common_setting', array(
				'skey' => 'watermarkminheight',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['watermarkminwidth'] && unserialize($settings['watermarkminwidth']) === FALSE) {
			$rownew = array('portal' => $settings['watermarkminwidth'], 'forum' => $settings['watermarkminwidth'], 'album' => $settings['watermarkminwidth']);
			DB::insert('common_setting', array(
				'skey' => 'watermarkminwidth',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['watermarkquality'] && unserialize($settings['watermarkquality']) === FALSE) {
			$rownew = array('portal' => $settings['watermarkquality'], 'forum' => $settings['watermarkquality'], 'album' => $settings['watermarkquality']);
			DB::insert('common_setting', array(
				'skey' => 'watermarkquality',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['watermarkstatus'] && unserialize($settings['watermarkstatus']) === FALSE) {
			$rownew = array('portal' => $settings['watermarkstatus'], 'forum' => $settings['watermarkstatus'], 'album' => $settings['watermarkstatus']);
			DB::insert('common_setting', array(
				'skey' => 'watermarkstatus',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['watermarktrans'] && unserialize($settings['watermarktrans']) === FALSE) {
			$rownew = array('portal' => $settings['watermarktrans'], 'forum' => $settings['watermarktrans'], 'album' => $settings['watermarktrans']);
			DB::insert('common_setting', array(
				'skey' => 'watermarktrans',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['watermarktype'] && unserialize($settings['watermarktype']) === FALSE) {
			$watermarktype_map = array(
				0 => 'gif',
				1 => 'png',
				2 => 'text',
			);
			$rownew = array('portal' => $watermarktype_map[$settings['watermarktype']], 'forum' => $watermarktype_map[$settings['watermarktype']], 'album' => $watermarktype_map[$settings['watermarktype']]);
			DB::insert('common_setting', array(
				'skey' => 'watermarktype',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if($settings['watermarktext'] && unserialize($settings['watermarktext']) === FALSE) {
			$rownew = array();
			$watermarktext = (array)unserialize($settings['watermarktext']);
			foreach($watermarktext as $data_k => $data_v) {
				$rownew[$data_k]['portal'] = $data_v;
				$rownew[$data_k]['forum'] = $data_v;
				$rownew[$data_k]['album'] = $data_v;
			}
			DB::insert('common_setting', array(
				'skey' => 'watermarktext',
				'svalue' => addslashes(serialize($rownew)),
			), false, true);
		}
		if(!$settings['mobile']) {
			DB::insert('common_setting', array(
				'skey' => 'mobile',
				'svalue' => 'a:2:{s:11:"allowmobile";i:0;s:13:"mobilepreview";i:1;}',
			), false ,true);
		}
		if(!$settings['card']) {
			DB::insert('common_setting', array(
				'skey' => 'card',
				'svalue' => 'a:1:{s:4:"open";s:1:"0";}',
			), false, true);
		}
		DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('group_allowfeed', '1')");
		if(empty($settings['relatenum'])) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('relatenum', '10')");
		}
		if(!isset($settings['profilegroup'])) {
			$profilegroupnew = serialize(array(
				'base' =>
				array (
				  'available' => 1,
				  'displayorder' => 0,
				  'title' => '基本资料',
				  'field' =>
				  array (
					'realname' => 'realname',
					'gender' => 'gender',
					'birthday' => 'birthday',
					'birthcity' => 'birthcity',
					'residecity' => 'residecity',
					'residedist' => 'residedist',
					'affectivestatus' => 'affectivestatus',
					'lookingfor' => 'lookingfor',
					'bloodtype' => 'bloodtype',
					'field1' => 'field1',
					'field2' => 'field2',
					'field3' => 'field3',
					'field4' => 'field4',
					'field5' => 'field5',
					'field6' => 'field6',
					'field7' => 'field7',
					'field8' => 'field8',
				  ),
				),
				'contact' =>
				array (
				  'title' => '联系方式',
				  'available' => '1',
				  'displayorder' => '1',
				  'field' =>
				  array (
					'telephone' => 'telephone',
					'mobile' => 'mobile',
					'icq' => 'icq',
					'qq' => 'qq',
					'yahoo' => 'yahoo',
					'msn' => 'msn',
					'taobao' => 'taobao',
				  ),
				),
				'edu' =>
				array (
				  'available' => 1,
				  'displayorder' => 2,
				  'title' => '教育情况',
				  'field' =>
				  array (
					'graduateschool' => 'graduateschool',
					'education' => 'education',
				  ),
				),
				'work' =>
				array (
				  'available' => 1,
				  'displayorder' => 3,
				  'title' => '工作情况',
				  'field' =>
				  array (
					'occupation' => 'occupation',
					'company' => 'company',
					'position' => 'position',
					'revenue' => 'revenue',
				  ),
				),
				'info' =>
				array (
				  'title' => '个人信息',
				  'available' => '1',
				  'displayorder' => '4',
				  'field' =>
				  array (
					'idcardtype' => 'idcardtype',
					'idcard' => 'idcard',
					'address' => 'address',
					'zipcode' => 'zipcode',
					'site' => 'site',
					'bio' => 'bio',
					'interest' => 'interest',
					'sightml' => 'sightml',
					'customstatus' => 'customstatus',
					'timeoffset' => 'timeoffset',
				  ),
				),
			));
			DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('profilegroup', '$profilegroupnew')");
		}
		if(!isset($settings['ranklist'])) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('ranklist', '".'a:11:{s:6:"status";s:1:"1";s:10:"cache_time";s:1:"1";s:12:"index_select";s:8:"thisweek";s:6:"member";a:3:{s:9:"available";s:1:"1";s:10:"cache_time";s:1:"5";s:8:"show_num";s:2:"20";}s:6:"thread";a:3:{s:9:"available";s:1:"1";s:10:"cache_time";s:1:"5";s:8:"show_num";s:2:"20";}s:4:"blog";a:3:{s:9:"available";s:1:"1";s:10:"cache_time";s:1:"5";s:8:"show_num";s:2:"20";}s:4:"poll";a:3:{s:9:"available";s:1:"1";s:10:"cache_time";s:1:"5";s:8:"show_num";s:2:"20";}s:8:"activity";a:3:{s:9:"available";s:1:"1";s:10:"cache_time";s:1:"5";s:8:"show_num";s:2:"20";}s:7:"picture";a:3:{s:9:"available";s:1:"1";s:10:"cache_time";s:1:"5";s:8:"show_num";s:2:"20";}s:5:"forum";a:3:{s:9:"available";s:1:"1";s:10:"cache_time";s:1:"5";s:8:"show_num";s:2:"20";}s:5:"group";a:3:{s:9:"available";s:1:"1";s:10:"cache_time";s:1:"5";s:8:"show_num";s:2:"20";}}'."')");
		}
		if(!isset($settings['ipregctrltime'])) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('ipregctrltime', '72')");
		}
		DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('regname', 'register')");
		if(empty($settings['reglinkname'])) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('reglinkname', '注册')");
		}

		if(empty($settings['domain'])) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('domain', '".'a:5:{s:12:"defaultindex";s:9:"forum.php";s:10:"holddomain";s:18:"www|*blog*|*space*";s:4:"list";a:0:{}s:3:"app";a:5:{s:6:"portal";s:0:"";s:5:"forum";s:0:"";s:5:"group";s:0:"";s:4:"home";s:0:"";s:7:"default";s:0:"";}s:4:"root";a:5:{s:4:"home";s:0:"";s:5:"group";s:0:"";s:5:"forum";s:0:"";s:5:"topic";s:0:"";s:7:"channel";s:0:"";}}'."')");
		}
		if(empty($settings['group_recommend'])) {
			if($settings['newbiespan'] > 0) {
				$newsettings['newbiespan'] = round($settings['newbiespan'] * 60);
			}
			DB::query("UPDATE ".DB::table('common_member_field_forum')." SET attentiongroup=''");

			$query = DB::query("SELECT f.fid, f.name, ff.description, ff.icon FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='3' AND f.type='sub' ORDER BY f.commoncredits desc LIMIT 8");
			while($row = DB::fetch($query)) {
				$row['name'] = addslashes($row['name']);
				$settings['attachurl'] .= substr($settings['attachurl'], -1, 1) != '/' ? '/' : '';
				if($row['icon']) {
					$row['icon'] = $settings['attachurl'].'group/'.$row['icon'];
				} else {
					$row['icon'] = 'static/image/common/groupicon.gif';
				}
				$row['description'] = addslashes($row['description']);
				$group_recommend[$row[fid]] = $row;
			}
			$newsettings['group_recommend'] = serialize($group_recommend);
			if($newsettings) {
				foreach($newsettings as $skey => $svalue) {
					DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('$skey', '$svalue')");
				}
			}
		}

		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_magic')." WHERE credit>'0'")) {
			$creditstranssi = explode(',', $settings['creditstrans']);
			$creditstran = $creditstranssi[3] ? $creditstranssi[3] : $creditstranssi[0];
			DB::update('common_magic', array('credit' => $creditstran));
		}
		if(!isset($settings['allowviewuserthread'])) {
			$allowviewuserthread = array('allow'=>'1','fids'=>array());
			$query = DB::query('SELECT ff.fid,ff.viewperm FROM '.DB::table('forum_forum').' f LEFT JOIN '.DB::table('forum_forumfield')." ff ON f.fid = ff.fid WHERE f.status='1' AND f.type IN ('forum','sub')");
			while($value = DB::fetch($query)) {
				$arr = !empty($value['viewperm']) ? explode("\t", $value['viewperm']) : array();
				if(empty($value['viewperm']) || in_array('7', $arr) ||  in_array($settings['newusergroupid'], $arr) ) {
					$allowviewuserthread['fids'][] = $value['fid'];
				}
			}
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('allowviewuserthread', '".addslashes(serialize($allowviewuserthread))."')");
		}
		if(!isset($settings['focus'])) {
			$focusnew = array('title' => '站长推荐', 'cookie' => 1);
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('focus', '".addslashes(serialize($focusnew))."')");
		} else {
			$focus = unserialize($settings['focus']);
			if(!isset($focus['cookie'])) {
				$focus['cookie'] = 1;
				DB::query("UPDATE ".DB::table('common_setting')." SET svalue='".addslashes(serialize($focus))."' WHERE skey='focus'");
			}
		}
		if(!isset($settings['onlyacceptfriendpm'])) {
			$onlyacceptfriendpmnew = '0';
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('onlyacceptfriendpm', '$onlyacceptfriendpmnew')");
		}
		if(!isset($settings['pmreportuser'])) {
			$pmreportusernew = '1';
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('pmreportuser', '$pmreportusernew')");
		}
		if(!isset($settings['chatpmrefreshtime'])) {
			$chatpmrefreshtimenew = '8';
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('chatpmrefreshtime', '$chatpmrefreshtimenew')");
		}
		if(!isset($settings['preventrefresh'])) {
			$preventrefreshnew = '1';
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('preventrefresh', '$preventrefreshnew')");
		}
		if(!isset($settings['article_tags'])) {
			$article_tagsnew = addslashes(serialize(array(1 => '原创', 2 => '热点', 3 => '组图', 4 => '爆料', 5 => '头条', 6 => '幻灯', 7 => '滚动', 8 => '推荐')));
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('article_tags', '$article_tagsnew')");
		}
		if(empty($settings['anonymoustext'])) {
			DB::query("REPLACE INTO ".DB::table('common_setting')." VALUES ('anonymoustext', '匿名')");
		}
		if(!$word_type_count = DB::result_first("SELECT count(*) FROM ".DB::table('common_word_type')."")) {
			DB::query("INSERT INTO ".DB::table('common_word_type')." VALUES('1', '政治'),('2', '广告')");
		}
		if(!isset($settings['userreasons'])) {
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('userreasons', '很给力!\r\n神马都是浮云\r\n赞一个!\r\n山寨\r\n淡定')");
		}
		if(!$forum_typevar_search = DB::result_first("SELECT count(*) FROM ".DB::table('forum_typevar')." WHERE search > 2 LIMIT 1")) {
			DB::query("UPDATE ".DB::table('forum_typevar')." SET search = '3' WHERE search = '1'");
		}
		if($seccodecheck = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey = 'seccodestatus' LIMIT 1")) {
			if(!($seccodecheck & 16)) {
				$seccodecheck = setstatus(5, 1, $seccodecheck);
				DB::query("UPDATE ".DB::table('common_setting')." SET svalue = '$seccodecheck' WHERE skey = 'seccodestatus'");
			}
		}

		if(!isset($settings['lazyload'])) {
			DB::query("INSERT INTO ".DB::table('common_setting')." VALUES ('lazyload', '0')");
		}

		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_addon')." WHERE `key` = '25z5wh0o00' AND siteurl = 'http://addons.discuz.com' LIMIT 1")) {
			DB::query("REPLACE INTO ".DB::table('common_addon')." (`key`, `title`, `sitename`, `siteurl`, `description`, `contact`, `logo`, `system`) VALUES ('25z5wh0o00', 'Comsenz', 'Discuz! 扩展中心', 'http://addons.discuz.com', 'Discuz! 扩展中心最新的论坛插件', 'http://addons.discuz.com/contact', 'http://www.comsenz.com/addon/logo.gif', 1)");
		}

		if(!DB::result_first("SELECT allowreplycredit FROM ".DB::table('common_usergroup_field')." WHERE groupid = 1")) {
			DB::query("UPDATE ".DB::table('common_usergroup_field')." SET allowreplycredit = '1' WHERE groupid = 1");
		}
		DB::delete('common_addon', "`key`='R051uc9D1i'");
		show_msg("配置项升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'admingroup') {
		$nextop = 'updatethreadtype';
		if(!DB::result_first("SELECT allowclearrecycle FROM ".DB::table('common_admingroup')." WHERE allowclearrecycle='1'")) {
			DB::query('UPDATE '.DB::table('common_admingroup')." SET allowclearrecycle='1' WHERE admingid='1' OR admingid='2'");
		}
		DB::query('UPDATE '.DB::table('common_admingroup')." SET allowmanagetag='1' WHERE admingid IN ('1', '2', '3')");
		DB::query('UPDATE '.DB::table('common_usergroup_field')." SET allowposttag='1' WHERE groupid=1");
		if(DB::result_first("SELECT cpgroupid FROM ".DB::table('common_admincp_group')." WHERE cpgroupid='3'")) {
			if(!DB::result_first("SELECT cpgroupid FROM ".DB::table('common_admincp_perm')." WHERE cpgroupid='3' AND perm='threads_group'")) {
				DB::query("INSERT INTO ".DB::table('common_admincp_perm')." VALUES ('3', 'threads_group')");
				DB::query("INSERT INTO ".DB::table('common_admincp_perm')." VALUES ('3', 'prune_group')");
				DB::query("INSERT INTO ".DB::table('common_admincp_perm')." VALUES ('3', 'attach_group')");
				DB::query("ALTER TABLE ".DB::table('common_admingroup')." DROP `disablepostctrl`");
				DB::query("UPDATE ".DB::table('common_usergroup_field')." SET allowgroupdirectpost='3'");
				DB::query("UPDATE ".DB::table('common_usergroup_field')." SET allowgroupposturl='3' WHERE groupid='1'");
			}
		}
		if(DB::result_first("SELECT cpgroupid FROM ".DB::table('common_admincp_group')." WHERE cpgroupid='1'")) {
			if(!DB::result_first("SELECT cpgroupid FROM ".DB::table('common_admincp_perm')." WHERE cpgroupid='1' AND perm='postcomment'")) {
				DB::query("INSERT INTO ".DB::table('common_admincp_perm')." VALUES ('1', 'postcomment')");
			}
		}
		if(!DB::result_first("SELECT allowbanvisituser FROM ".DB::table('common_admingroup')." WHERE allowbanvisituser='1'")) {
			DB::query('UPDATE '.DB::table('common_admingroup')." SET allowbanvisituser='1' WHERE admingid='1' OR admingid='2'");
		}
		show_msg("管理组设置升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'updatethreadtype') {
		$nextop = 'updatecron';
		$selectoption = array();

		$query = DB::query("SELECT * FROM ".DB::table('forum_typeoption')." WHERE type='select'");
		while($typeoptionarr = DB::fetch($query)) {
			$selectoption[] = $typeoptionarr['identifier'];
		}

		$query = DB::query("SELECT * FROM ".DB::table('forum_threadtype'));
		while($threadtypearr = DB::fetch($query)) {
			if(DB::num_rows(DB::query("SHOW TABLES LIKE '".DB::table('forum_optionvalue')."$threadtypearr[typeid]'")) != 1) {
				continue;
			}
			$varnames = array();
			$queryoptionvalue = DB::query("SELECT * FROM ".DB::table('forum_optionvalue')."$threadtypearr[typeid] LIMIT 1");
			if($optionvaluearr = DB::fetch($queryoptionvalue)) {
				foreach($optionvaluearr as $key => $value) {
					if(in_array($key, $selectoption)) {
						$varnames[] = 'CHANGE `'.$key.'` `'.$key.'` VARCHAR(50) NOT NULL';
					}
				}
			}
			if(!empty($varnames)) {
				DB::query("ALTER TABLE ".DB::table('forum_optionvalue')."$threadtypearr[typeid] ".implode(',', $varnames));
			}
		}
		show_msg("分类信息升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'updatecron') {
		$nextop = 'updatemagic';
		if(!DB::result_first("SELECT filename FROM ".DB::table('common_cron')." WHERE filename='cron_cleanfeed.php'")) {
			DB::query("INSERT INTO ".DB::table('common_cron')." VALUES ('', '1','system','清理过期动态','cron_cleanfeed.php','1269746634','1269792000','-1','-1','0','0')");
		}

		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_cron')." WHERE filename='cron_birthday_daily.php'")) {
			DB::query("DELETE FROM ".DB::table('common_cron')." WHERE filename='cron_birthday_daily.php'");
		}

		show_msg("计划任务升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'updatemagic') {
		$nextop = 'updatereport';
		if(DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE identifier='highlight'")) {
			DB::query("UPDATE ".DB::table('common_magic')." SET name='变色卡', description='可以将帖子或日志的标题高亮，变更颜色' WHERE identifier='highlight'");
		}
		if(DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE identifier='namepost'")) {
			DB::query("UPDATE ".DB::table('common_magic')." SET name='显身卡', description='可以查看一次匿名用户的真实身份。' WHERE identifier='namepost'");
		}
		if(DB::result_first("SELECT name FROM ".DB::table('common_magic')." WHERE identifier='anonymouspost'")) {
			DB::query("UPDATE ".DB::table('common_magic')." SET name='匿名卡', description='在指定的地方，让自己的名字显示为匿名。' WHERE identifier='anonymouspost'");
		}

		show_msg("道具升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'updatereport') {
		$nextop = 'myappcount';
		if(!DB::result_first('SELECT skey FROM '.DB::table('common_setting')." WHERE skey='report_reward'")) {
			$report_uids = array();
			$founders = $_G['config']['admincp']['founder'] !== '' ? explode(',', str_replace(' ', '', addslashes($_G['config']['admincp']['founder']))) : array();
			if($founders) {
				$founderexists = true;
				$fuid = $fuser = array();
				foreach($founders as $founder) {
					if(is_numeric($founder)) {
						$fuid[] = $founder;
					} else {
						$fuser[] = $founder;
					}
				}
				$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE ".($fuid ? "uid IN (".dimplode($fuid).")" : '0')." OR ".($fuser ? "username IN (".dimplode($fuser).")" : '0'));
				while($founder = DB::fetch($query)) {
					$report_uids[] = $founder['uid'];
				}
			}
			$query = DB::query("SELECT uid FROM ".DB::table('common_admincp_perm')." ap LEFT JOIN ".DB::table('common_admincp_member')." am ON am.cpgroupid=ap.cpgroupid where perm='report'");
			while($user = DB::fetch($query)) {
				if(empty($users[$user[uid]])) {
					$report_uids[] = $user['uid'];
				}
			}
			if($report_uids) {
				$report_receive = serialize(array('adminuser' => $report_uids, 'supmoderator' => array()));
				DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('report_receive', '$report_receive')");
			}
			$report_reward = array();
			$report_reward['min'] = '-3';
			$report_reward['max'] = '3';
			$report_reward = serialize($report_reward);
			DB::query("REPLACE INTO ".DB::table('common_setting')." (skey, svalue) VALUES ('report_reward', '$report_reward')");
		}

		show_msg("举报升级完成", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'myappcount') {

		$nextop = 'nav';
		$needupgrade = DB::query("SELECT COUNT(*) FROM ".DB::table('common_myapp_count'), 'SILENT');
		if($needupgrade) {
			DB::query("DROP TABLE `".DB::table('common_myapp_count')."`");
			DB::query("DROP TABLE `".DB::table('home_userapp_stat')."`");
		}
		show_msg("漫游应用统计升级完成", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'nav') {

		$nextop = 'forumstatus';

		$query = DB::query("SELECT * FROM ".DB::table('common_nav')." WHERE type='0'");
		$navs = array();
		while($nav = DB::fetch($query)) {
			$navs[] = $nav;
		}
		$navs = daddslashes($navs);
		DB::delete('common_nav', "type='0'");
		DB::delete('common_nav', "name='{hr}'");
		DB::delete('common_nav', "name='{userpanelarea1}'");
		DB::delete('common_nav', "name='{userpanelarea2}'");
		$sql = implode('', file(DISCUZ_ROOT.'./install/data/install_data.sql'));
		preg_match("/\[update\_nav\](.+?)\[\/update\_nav\]/is", $sql, $a);
		runquery($a[1]);
		foreach($navs as $nav) {
			if($nav['identifier']) {
				DB::update('common_nav', array('name' => $nav['name'], 'available' => $nav['available'], 'displayorder' => $nav['displayorder']),
					"navtype='$nav[navtype]' AND identifier='$nav[identifier]'");
			}
		}

		show_msg("导航数据升级完成", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'forumstatus') {

		$nextop = 'poststick';
		$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE status='2'");
		if(DB::num_rows($query)) {
			while($row = DB::fetch($query)) {
				$fids[] = $row['fid'];
			}
			DB::update('forum_forum', array('status' => 1), "status='2'");
		}

		show_msg("版块状态升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'poststick') {

		$nextop = 'usergroup';
		$query = DB::query("SELECT * FROM ".DB::table('forum_postposition')." WHERE stick='1'", 'SILENT');
		if(DB::num_rows($query)) {
			while($row = DB::fetch($query)) {
				DB::query("REPLACE INTO ".DB::table('forum_poststick')." SET tid='$row[tid]', pid='$row[pid]', position='$row[position]', dateline='$row[dateline]'");
			}
			DB::query("DELETE FROM ".DB::table('forum_postposition')." WHERE stick='1'");
		}

		show_msg("回帖推荐升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'usergroup') {
		$nextop = 'creditrule';
		DB::update('common_usergroup', array('allowvisit' => 2), "groupid='1'");
		if(DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_forum')." WHERE allowmediacode>'0'") &&
			!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_usergroup_field')." WHERE allowmediacode>'0'")) {
			DB::update('common_usergroup_field', array('allowmediacode' => 1), "groupid<'4' OR groupid>'9'");
		}
		show_msg("用户升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'creditrule') {
		$nextop = 'bbcode';
		$delrule = array('register', 'realname', 'invitefriend', 'report', 'uploadimage', 'editrealname', 'editrealemail', 'delavatar');
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_credit_rule')." WHERE action IN(".dimplode($delrule).")"),0);
		if($count) {
			DB::query("DELETE FROM ".DB::table('common_credit_rule')." WHERE action IN(".dimplode($delrule).")");
		}
		DB::update('common_credit_rule', array('rulename' => '每天登录'), "rulename='每天登陆'");
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_rule')." WHERE action='portalcomment'");
		if(!$count) {
			DB::query("INSERT INTO ".DB::table('common_credit_rule')." (`rulename`, `action`, `cycletype`, `cycletime`, `rewardnum`, `norepeat`, `extcredits1`, `extcredits2`, `extcredits3`, `extcredits4`, `extcredits5`, `extcredits6`, `extcredits7`, `extcredits8`, `fids`) VALUES ('文章评论','portalcomment','1','0','40','1','0','1','0','0','0','0','0','0','')");
		}

		show_msg("积分规则升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'bbcode') {
		$nextop = 'stamp';
		$allowcusbbcodes = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_usergroup_field'));
		while($row = DB::fetch($query)) {
			if($row['allowcusbbcode']) {
				$allowcusbbcodes[] = $row['groupid'];
			}
		}
		if($allowcusbbcodes) {
			DB::query("UPDATE ".DB::table('forum_bbcode')." SET perm='".implode("\t", $allowcusbbcodes)."' WHERE perm=''");
		}
		show_msg("自定义代码权限升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'stamp') {
		$nextop = 'block_item';
		$stampnew = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE stamp>'0'");
		if(!$stampnew) {
			$query = DB::query("SELECT t.tid, tm.stamp FROM ".DB::table('forum_thread')." t
				INNER JOIN ".DB::table('forum_threadmod')." tm ON t.tid=tm.tid AND tm.action='SPA'
				WHERE t.status|16=t.status");
			while($row = DB::fetch($query)) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET stamp='$row[stamp]' WHERE tid='$row[tid]'", 'UNBUFFERED');
			}
		}
		DB::query("REPLACE INTO ".DB::table('common_smiley')." (id, typeid, displayorder, type, code, url) VALUES ('83','4','9','stamp','编辑采用','010.gif')");
		DB::query("REPLACE INTO ".DB::table('common_smiley')." (id, typeid, displayorder, type, code, url) VALUES ('84','0','18','stamplist','编辑采用','010.small.gif')");
		require_once libfile('function/cache');
		updatecache('stamps');
		updatecache('stamptypeid');
		show_msg("鉴定图章升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'block_item') {
		$nextop = 'block_permission';
		$bids = $items = $blocks = array();
		$query = DB::query("SELECT itemid, bid, pic, picflag, thumbpath FROM ".DB::table('common_block_item')." WHERE makethumb='1'");
		while($row = DB::fetch($query)) {
			if(empty($row['thumbpath'])) {
				$bids[$row['bid']] = $row['bid'];
				$items[] = $row;
			}
		}
		if($bids) {
			$query = DB::query("SELECT bid, picwidth, picheight FROM ".DB::table('common_block')." WHERE bid IN(".dimplode($bids).")");
			while($value = DB::fetch($query)) {
				$blocks[$value['bid']] = $value;
			}
			foreach($items as $item) {
				$block = $blocks[$item['bid']];
				$hash = md5($item['pic'].'-'.$item['picflag'].':'.$block['picwidth'].'|'.$block['picheight']);
				$thumbpath = 'block/'.substr($hash, 0, 2).'/'.$hash.'.jpg';
				DB::update('common_block_item', array('thumbpath' => $thumbpath), "itemid='$item[itemid]'");
			}
		}
		show_msg("模块缩略图权限升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'block_permission') {
		$nextop = 'portalcategory_permission';
		if(!DB::result_first('SELECT skey FROM '.DB::table('common_setting')." WHERE skey='group_recommend' LIMIT 1")) {
			DB::query("UPDATE ".DB::table('common_block_permission')." SET allowmanage=allowsetting,allowrecomment=allowdata");
		}
		if(!DB::result_first('SELECT inheritedtplname FROM '.DB::table('common_template_permission')." WHERE inheritedtplname != '' LIMIT 1")) {
			$query = DB::query('SELECT * FROM '.DB::table('common_template_permission')." WHERE inheritedtplname = ''");
			$templatearr = array();
			while($value = DB::fetch($query)) {
				$templatearr[$value['targettplname']][] = $value;
			}
			if(!empty($templatearr)) {
				require_once libfile('class/blockpermission');
				$tplpermissions = new template_permission();
				foreach($templatearr as $tplname => $users) {
					$tplpermissions->add_users($tplname, $users);
				}
			}
		}
		show_msg("模块权限升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'portalcategory_permission') {
		$nextop = 'portal_comment';
		if(!DB::result_first('SELECT inheritedcatid FROM '.DB::table('portal_category_permission')." WHERE inheritedcatid > '0' LIMIT 1")) {
			$query = DB::query('SELECT * FROM '.DB::table('portal_category_permission')." WHERE inheritedcatid = '0'");
			$catearr = array();
			while($value = DB::fetch($query)) {
				$catearr[$value['catid']][] = $value;
			}
			if(!empty($catearr)) {
				require_once libfile('class/portalcategory');
				$categorypermissions = new portal_category();
				foreach($catearr as $catid => $users) {
					$categorypermissions->add_users_perm($catid, $users);
				}
			}
		}
		show_msg("门户频道权限升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'portal_comment') {
		$nextop = 'portal_article_cover_img';
		$one = DB::fetch_first('SELECT * FROM '.DB::table('portal_comment')." WHERE id=0 AND idtype='' LIMIT 1");
		if($one && isset($one['aid'])) {
			DB::query("UPDATE ".DB::table('portal_comment')." SET id=aid,idtype='aid' WHERE aid>0");
		}
		show_msg("文章评论升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'portal_article_cover_img') {
		$nextop = 'block_style';
		$pic = DB::result_first('SELECT pic FROM '.DB::table('portal_article_title')." WHERE LENGTH(pic)>6 LIMIT 1");
		if($pic && is_numeric(substr($pic, 0, strpos($pic,'/')))) {
			DB::query("UPDATE ".DB::table('portal_article_title')." SET pic=CONCAT('portal/',pic) WHERE LENGTH(pic)>6");
		}
		show_msg("文章封面图升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'block_style') {
		$nextop = 'block_script';
		$sql = implode('', file(DISCUZ_ROOT.'./install/data/install_data.sql'));
		preg_match("/\[block\_style\](.+?)\[\/block\_style\]/is", $sql, $a);
		unset($sql);
		preg_match_all("/\[key\:(.+?)\](.+?)\[\/key\]/is", $a[1], $aa);
		$data = array();
		if(!empty($aa[1])) {
			foreach($aa[1] as $key => $value){
				$data[$value] = $aa[2][$key];
			}
			$hashs = array_keys($data);
			$query = DB::query('SELECT hash FROM '.DB::table('common_block_style')." WHERE hash IN (".dimplode($hashs).")");
			while($value = DB::fetch($query)) {
				unset($data[$value['hash']]);
			}
			if(!empty($data)) {
				$sql = implode("\r\n", $data);
				runquery($sql);
			}
			DB::query("UPDATE ".DB::table('common_block_style')." SET name = replace(`name`, 'X1.5', '内置')");
			DB::query("UPDATE ".DB::table('common_block_style')." SET name = replace(`name`, 'X2.0', '内置')");
		}
		show_msg("模块模板升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'block_script') {
		$nextop = 'common_usergroup_field';
		include_once libfile('function/block');
		$blocks = $styles = $styleids = array();
		$query = DB::query('SELECT * FROM '.DB::table('common_block')." WHERE blockclass='forum_attachment' OR blockclass='group_attachment'");
		while($value = DB::fetch($query)) {
			$blocks[$value['bid']] = $value;
			if(empty($value['blockstyle'])) {
				$styleids[$value['styleid']] = $value['styleid'];
			}
		}

		if($styleids) {
			$query = DB::query('SELECT * FROM '.DB::table('common_block_style')." WHERE styleid IN (".dimplode($styleids).")");
			while($value = DB::fetch($query)) {
				$value['template'] = unserialize($value['template']);
				$styles[$value['styleid']] = $value;
			}
		}
		foreach($blocks as $bid => $block) {
			unset($block['bid']);
			if(empty($block['blockstyle'])) {
				$block['blockstyle'] = $styles[$block['styleid']];
			} else {
				$block['blockstyle'] = unserialize($block['blockstyle']);
			}
			$block = block_conver_to_thread($block);
			DB::update('common_block', daddslashes($block), array('bid'=>$bid));

			DB::query('DELETE FROM '.DB::table('common_block_style')." WHERE blockclass='forum_attachment' OR blockclass='group_attachment'");
			$_G['block'][$bid] = $block;
			block_updatecache($bid, true);
		}

		show_msg("模块脚本升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'common_usergroup_field') {
		$nextop = 'group_index';
		if(!DB::result_first('SELECT skey FROM '.DB::table('common_setting')." WHERE skey='group_recommend' LIMIT 1")) {
			DB::query("UPDATE ".DB::table('common_usergroup_field')."
				SET allowcommentarticle=allowcomment,allowblogmod=allowblog,allowdoingmod=allowdoing,allowuploadmod=allowupload,allowsharemod=allowshare,allowdownlocalimg=allowpostarticle");
		}
		$queryraterange = DB::query("SELECT groupid, raterange FROM ".DB::table('common_usergroup_field'));
		while($usergroupfield = DB::fetch($queryraterange)) {
			if($usergroupfield['raterange']) {
				$raterangearray = array();
				foreach(explode("\n", $usergroupfield['raterange']) as $range) {
					$range = explode("\t", $range);
					if(count($range) == 4) {
						$raterangearray[$range[0]] = implode("\t", array($range[0], 'isself' => 0, 'min' => $range[1], 'max' => $range[2], 'mrpd' => $range[3]));
					}
				}
				if(!empty($raterangearray)) {
					DB::query("UPDATE ".DB::table('common_usergroup_field')." SET raterange='".implode("\n", $raterangearray)."' WHERE groupid='".$usergroupfield['groupid']."'");
				}
			}
		}
		show_msg("用户组权限升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'group_index') {
		$nextop = 'domain';
		if(!DB::result_first('SELECT skey FROM '.DB::table('common_setting')." WHERE skey='group_recommend' LIMIT 1")) {
			$arr = array(
				0 => array('importfile'=>'./data/group_index.xml','primaltplname'=>'group/index', 'targettplname'=>'group/index'),
			);
			foreach ($arr as $v) {
				import_diy($v['importfile'], $v['primaltplname'], $v['targettplname']);
			}
		}
		show_msg("群组首页升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'domain') {
		$nextop = 'pm';
		if(!empty($_G['config']['app']['domain'])) {
			$update = 0;
			foreach($_G['config']['app']['domain'] as $key => $value) {
				if($value && !$_G['setting']['domain']['app'][$key]) {
					$update = 1;
				}
			}
			if($update) {
				$domain = array(
					'defaultindex' => !empty($_G['config']['app']['default']) ? $_G['config']['app']['default'].'.php' : '',
					'app' => $_G['config']['app']['domain'],
				);
				DB::insert('common_setting', array('skey' => 'domain', 'svalue' => addslashes(serialize($domain))), false, true);
			}
		}
		if(!empty($_G['config']['app']['default']) && !$_G['setting']['defaultindex']) {
			DB::insert('common_setting', array('skey' => 'defaultindex', 'svalue' => $_G['config']['app']['default'].'.php'), 0, 1);
		}
		if(!empty($_G['config']['home']['holddomain']) && !$_G['setting']['holddomain']) {
			$holddomain = implode('|', explode(',', $_G['config']['home']['holddomain']));
			DB::insert('common_setting', array('skey' => 'holddomain', 'svalue' => $holddomain), 0, 1);
		}
		if(!empty($_G['config']['home']['allowdomain']) && !$_G['setting']['allowspacedomain']) {
			DB::insert('common_setting', array('skey' => 'allowspacedomain', 'svalue' => 1), 0, 1);
		}

		if(!DB::result_first("SELECT domain FROM ".DB::table('common_domain')." WHERE idtype='home'")) {
			$domainroot = $_G['config']['home']['domainroot'] ? $_G['config']['home']['domainroot'] : '';
			DB::query("INSERT INTO ".DB::table('common_domain')." (domain, domainroot, id, idtype) SELECT domain, '$domainroot', uid, 'home' FROM ".DB::table('common_member_field_home')." WHERE domain<>''");
		}
		show_msg("域名设置升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'pm') {
		$nextop = 'allowgetimage';
		DB::query("UPDATE ".DB::table('common_member')." SET newpm='0'");
		show_msg("新短消息状态重置完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'allowgetimage') {
		$nextop = 'verify';
		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_usergroup_field')." WHERE allowgetimage='1'")) {
			$query = DB::query("SELECT groupid, allowgetattach FROM ".DB::table('common_usergroup_field'));
			while($row = DB::fetch($query)) {
				DB::query('UPDATE '.DB::table('common_usergroup_field')." SET allowgetimage='".intval($row['allowgetattach'])."' WHERE groupid='$row[groupid]'");
			}
			$query = DB::query("SELECT uid, allowgetattach FROM ".DB::table('forum_access'));
			while($row = DB::fetch($query)) {
				DB::query('UPDATE '.DB::table('forum_access')." SET allowgetimage='".intval($row['allowgetattach'])."' WHERE uid='$row[uid]'");
			}
		}
		show_msg("查看图片权限升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'verify') {
		$nextop = 'threadimage';
		$settings = $verifys = array();

		$query = DB::query('SELECT * FROM '.DB::table('common_setting')." WHERE skey IN ('verify', 'realname', 'videophoto', 'video_allowviewspace')");
		while($value = DB::fetch($query)) {
			if($value['skey'] == 'verify') {
				$verifys = unserialize($value['svalue']);
			} else {
				$settings[$value['skey']] = $value['svalue'];
			}
		}
		$updateverify = $_GET['updateverify'] ? true : false;
		if(!isset($verifys[6])) {
			$verifys[6] = array(
					'title' => '实名认证',
					'available' => $settings['realname'],
					'showicon' => 0,
					'viewrealname' => 0,
					'field' => array('realname' => realname),
					'icon' => ''
				);
			$verifys[7] = array(
					'title' => '视频认证',
					'available' => $settings['videophoto'],
					'showicon' => 0,
					'viewvideophoto' => $settings['video_allowviewspace'],
					'icon' => ''
				);
			if($verifys['enabled'] && ($settings['realname'] || $settings['videophoto'])) {
				$verifys['enabled'] = 1;
			}
			$verifyvalue = daddslashes(serialize($verifys));
			DB::query("REPLACE INTO ".DB::table('common_setting')." SET skey='verify', svalue='$verifyvalue'");
			$updateverify = true;
		}
		if($updateverify) {
			$p = 1000;
			$i = !empty($_GET['i']) ? intval($_GET['i']) : 0;
			$n = 0;
			$t = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_profile')." WHERE realname != ''");
			if($t) {
				$query = DB::query('SELECT mp.realname, m.* FROM '.DB::table('common_member_profile')." mp LEFT JOIN ".DB::table('common_member')." m  USING(uid) WHERE mp.uid>'$i' AND mp.realname != '' LIMIT $p");
				while($value=DB::fetch($query)) {
					$n = intval($value['uid']);
					$havauser = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_verify')." WHERE uid='$n'");
					$data = array(
							'verify6' => '1',
							'verify7' => $value['videophotostatus'] ? 1 : 0,
						);
					if($havauser) {
						DB::update('common_member_verify', $data, array('uid' => $n));
					} else {
						$data['uid'] = $n;
						DB::insert('common_member_verify', $data);
					}

				}
				if($n) {
					show_msg("实名认证升级中[$n/$t]", "$theurl?step=data&op=verify&i=$n&updateverify=true");
				}
			}
		}
		show_msg("认证数据升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'forumattach') {
		$nextop = 'moderate';
		$limit = 10000;
		$start = !empty($_GET['start']) ? $_GET['start'] : 0;
		$needupgrade = DB::query("SELECT COUNT(*) FROM ".DB::table('forum_attachmentfield'), 'SILENT');
		$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_attachment'));
		if($needupgrade && $count) {
			if(!$start) {
				for($i = 0;$i < 10;$i++) {
					DB::query("TRUNCATE ".DB::table('forum_attachment_'.$i));
				}
			}
			$query = DB::query("SELECT a.*,af.description FROM ".DB::table('forum_attachment')." a
				LEFT JOIN ".DB::table('forum_attachmentfield')." af USING(aid)
				ORDER BY aid LIMIT $start, $limit");
			if(DB::num_rows($query)) {
				while($row = DB::fetch($query)) {
					$row = daddslashes($row);
					$tid = (string)$row['tid'];
					$tableid = $tid{strlen($tid)-1};
					DB::update('forum_attachment', array('tableid' => $tableid), array('aid' => $row['aid']));
					DB::insert('forum_attachment_'.$tableid, array(
						'aid' => $row['aid'],
						'tid' => $row['tid'],
						'pid' => $row['pid'],
						'uid' => $row['uid'],
						'dateline' => $row['dateline'],
						'filename' => $row['filename'],
						'filesize' => $row['filesize'],
						'attachment' => $row['attachment'],
						'remote' => $row['remote'],
						'description' => $row['description'],
						'readperm' => $row['readperm'],
						'price' => $row['price'],
						'isimage' => $row['isimage'],
						'width' => $row['width'],
						'thumb' => $row['thumb'],
						'picid' => $row['picid'],
					));
				}
				$start += $limit;
				show_msg("论坛附件表升级中 ... $start/$count", "$theurl?step=data&op=forumattach&start=$start");
			}
			DB::query("DROP TABLE `".DB::table('forum_attachmentfield')."`");
			DB::query("ALTER TABLE ".DB::table('forum_attachment')."
				DROP `width`,
				DROP `dateline`,
				DROP `readperm`,
				DROP `price`,
				DROP `filename`,
				DROP `filetype`,
				DROP `filesize`,
				DROP `attachment`,
				DROP `isimage`,
				DROP `thumb`,
				DROP `remote`,
				DROP `picid`
			");
		}
		show_msg("论坛附件表升级完毕", "$theurl?step=data&op=$nextop");
	} elseif($_GET['op'] == 'threadimage') {
		$nextop = 'forumattach';
		$defaultmonth = 10;
		$limit = 1000;
		$start = !empty($_GET['start']) ? $_GET['start'] : 0;
		$needupgraded = DB::query("SELECT COUNT(*) FROM ".DB::table('forum_attachmentfield'), 'SILENT');
		if($needupgraded) {
			$cachefile = DISCUZ_ROOT.'./data/threadimage.cache';
			if(!file_exists($cachefile)) {
				$dateline = time() - 86400 * $defaultmonth * 30;
				$query = DB::query("SELECT tid from ".DB::table('forum_thread')." WHERE dateline>'$dateline' AND attachment='2' AND posttableid='0'");
				$data = array();
				while($row = DB::fetch($query)) {
					$data[] = $row['tid'];
				}
				if($data && @$fp = fopen($cachefile, 'w')) {
					fwrite($fp, implode('|', $data));
					fclose($fp);
				} else {
					show_msg("主题图片表无法处理，跳过", "$theurl?step=data&op=$nextop");
				}
			} else {
				$data = @file($cachefile);
				if(!$data) {
					show_msg("主题图片表无法处理，跳过", "$theurl?step=data&op=$nextop");
				}
				$data = explode('|', $data[0]);
			}
			$tids = array_slice($data, $start, $limit);
			if(!$tids) {
				@unlink($cachefile);
				show_msg("主题图片表处理完毕", "$theurl?step=data&op=$nextop");
			}
			$query = DB::query("SELECT tid, pid FROM ".DB::table('forum_post')." WHERE tid IN (".dimplode($tids).") AND first='1'");
			$insertsql = array();
			while($row = DB::fetch($query)) {
				$threadimage = DB::fetch_first("SELECT attachment, remote FROM ".DB::table(getattachtablebytid($row['tid']))." WHERE pid='$row[pid]' AND isimage IN ('1', '-1') ORDER BY width DESC LIMIT 1");
				if($threadimage['attachment']) {
					$threadimage = daddslashes($threadimage);
					$insertsql[$row['tid']] = "('$row[tid]', '$threadimage[attachment]', '$threadimage[remote]')";
				}
			}
			if($insertsql) {
				DB::query("INSERT INTO ".DB::table('forum_threadimage')." (`tid`, `attachment`, `remote`) VALUES ".implode(',', $insertsql));
			}
			$start += $limit;
			show_msg("主题图片表处理中 ... $start ", "$theurl?step=data&op=threadimage&start=$start");
		} else {
			show_msg("主题图片表无法处理，跳过", "$theurl?step=data&op=$nextop");
		}
	} elseif($_GET['op'] == 'moderate') {

		$nextop = 'founder';
		$modcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_moderate'));
		if(!$modcount) {
			$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE displayorder='-2'");
			while($row = DB::fetch($query)) {
				updatemoderate('tid', $row['tid']);
			}
			loadcache('posttable_info');
			$posttables = array();
			if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
				foreach($_G['cache']['posttable_info'] as $posttableid => $data) {
					$posttables[] = $posttableid;
				}
			} else {
				$posttables[] = 0;
			}
			foreach($posttables as $postableid) {
				$query = DB::query("SELECT pid FROM ".DB::table(getposttable($postableid))." WHERE invisible='-2' AND first='0'");
				while($row = DB::fetch($query)) {
					updatemoderate('pid', $row['pid']);
				}
			}

			$query = DB::query("SELECT blogid FROM ".DB::table('home_blog')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('blogid', $row['blogid']);
			}
			$query = DB::query("SELECT doid FROM ".DB::table('home_doing')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('doid', $row['doid']);
			}
			$query = DB::query("SELECT picid FROM ".DB::table('home_pic')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('picid', $row['picid']);
			}
			$query = DB::query("SELECT sid FROM ".DB::table('home_share')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('sid', $row['sid']);
			}
			$query = DB::query("SELECT idtype, cid FROM ".DB::table('home_comment')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate($row['idtype'].'_cid', $row['cid']);
			}
			$query = DB::query("SELECT aid FROM ".DB::table('portal_article_title')." WHERE status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('aid', $row['aid']);
			}
			$query = DB::query("SELECT cid FROM ".DB::table('portal_comment')." WHERE idtype='aid' AND status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('aid_cid', $row['cid']);
			}
			$query = DB::query("SELECT cid FROM ".DB::table('portal_comment')." WHERE idtype='topic' AND status='1'");
			while($row = DB::fetch($query)) {
				updatemoderate('topicid_cid', $row['cid']);
			}
		}
		show_msg("审核数据升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'founder') {

		$nextop = 'plugin';
		$founders = explode(',', str_replace(' ', '', $_G['config']['admincp']['founder']));
		if($founders) {
			foreach($founders as $founder) {
				if(is_numeric($founder)) {
					$fuid[] = $founder;
				} else {
					$fuser[] = $founder;
				}
			}
			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE ".($fuid ? "uid IN (".dimplode($fuid).")" : '0')." OR ".($fuser ? "username IN (".dimplode($fuser).")" : '0'));
			$founders = array();
			while($founder = DB::fetch($query)) {
				$founders[] = $founder['uid'];
			}
			if($founders) {
				DB::update('common_member', array('allowadmincp' => 1), 'uid IN ('.dimplode($founders).')');
			}
		}

		show_msg("创始人数据升级完毕", "$theurl?step=data&op=$nextop");

	} elseif($_GET['op'] == 'plugin') {

		$nextop = 'end';

		loadcache('pluginlanguage_script');
		loadcache('pluginlanguage_template');
		loadcache('pluginlanguage_install');
		if(!$_G['cache']['pluginlanguage_script'] && !$_G['cache']['pluginlanguage_template'] && !$_G['cache']['pluginlanguage_install']) {
			$query = DB::query("SELECT identifier, pluginid, modules FROM ".DB::table('common_plugin'));
			while($plugin = DB::fetch($query)) {
				$plugin['modules'] = unserialize($plugin['modules']);
				if(!empty($plugin['modules']['extra']['langexists'])) {
					@include DISCUZ_ROOT.'./data/plugindata/'.$plugin['identifier'].'.lang.php';
					if(!empty($scriptlang)) {
						$_G['cache']['pluginlanguage_script'][$plugin['identifier']] = $scriptlang[$plugin['identifier']];
					}
					if(!empty($templatelang)) {
						$_G['cache']['pluginlanguage_template'][$plugin['identifier']] = $templatelang[$plugin['identifier']];
					}
					if(!empty($installlang)) {
						$_G['cache']['pluginlanguage_install'][$plugin['identifier']] = $installlang[$plugin['identifier']];
					}
				}
			}
			save_syscache('pluginlanguage_script', $_G['cache']['pluginlanguage_script']);
			save_syscache('pluginlanguage_template', $_G['cache']['pluginlanguage_template']);
			save_syscache('pluginlanguage_install', $_G['cache']['pluginlanguage_install']);
		}

		show_msg("插件语言包数据升级完毕", "$theurl?step=data&op=$nextop");

	} else {

		$deletevar = array('app', 'home');//config中需要删除的项目
		$default_config = $_config = array();
		$default_configfile = DISCUZ_ROOT.'./config/config_global_default.php';
		if(!file_exists($default_configfile)) {
			exit('config_global_default.php was lost, please reupload this  file.');
		} else {
			include $default_configfile;
			$default_config = $_config;
		}
		$configfile = DISCUZ_ROOT.'./config/config_global.php';
		include $configfile;
		DB::query("UPDATE ".DB::table('common_plugin')." SET available='0' WHERE identifier NOT IN ('qqconnect', 'cloudstat', 'soso_smilies')");
		if(save_config_file($configfile, $_config, $default_config, $deletevar)) {
			show_msg("数据处理完成", "$theurl?step=delete");
		} else {
			show_msg('"config/config_global.php" 文件已更新，由于 "config/" 目录不可写入，我们已将更新的文件保存到 "data/" 目录下，请通过 FTP 软件将其转移到 "config/" 目录下覆盖源文件。<br /><br /><a href="'.$theurl.'?step=delete">当您完成上述操作后点击这里继续</a>');
		}
	}

}elseif ($_GET['step'] == 'delete') {

	if(!$devmode) {
		show_msg("数据删除不处理，进入下一步", "$theurl?step=style");
	}

	$oldtables = array();
	$query = DB::query("SHOW TABLES LIKE '$config[tablepre]%'");
	while ($value = DB::fetch($query)) {
		$values = array_values($value);
		$oldtables[] = $values[0];
	}

	$sql = implode('', file($sqlfile));
	preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s+\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];

	$deltables = array();
	$delcolumns = array();

	foreach ($oldtables as $tname) {
		$tname = substr($tname, strlen($config['tablepre']));
		if(in_array($tname, $newtables)) {
			$query = DB::query("SHOW CREATE TABLE ".DB::table($tname));
			$cvalue = DB::fetch($query);
			$oldcolumns = getcolumn($cvalue['Create Table']);

			$i = array_search($tname, $newtables);
			$newcolumns = getcolumn($newsqls[$i]);

			foreach ($oldcolumns as $colname => $colstruct) {
				if($colname == 'UNIQUE' || $colname == 'KEY') {
					foreach ($colstruct as $key_index => $key_value) {
						if(empty($newcolumns[$colname][$key_index])) {
							$delcolumns[$tname][$colname][$key_index] = $key_value;
						}
					}
				} else {
					if(empty($newcolumns[$colname])) {
						$delcolumns[$tname][] = $colname;
					}
				}
			}
		} else {
			if(!strexists($tname, 'uc_') && !strexists($tname, 'ucenter_') && !preg_match('/forum_(thread|post)_(\d+)$/i', $tname)) {
				$deltables[] = $tname;
			}
		}
	}

	show_header();
	echo '<form method="post" autocomplete="off" action="'.$theurl.'?step=delete">';

	$deltablehtml = '';
	if($deltables) {
		$deltablehtml .= '<table>';
		foreach ($deltables as $tablename) {
			$deltablehtml .= "<tr><td><input type=\"checkbox\" name=\"deltables[$tablename]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td></tr>";
		}
		$deltablehtml .= '</table>';
		echo "<p>以下 <strong>数据表</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$deltablehtml";
	}

	$delcolumnhtml = '';
	if($delcolumns) {
		$delcolumnhtml .= '<table>';
		foreach ($delcolumns as $tablename => $cols) {
			foreach ($cols as $coltype => $col) {
				if (is_array($col)) {
					foreach ($col as $index => $indexvalue) {
						$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$coltype][$index]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>索引($coltype) $index $indexvalue</td></tr>";
					}
				} else {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>字段 $col</td></tr>";
				}
			}
		}
		$delcolumnhtml .= '</table>';

		echo "<p>以下 <strong>字段</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$delcolumnhtml";
	}

	if(empty($deltables) && empty($delcolumns)) {
		echo "<p>与标准数据库相比，没有需要删除的数据表和字段</p><a href=\"$theurl?step=style\">请点击进入下一步</a></p>";
	} else {
		echo "<p><input type=\"submit\" name=\"delsubmit\" value=\"提交删除\"></p><p>您也可以忽略多余的表和字段<br><a href=\"$theurl?step=style\">直接进入下一步</a></p>";
	}
	echo '</form>';

	show_footer();
	exit();

} elseif ($_GET['step'] == 'style') {
	if(empty($_GET['confirm'])) {
		show_msg("请确认是否要恢复默认风格？<br /><br /><a href=\"$theurl?step=style&confirm=yes\">[ 是 ]</a>&nbsp;&nbsp;<a href=\"$theurl?step=cache\">[ 否 ]</a>", '');
	}

	define('IN_ADMINCP', true);
	require_once libfile('function/admincp');
	require_once libfile('function/importdata');
	$dir = DB::result_first("SELECT t.directory FROM ".DB::table('common_style')." s LEFT JOIN ".DB::table('common_template')." t ON t.templateid=s.templateid WHERE s.styleid='1'");
	import_styles(1, $dir, 1, 0);
	DB::update('common_setting', array('svalue' => 1), "skey='styleid'");

	show_msg("默认风格已恢复，进入下一步", "$theurl?step=cache");

} elseif ($_GET['step'] == 'cache') {

	if(!$devmode && @$fp = fopen($lockfile, 'w')) {
		fwrite($fp, ' ');
		fclose($fp);
	}

	dir_clear(ROOT_PATH.'./data/template');
	dir_clear(ROOT_PATH.'./data/cache');
	dir_clear(ROOT_PATH.'./data/threadcache');
	dir_clear(ROOT_PATH.'./uc_client/data');
	dir_clear(ROOT_PATH.'./uc_client/data/cache');
	save_syscache('setting', '');

	show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=initsys" style="display:none;" onload="document.getElementById(\'finalmsg\').innerHTML = \'恭喜，数据库结构升级完成！为了数据安全，请删除本文件。\'"></iframe>');

}

function has_another_special_table($tablename, $key) {
	if(!$key) {
		return $tablename;
	}

	$tables_array = get_special_tables_array($tablename);

	if($key > count($tables_array)) {
		return FALSE;
	} else {
		return TRUE;
	}
}

function get_special_tables_array($tablename) {
	$tablename = DB::table($tablename);
	$tablename = str_replace('_', '\_', $tablename);
	$query = DB::query("SHOW TABLES LIKE '{$tablename}\_%'");
	$dbo = DB::object();
	$tables_array = array();
	while($row = $dbo->fetch_array($query, MYSQL_NUM)) {
		if(preg_match("/^{$tablename}_(\\d+)$/i", $row[0])) {
			$prefix_len = strlen($dbo->tablepre);
			$row[0] = substr($row[0], $prefix_len);
			$tables_array[] = $row[0];
		}
	}
	return $tables_array;
}

function get_special_table_by_num($tablename, $num) {
	$tables_array = get_special_tables_array($tablename);

	$num --;
	return isset($tables_array[$num]) ? $tables_array[$num] : FALSE;
}

function getcolumn($creatsql) {

	$creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
	preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = array();
	foreach ($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = remakesql($value);
		if(substr($value, -1) == ',') $value = substr($value, 0, -1);

		$vs = explode(' ', $value);
		$cname = $vs[0];

		if($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

			$name_length = strlen($cname);
			if($cname == 'UNIQUE') $name_length = $name_length + 4;

			$subvalue = trim(substr($value, $name_length));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols[$cname][$subcname] = trim(substr($value, ($name_length+2+strlen($subcname))));

		}  elseif($cname == 'PRIMARY') {

			$newcols[$cname] = trim(substr($value, 11));

		}  else {

			$newcols[$cname] = trim(substr($value, strlen($cname)));
		}
	}
	return $newcols;
}

function remakesql($value) {
	$value = trim(preg_replace("/\s+/", ' ', $value));
	$value = str_replace(array('`',', ', ' ,', '( ' ,' )', 'mediumtext'), array('', ',', ',','(',')','text'), $value);
	return $value;
}

function show_msg($message, $url_forward='') {

	if($url_forward) {
		$message = "<a href=\"$url_forward\">$message (跳转中...)</a><script>setTimeout(\"window.location.href ='$url_forward';\", 1);</script>";
	}

	show_header();
	print<<<END
	<table>
	<tr><td>$message</td></tr>
	</table>
END;
	show_footer();
	exit();
}


function show_header() {
	global $config;

	$nowarr = array($_GET['step'] => ' class="current"');

	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
	<title> 数据库升级程序 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>数据库升级工具</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[start]}>升级开始</td>
	<td{$nowarr[sql]}>数据库结构添加与更新</td>
	<td{$nowarr[data]}>数据更新</td>
	<td{$nowarr[delete]}>数据库结构删除</td>
	<td{$nowarr[cache]}>升级完成</td>
	</tr>
	</table>
	<br>
END;
}

function show_footer() {
	print<<<END
	</div>
	<div id="footer">&copy; Comsenz Inc. 2001-2011 http://www.comsenz.com</div>
	</div>
	<br>
	</body>
	</html>
END;
}

function runquery($sql) {
	global $_G;
	$tablepre = $_G['config']['db'][1]['tablepre'];
	$dbcharset = $_G['config']['db'][1]['dbcharset'];

	$sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' '.$tablepre, ' '.$tablepre, ' `'.$tablepre, ' '.$tablepre, ' `'.$tablepre), $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				DB::query(createtable($query, $dbcharset));

			} else {
				DB::query($query);
			}

		}
	}
}


function import_diy($importfile, $primaltplname, $targettplname) {
	global $_G;

	$css = $html = '';
	$arr = array();

	$content = file_get_contents(realpath($importfile));
	if (empty($content)) return $arr;
	require_once DISCUZ_ROOT.'./source/class/class_xml.php';
	$diycontent = xml2array($content);

	if ($diycontent) {

		foreach ($diycontent['layoutdata'] as $key => $value) {
			if (!empty($value)) getframeblock($value);
		}
		$newframe = array();
		foreach ($_G['curtplframe'] as $value) {
			$newframe[] = $value['type'].random(6);
		}

		$mapping = array();
		if (!empty($diycontent['blockdata'])) {
			$mapping = block_import($diycontent['blockdata']);
			unset($diycontent['blockdata']);
		}

		$oldbids = $newbids = array();
		if (!empty($mapping)) {
			foreach($mapping as $obid=>$nbid) {
				$oldbids[] = 'portal_block_'.$obid;
				$newbids[] = 'portal_block_'.$nbid;
			}
		}

		require_once DISCUZ_ROOT.'./source/class/class_xml.php';
		$xml = array2xml($diycontent['layoutdata'],true);
		$xml = str_replace($oldbids, $newbids, $xml);
		$xml = str_replace((array)array_keys($_G['curtplframe']), $newframe, $xml);
		$diycontent['layoutdata'] = xml2array($xml);

		$css = str_replace($oldbids, $newbids, $diycontent['spacecss']);
		$css = str_replace((array)array_keys($_G['curtplframe']), $newframe, $css);

		$arr['spacecss'] = $css;
		$arr['layoutdata'] = $diycontent['layoutdata'];
		$arr['style'] = $diycontent['style'];
		save_diy_data($primaltplname, $targettplname, $arr, true);
	}
	return $arr;
}

function save_config_file($filename, $config, $default, $deletevar) {
	$config = setdefault($config, $default, $deletevar);
	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$content = <<<EOT
<?php


\$_config = array();

EOT;
	$content .= getvars(array('_config' => $config));
	$content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)." //\r\n\r\n?>";
	if(!is_writable($filename) || !($len = file_put_contents($filename, $content))) {
		file_put_contents(DISCUZ_ROOT.'./data/config_global.php', $content);
		return 0;
	}
	return 1;
}

function setdefault($var, $default, $deletevar) {
	foreach ($default as $k => $v) {
		if(!isset($var[$k])) {
			$var[$k] = $default[$k];
		} elseif(is_array($v)) {
			$var[$k] = setdefault($var[$k], $default[$k]);
		}
	}
	foreach ($deletevar as $k) {
		unset($var[$k]);
	}
	return $var;
}

function getvars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
			continue;
		}
		if(is_array($val)) {
			$evaluate .= buildarray($val, 0, "\${$key}")."\r\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}

function buildarray($array, $level = 0, $pre = '$_config') {
	static $ks;
	if($level == 0) {
		$ks = array();
		$return = '';
	}

	foreach ($array as $key => $val) {
		if($level == 0) {
			$newline = str_pad('  CONFIG '.strtoupper($key).'  ', 70, '-', STR_PAD_BOTH);
			$return .= "\r\n// $newline //\r\n";
			if($key == 'admincp') {
				$newline = str_pad(' Founders: $_config[\'admincp\'][\'founder\'] = \'1,2,3\'; ', 70, '-', STR_PAD_BOTH);
				$return .= "// $newline //\r\n";
			}
		}

		$ks[$level] = $ks[$level - 1]."['$key']";
		if(is_array($val)) {
			$ks[$level] = $ks[$level - 1]."['$key']";
			$return .= buildarray($val, $level + 1, $pre);
		} else {
			$val =  is_string($val) || strlen($val) > 12 || !preg_match("/^\-?[1-9]\d*$/", $val) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			$return .= $pre.$ks[$level - 1]."['$key']"." = $val;\r\n";
		}
	}
	return $return;
}

function dir_clear($dir) {
	global $lang;
	if($directory = @dir($dir)) {
		while($entry = $directory->read()) {
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			}
		}
		$directory->close();
		@touch($dir.'/index.htm');
	}
}

function block_conver_to_thread($block){
	if($block['blockclass'] == 'forum_attachment') {
		$block['blockclass'] = 'forum_thread';
		$block['script'] = 'thread';
	} else if($block['blockclass'] == 'group_attachment') {
		$block['blockclass'] = 'group_thread';
		$block['script'] = 'groupthread';
	}
	$block['param'] = is_array($block['param']) ? $block['param'] : (array)unserialize($block['param']);
	unset($block['param']['threadmethod']);
	$block['param']['special'] = array(0);
	$block['param']['picrequired'] = 1;
	$block['param'] = serialize($block['param']);
	$block['styleid'] = 0;
	$block['blockstyle'] = block_style_conver_to_thread($block['blockstyle'], $block['blockclass']);
	return $block;
}

function block_style_conver_to_thread($style, $blockclass) {
	$template = block_build_template($style['template']);
	$search = array('threadurl', 'threadsubject', 'threadsummary', 'filesize', 'downloads');
	$replace = array('url', 'title', 'summary', '');
	$template = str_replace($search, $replace, $template);
	$arr = array(
		'name' => '',
		'blockclass' => $blockclass,
	);
	block_parse_template($template, $arr);
	$arr['fields'] = unserialize($arr['fields']);
	$arr['template'] = unserialize($arr['template']);
	$arr = serialize($arr);
	return $arr;
}

?>